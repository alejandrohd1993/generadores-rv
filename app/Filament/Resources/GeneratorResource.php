<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeneratorResource\Pages;
use App\Filament\Resources\GeneratorResource\RelationManagers;
use App\Models\Generator;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class GeneratorResource extends Resource
{
    protected static ?string $model = Generator::class;

    protected static ?string $modelLabel = 'Generador';

    protected static ?string $pluralModelLabel = 'Generadores';

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->label('Identificador Único del Generador')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('modelo')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('marca')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('horometro')
                    ->maxLength(255)
                    ->helperText('Formato: HH:MM:SS')
                    ->default(null),
                Forms\Components\Select::make('estado')
                    ->options([
                        'Disponible' => 'Disponible',
                        'En uso' => 'En uso',
                        'En mantenimiento' => 'En mantenimiento',
                        'Fuera de servicio' => 'Fuera de servicio',
                    ])
                    ->default('Disponible')
                    ->required(),
                Section::make('Último Mantenimiento')
                    ->schema([
                        Forms\Components\TextInput::make('ultimo_mantenimiento_filtro')
                            ->label('Horómetro Último Mantenimiento de Filtro'),
                        Forms\Components\Select::make('filtro_suplly_id')
                            ->label('Insumo utilizado (Filtro)')
                            ->options(function () {
                                return \App\Models\Suplly::where('tipo', 'filtro')
                                    ->pluck('nombre', 'id')
                                    ->toArray();
                            })
                            ->preload(),
                        Forms\Components\TextInput::make('ultimo_mantenimiento_aceite')
                            ->label('Horómetro Último Mantenimiento de Aceite'),
                        Forms\Components\Select::make('aceite_suplly_id')
                            ->label('Insumo utilizado (Aceite)')
                            ->options(function () {
                                return \App\Models\Suplly::where('tipo', 'aceite')
                                    ->pluck('nombre', 'id')
                                    ->toArray();
                            })
                            ->preload(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('modelo')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('marca')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('horometro')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('estado'),
                Tables\Columns\TextColumn::make('ultimo_mantenimiento_filtro')
                    ->label('Último Mant. Filtro')
                    ->getStateUsing(function (Generator $record): string {
                        $ultimoMantenimientoFiltro = $record->usages()
                            ->where('tipo', 'mantenimiento')
                            ->whereHas('generator', function ($query) use ($record) {
                                $query->where('id', $record->id);
                            })
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('maintenances')
                                    ->whereColumn('maintenances.id', 'usages.reference_id')
                                    ->where('maintenances.tipo_mantenimiento', 'Filtro');
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();

                        return $ultimoMantenimientoFiltro
                            ? $ultimoMantenimientoFiltro->horometro_fin
                            : ($record->ultimo_mantenimiento_filtro ?? 'N/A');
                    }),
                Tables\Columns\TextColumn::make('ultimo_mantenimiento_aceite')
                    ->label('Último Mant. Aceite')
                    ->getStateUsing(function (Generator $record): string {
                        $ultimoMantenimientoAceite = $record->usages()
                            ->where('tipo', 'mantenimiento')
                            ->whereHas('generator', function ($query) use ($record) {
                                $query->where('id', $record->id);
                            })
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('maintenances')
                                    ->whereColumn('maintenances.id', 'usages.reference_id')
                                    ->where('maintenances.tipo_mantenimiento', 'Aceite');
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();

                        return $ultimoMantenimientoAceite
                            ? $ultimoMantenimientoAceite->horometro_fin
                            : ($record->ultimo_mantenimiento_aceite ?? 'N/A');
                    }),
                Tables\Columns\TextColumn::make('ultimo_horometro')
                    ->label('Último Horómetro')
                    ->getStateUsing(function (Generator $record): string {
                        $ultimoUsage = $record->usages()->orderBy('created_at', 'desc')->first();
                        return $ultimoUsage ? $ultimoUsage->horometro_fin : $record->horometro ?? 'N/A';
                    }),
                Tables\Columns\TextColumn::make('tiempo_restante_filtro')
                    ->label('Tiempo Rest. Filtro ACPM')
                    ->getStateUsing(function (Generator $record): string {
                        $ultimoHorometro = $record->usages()->orderBy('created_at', 'desc')->first()?->horometro_fin ?? $record->horometro;

                        $ultimoMantenimientoFiltro = $record->usages()
                            ->where('tipo', 'mantenimiento')
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('maintenances')
                                    ->whereColumn('maintenances.id', 'usages.reference_id')
                                    ->where('maintenances.tipo_mantenimiento', 'Filtro');
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $horometroUltimoMantenimiento = $ultimoMantenimientoFiltro?->horometro_fin ?? $record->ultimo_mantenimiento_filtro;

                        if (!$ultimoHorometro || !$horometroUltimoMantenimiento) {
                            return 'N/A';
                        }

                        $horasUltimoMantenimiento = self::convertirHorasADecimal($horometroUltimoMantenimiento);
                        $horasActuales = self::convertirHorasADecimal($ultimoHorometro);
                        $horasTranscurridas = $horasActuales - $horasUltimoMantenimiento;

                        // ⚠️ Obtener las horas del insumo asociado
                        $horasParaElInsumo = optional($ultimoMantenimientoFiltro->reference_model?->suplly)->horas ?? 100;

                        $horasRestantes = $horasParaElInsumo - $horasTranscurridas;

                        if ($horasRestantes <= 0) {
                            return 'Mantenimiento requerido';
                        }

                        return self::convertirDecimalAHoras($horasRestantes);
                    })
                    ->color(function (Generator $record): string {
                        $ultimoHorometro = $record->usages()->orderBy('created_at', 'desc')->first()?->horometro_fin ?? $record->horometro;
                    
                        $ultimoMantenimientoFiltro = $record->usages()
                            ->where('tipo', 'mantenimiento')
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('maintenances')
                                    ->whereColumn('maintenances.id', 'usages.reference_id')
                                    ->where('maintenances.tipo_mantenimiento', 'Filtro');
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();
                    
                        $horometroUltimoMantenimiento = $ultimoMantenimientoFiltro?->horometro_fin ?? $record->ultimo_mantenimiento_filtro;
                    
                        if (!$ultimoHorometro || !$horometroUltimoMantenimiento) {
                            return 'gray';
                        }
                    
                        $horasUltimoMantenimiento = self::convertirHorasADecimal($horometroUltimoMantenimiento);
                        $horasActuales = self::convertirHorasADecimal($ultimoHorometro);
                        $horasTranscurridas = $horasActuales - $horasUltimoMantenimiento;
                    
                        $horasParaElInsumo = optional($ultimoMantenimientoFiltro->reference?->suplly)->horas ?? 100;
                        $horasRestantes = $horasParaElInsumo - $horasTranscurridas;
                    
                        if ($horasRestantes <= 0) {
                            return 'danger';
                        } elseif ($horasRestantes <= $horasParaElInsumo * 0.2) {
                            return 'danger';
                        } elseif ($horasRestantes <= $horasParaElInsumo * 0.5) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    }),
                Tables\Columns\TextColumn::make('tiempo_restante_aceite')
                    ->label('Tiempo Rest. Aceite')
                    ->getStateUsing(function (Generator $record): string {
                        $ultimoHorometro = $record->usages()->orderBy('created_at', 'desc')->first()?->horometro_fin ?? $record->horometro;

                        $ultimoMantenimientoAceite = $record->usages()
                            ->where('tipo', 'mantenimiento')
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('maintenances')
                                    ->whereColumn('maintenances.id', 'usages.reference_id')
                                    ->where('maintenances.tipo_mantenimiento', 'Aceite');
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $horometroUltimoMantenimiento = $ultimoMantenimientoAceite?->horometro_fin ?? $record->ultimo_mantenimiento_aceite;

                        if (!$ultimoHorometro || !$horometroUltimoMantenimiento) {
                            return 'N/A';
                        }

                        $horasUltimoMantenimiento = self::convertirHorasADecimal($horometroUltimoMantenimiento);
                        $horasActuales = self::convertirHorasADecimal($ultimoHorometro);
                        $horasTranscurridas = $horasActuales - $horasUltimoMantenimiento;

                        // ⚠️ Obtener las horas del insumo asociado
                        $horasParaElInsumo = optional($ultimoMantenimientoAceite->reference_model?->suplly)->horas ?? 200;

                        $horasRestantes = $horasParaElInsumo - $horasTranscurridas;

                        if ($horasRestantes <= 0) {
                            return 'Mantenimiento requerido';
                        }

                        return self::convertirDecimalAHoras($horasRestantes);
                    })
                    ->color(function (Generator $record): string {
                        $ultimoHorometro = $record->usages()->orderBy('created_at', 'desc')->first()?->horometro_fin ?? $record->horometro;
                    
                        $ultimoMantenimientoAceite = $record->usages()
                            ->where('tipo', 'mantenimiento')
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('maintenances')
                                    ->whereColumn('maintenances.id', 'usages.reference_id')
                                    ->where('maintenances.tipo_mantenimiento', 'Aceite');
                            })
                            ->orderBy('created_at', 'desc')
                            ->first();
                    
                        $horometroUltimoMantenimiento = $ultimoMantenimientoAceite?->horometro_fin ?? $record->ultimo_mantenimiento_filtro;
                    
                        if (!$ultimoHorometro || !$horometroUltimoMantenimiento) {
                            return 'gray';
                        }
                    
                        $horasUltimoMantenimiento = self::convertirHorasADecimal($horometroUltimoMantenimiento);
                        $horasActuales = self::convertirHorasADecimal($ultimoHorometro);
                        $horasTranscurridas = $horasActuales - $horasUltimoMantenimiento;
                    
                        $horasParaElInsumo = optional($ultimoMantenimientoAceite->reference?->suplly)->horas ?? 200;
                        $horasRestantes = $horasParaElInsumo - $horasTranscurridas;
                    
                        if ($horasRestantes <= 0) {
                            return 'danger';
                        } elseif ($horasRestantes <= $horasParaElInsumo * 0.2) {
                            return 'danger';
                        } elseif ($horasRestantes <= $horasParaElInsumo * 0.5) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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

    // Métodos auxiliares para convertir formatos de hora
    private static function convertirHorasADecimal(string $horasFormato): float
    {
        if (empty($horasFormato)) {
            return 0;
        }

        $partes = explode(':', $horasFormato);

        if (count($partes) !== 3) {
            return 0;
        }

        $horas = (int) $partes[0];
        $minutos = (int) $partes[1];
        $segundos = (int) $partes[2];

        return $horas + ($minutos / 60) + ($segundos / 3600);
    }

    private static function convertirDecimalAHoras(float $horasDecimal): string
    {
        $horas = floor($horasDecimal);
        $minutosDecimal = ($horasDecimal - $horas) * 60;
        $minutos = floor($minutosDecimal);
        $segundos = floor(($minutosDecimal - $minutos) * 60);

        return sprintf('%03d:%02d:%02d', $horas, $minutos, $segundos);
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
            'index' => Pages\ListGenerators::route('/'),
            'create' => Pages\CreateGenerator::route('/create'),
            'edit' => Pages\EditGenerator::route('/{record}/edit'),
        ];
    }
}
