<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar datos actuales desde la relación con campos pivote
        $data['generators_data'] = $this->record->generators->map(function ($generator) {
            return [
                'generators' => $generator->id,
                'horometro_inicio' => $generator->pivot->horometro_inicio,
                'horometro_fin' => $generator->pivot->horometro_fin,
                'horas_trabajadas' => $generator->pivot->horas_trabajadas,
            ];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        // Eliminar todas las relaciones existentes
        $this->record->generators()->detach();

        // Volver a agregar con los datos nuevos
        foreach ($data['generators_data'] as $generatorData) {
            $this->record->generators()->attach(
                $generatorData['generators'],
                [
                    'horometro_inicio' => $generatorData['horometro_inicio'],
                    'horometro_fin' => $generatorData['horometro_fin'],
                    'horas_trabajadas' => $generatorData['horas_trabajadas'],
                ]
            );
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
