<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use App\Models\Generator;
use App\Mail\ServicioCompletado;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;



class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $modelLabel = 'Servicio';

    protected static ?string $pluralModelLabel = 'Servicios';

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->autocomplete('off')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'nombre')
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Operador')
                            ->relationship('user', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('lugar')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'En proceso' => 'En proceso',
                                'Completado' => 'Completado',
                                'Cancelado' => 'Cancelado',
                            ])
                            ->default('Pendiente')
                            ->required(),
                        Forms\Components\Select::make('facturado')
                            ->options([
                                'Si' => 'Si',
                                'No' => 'No',
                            ])
                            ->default('No')
                            ->required(),
                        Forms\Components\TextInput::make('valor_servicio')
                            ->numeric()
                            ->prefix('$')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(','),
                    ])
                    ->columns(2),

                Section::make('Fechas del Servicio')
                    ->schema([
                        Forms\Components\DatePicker::make('date_start')
                            ->label('Fecha de inicio')
                            ->required(),
                        Forms\Components\DatePicker::make('date_final')
                            ->label('Fecha final')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Presupuesto')
                    ->schema([
                        Forms\Components\TextInput::make('presupuesto_viaticos')
                            ->numeric()
                            ->prefix('$')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->reactive()
                            ->debounce(750)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                // Asegurarse de que siempre tenga un valor para la suma
                                $viaticos = floatval(preg_replace('/[^0-9.]/', '', $get('presupuesto_otros_gastos') ?? '0'));
                                $combustible = floatval(preg_replace('/[^0-9.]/', '', $state ?? '0'));
                                $total = $combustible + $viaticos;
                                // Aplicamos el formato de miles al total
                                $set('presupuesto_total', number_format($total, 2, '.', ','));
                            }),
                        Forms\Components\TextInput::make('presupuesto_otros_gastos')
                            ->numeric()
                            ->prefix('$')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->reactive()
                            ->debounce(500)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $combustible = floatval(preg_replace('/[^0-9.]/', '', $get('presupuesto_viaticos') ?? '0'));
                                $viaticos = floatval(preg_replace('/[^0-9.]/', '', $state ?? '0'));
                                $total = $combustible + $viaticos;
                                $set('presupuesto_total', number_format($total, 2, '.', ','));
                            }),
                        Forms\Components\TextInput::make('presupuesto_total')
                            ->numeric()
                            ->prefix('$')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->readonly(),
                    ])
                    ->columns(3),

                Section::make('Generadores Asignados')
                    ->schema([
                        Forms\Components\Repeater::make('generators_data')
                            ->label('Generadores Asignados')
                            ->schema([
                                Forms\Components\Select::make('generators')
                                    ->label('Generador')
                                    ->options(\App\Models\Generator::all()->pluck('codigo', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('horometro_inicio')
                                    ->label('Horómetro Inicio')
                                    ->hidden()
                                    ->required(),
                                Forms\Components\TextInput::make('horometro_fin')
                                    ->label('Horómetro Fin')
                                    ->hidden()
                                    ->required(),
                                Forms\Components\TextInput::make('horas_trabajadas')
                                    ->label('Horas Trabajadas')
                                    ->hidden()
                                    ->required(),
                            ])
                    ]),
                Section::make('Notas')
                    ->schema([
                        Forms\Components\TextInput::make('notas')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Operador')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lugar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_start')
                    ->label('Fecha de inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_final')
                    ->label('Fecha finalización')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado'),
                Tables\Columns\TextColumn::make('facturado'),
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
                    Action::make('marcarComoFacturado')
                        ->label('Marcar Facturado')
                        ->icon('heroicon-o-check-circle')
                        ->visible(fn($record) => $record->facturado === 'No')
                        ->action(function (Service $record) {
                            $record->update(['facturado' => 'Si']);
                        }),
                    Action::make('marcarComoCompletado')
                        ->label('Marcar Completado')
                        ->icon('heroicon-o-document-check')
                        ->visible(fn($record) => $record->estado !== 'Completado')
                        ->action(function (Service $record) {
                            $record->update(['estado' => 'Completado']);

                            // Enviar correo a contabilidad
                            $emailContabilidad = 'anthonyjdiaz89@gmail.com';

                            Mail::to($emailContabilidad)
                                ->send(new ServicioCompletado($record));
                        }),
                    Action::make('crearUsage')
                        ->label('Registrar Usos')
                        ->icon('heroicon-o-plus-circle')
                        ->visible(fn($record) => $record->estado !== 'Completado')
                        ->url(fn($record) => route('filament.admin.resources.usages.create', [
                            'tipo' => 'servicio',
                            'service_id' => $record->id
                        ])),
                    Action::make('preoperativo')
                        ->label('Uso Preoperativo')
                        ->icon('heroicon-o-adjustments-vertical')
                        ->visible(fn($record) => $record->estado !== 'Completado')
                        ->url(fn($record) => route('filament.admin.resources.usages.create', [
                            'tipo' => 'preoperativo',
                            'service_id' => $record->id
                        ])),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
