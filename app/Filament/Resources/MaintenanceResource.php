<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
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

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $modelLabel = 'Mantenimiento';

    protected static ?string $pluralModelLabel = 'Mantenimientos';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Identificador único')
                    ->placeholder('MTO Planta 250 aceite // 25-05-2025')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Operador asignado')
                    ->required(),
                Forms\Components\Select::make('generator_id')
                    ->label('Generador')
                    ->relationship('generator', 'codigo')
                    ->required(),
                Forms\Components\Select::make('tipo_mantenimiento')
                    ->options([
                        'aceite' => 'Aceite',
                        'filtro' => 'Filtro',
                        'otro' => 'Otro',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('suplly_id', null); // Resetear el insumo cuando cambia el tipo
                    })
                    ->required(),
                Forms\Components\Select::make('suplly_id')
                    ->label('Insumo')
                    ->relationship(
                        name: 'suplly',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query, Forms\Get $get) => 
                            $query->where('tipo', $get('tipo_mantenimiento'))
                    )
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('categoria_mantenimiento')
                    ->options([
                        'preventivo' => 'Preventivo',
                        'correctivo' => 'Correctivo',
                        'predictivo' => 'Predictivo',
                        'otro' => 'Otro',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('fecha')
                    ->required(),
                Forms\Components\Select::make('provider_id')
                    ->label('Proveedor')
                    ->relationship('provider', 'nombre')
                    ->required(),
                Forms\Components\TextInput::make('descripcion')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'En proceso' => 'En proceso',
                        'Completado' => 'Completado',
                        'Cancelado' => 'Cancelado',
                    ])
                    ->default('Pendiente')
                    ->required(),
                Forms\Components\TextInput::make('costo_mantenimiento')
                    ->numeric()
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->debounce(750)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('generator.codigo')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_mantenimiento'),
                Tables\Columns\TextColumn::make('categoria_mantenimiento'),
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('provider.nombre')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('descripcion')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('estado'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('marcarComoCompletado')
                        ->label('Marcar Completado')
                        ->icon('heroicon-o-document-check')
                        ->visible(fn($record) => $record->estado !== 'Completado')
                        ->action(function (Maintenance $record) {
                            $record->update(['estado' => 'Completado']);
                        }),
                    Action::make('crearUsage')
                        ->label('Registrar Usos')
                        ->icon('heroicon-o-plus-circle')
                        ->visible(fn($record) => $record->estado !== 'Completado')
                        ->url(fn($record) => route('filament.admin.resources.usages.create', [
                            'tipo' => 'mantenimiento',
                            'service_id' => $record->id,
                            'fecha' => $record->fecha,
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}
