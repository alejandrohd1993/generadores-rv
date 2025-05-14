<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

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
