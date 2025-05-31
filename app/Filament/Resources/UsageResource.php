<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsageResource\Pages;
use App\Filament\Resources\UsageResource\RelationManagers;
use App\Models\Maintenance;
use App\Models\Service;
use App\Models\Generator;
use App\Models\Usage;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;

class UsageResource extends Resource
{
    protected static ?string $model = Usage::class;

    protected static ?string $modelLabel = 'Uso';

    protected static ?string $pluralModelLabel = 'Usos';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        $tipo = request()->query('tipo');
        $referenceId = request()->query('service_id');
        $fecha_inicio = request()->query('fecha_inicio');
        $fecha_final = request()->query('fecha_final');
        $fecha = request()->query('fecha');
        $generador_id = request()->query('generador_id');

        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha')
                    ->required()
                    ->default($fecha)
                    ->minDate($fecha_inicio)
                    ->maxDate($fecha_final),
                Forms\Components\Select::make('tipo')
                    ->default($tipo)
                    ->options([
                        'servicio' => 'Servicio',
                        'mantenimiento' => 'Mantenimiento',
                        'preoperativo' => 'Preoperativo',
                    ])
                    ->required()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        $set('reference_id', null);
                        $set('generator_id', null);
                    })
                    ->live(),
                Forms\Components\Select::make('reference_id')
                    ->default($referenceId)
                    ->label('Relacionado con')
                    ->options(function (callable $get) {
                        return match ($get('tipo')) {
                            'servicio' => \App\Models\Service::where('estado', '!=', 'Completado')->pluck('nombre', 'id'),
                            'mantenimiento' => \App\Models\Maintenance::where('estado', '!=', 'Completado')->pluck('nombre', 'id'),
                            'preoperativo' => \App\Models\Service::where('estado', '!=', 'Completado')->pluck('nombre', 'id'),
                            default => [],
                        };
                    })
                    ->live()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        // Cuando cambiamos la referencia, reseteamos el generador
                        $set('generator_id', null);

                        // Si es un mantenimiento, pre-seleccionamos el generador asociado
                        if ($get('tipo') === 'mantenimiento' && $get('reference_id')) {
                            $maintenance = Maintenance::find($get('reference_id'));
                            if ($maintenance && $maintenance->generator_id) {
                                $set('generator_id', $maintenance->generator_id);
                            }
                        }
                    })
                    ->required(),
                Forms\Components\Select::make('generator_id')
                    ->label('Generador')
                    ->required()
                    ->default($generador_id)
                    ->options(function (Forms\Get $get) {
                        $tipo = $get('tipo');
                        $referenceId = $get('reference_id');

                        if (!$referenceId) {
                            return [];
                        }

                        if ($tipo === 'mantenimiento') {
                            // Si es un mantenimiento, solo mostramos el generador asociado
                            $maintenance = Maintenance::find($referenceId);
                            if ($maintenance && $maintenance->generator_id) {
                                return Generator::where('id', $maintenance->generator_id)
                                    ->pluck('codigo', 'id');
                            }
                            return [];
                        } else if ($tipo === 'servicio') {
                            // Si es un servicio, mostramos los generadores vinculados al servicio
                            return Generator::whereHas('services', function (Builder $query) use ($referenceId) {
                                $query->where('services.id', $referenceId);
                            })->pluck('codigo', 'id');
                        } else if ($tipo === 'preoperativo') {
                            // Si es un servicio, mostramos los generadores vinculados al servicio
                            return Generator::whereHas('services', function (Builder $query) use ($referenceId) {
                                $query->where('services.id', $referenceId);
                            })->pluck('codigo', 'id');
                        }

                        return [];
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $lastUsage = \App\Models\Usage::where('generator_id', $state)
                                ->orderBy('id', 'desc')
                                ->first();

                            if ($lastUsage) {
                                $set('horometro_inicio', $lastUsage->horometro_fin);

                                // Pre-llenar los valores de horas_fin con los valores del horómetro inicio
                                if ($lastUsage->horometro_fin) {
                                    $parts = explode(':', $lastUsage->horometro_fin);
                                    if (count($parts) == 3) {
                                        $set('horas_fin', (int)$parts[0]);
                                        $set('minutos_fin', (int)$parts[1]);
                                        $set('segundos_fin', (int)$parts[2]);

                                        // Actualizar el campo hidden horometro_fin
                                        $set('horometro_fin', $lastUsage->horometro_fin);
                                    }
                                }
                            } else {
                                $set('horometro_inicio', null);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('horometro_inicio')
                    ->readOnly(function (callable $get) {
                        return !empty($get('generator_id'));
                    }),
                Forms\Components\TextInput::make('horas_trabajadas')
                    ->readOnly(),
                Forms\Components\Hidden::make('horometro_fin'),
                Forms\Components\Section::make('Horómetro Final')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('horas_fin')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->label('Horas')
                            ->live(onBlur: true) // Cambiar a onBlur para actualizar solo cuando el usuario sale del campo
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::actualizarHorometroFin($get, $set);
                            }),
                        Forms\Components\TextInput::make('minutos_fin')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(59)
                            ->label('Minutos')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::actualizarHorometroFin($get, $set);
                            }),
                        Forms\Components\TextInput::make('segundos_fin')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(59)
                            ->label('Segundos')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::actualizarHorometroFin($get, $set);
                            }),
                    ]),

                Forms\Components\TextInput::make('combustible')
                    ->label('Gasto por combustible')
                    ->numeric()
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
                Forms\Components\TextInput::make('otros_gastos')
                    ->label('Otros gastos Generador')
                    ->numeric()
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('generator.codigo')
                    ->numeric(),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('reference_id')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('horometro_inicio'),
                Tables\Columns\TextColumn::make('horometro_fin'),
                Tables\Columns\TextColumn::make('horas_trabajadas'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsages::route('/'),
            'create' => Pages\CreateUsage::route('/create'),
            'edit' => Pages\EditUsage::route('/{record}/edit'),
        ];
    }

    // Métodos auxiliares convertidos a estáticos
    private static function actualizarHorometroFin(callable $get, callable $set): void
    {
        // Obtener los valores actuales, verificando que no sean null ni string vacío
        $horas = $get('horas_fin');
        $horas = (is_numeric($horas) && $horas !== '') ? $horas : 0;

        $minutos = $get('minutos_fin');
        $minutos = (is_numeric($minutos) && $minutos !== '') ? $minutos : 0;

        $segundos = $get('segundos_fin');
        $segundos = (is_numeric($segundos) && $segundos !== '') ? $segundos : 0;

        // Formatear a formato 1234:00:00
        $horometro = sprintf('%d:%02d:%02d', $horas, $minutos, $segundos);
        $set('horometro_fin', $horometro);

        // Actualizar horas trabajadas
        self::calcularHorasTrabajadas($get, $set);
    }

    private static function calcularHorasTrabajadas(callable $get, callable $set): void
    {
        $horometroInicio = $get('horometro_inicio');
        $horometroFin = $get('horometro_fin');

        if (empty($horometroInicio) || empty($horometroFin)) {
            return;
        }

        // Convertir horometros a segundos para el cálculo
        $inicioPartes = explode(':', $horometroInicio);
        $finPartes = explode(':', $horometroFin);

        if (count($inicioPartes) != 3 || count($finPartes) != 3) {
            return;
        }

        $inicioSegundos = ((int)$inicioPartes[0] * 3600) + ((int)$inicioPartes[1] * 60) + (int)$inicioPartes[2];
        $finSegundos = ((int)$finPartes[0] * 3600) + ((int)$finPartes[1] * 60) + (int)$finPartes[2];

        // Calcular la diferencia en segundos
        $diferenciaSegundos = $finSegundos - $inicioSegundos;

        // Solo actualizar si la diferencia es positiva
        if ($diferenciaSegundos > 0) {
            // Convertir segundos a formato hh:mm:ss
            $horas = floor($diferenciaSegundos / 3600);
            $minutos = floor(($diferenciaSegundos % 3600) / 60);
            $segundos = $diferenciaSegundos % 60;

            // Formatear a hh:mm:ss
            $horasTrabajadas = sprintf('%d:%02d:%02d', $horas, $minutos, $segundos);
            $set('horas_trabajadas', $horasTrabajadas);
        }
    }
}
