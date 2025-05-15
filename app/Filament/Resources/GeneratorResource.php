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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    ->required(),
                Section::make('Último Mantenimiento')
                    ->schema([
                        Forms\Components\TextInput::make('ultimo_mantenimiento_filtro')
                            ->label('Horómetro Último Mantenimiento de Filtro'),
                        Forms\Components\TextInput::make('ultimo_mantenimiento_aceite')
                            ->label('Horómetro Último Mantenimiento de Aceite'),
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
                Tables\Columns\TextColumn::make('modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('horometro')
                    ->searchable(),
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
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListGenerators::route('/'),
            'create' => Pages\CreateGenerator::route('/create'),
            'edit' => Pages\EditGenerator::route('/{record}/edit'),
        ];
    }
}
