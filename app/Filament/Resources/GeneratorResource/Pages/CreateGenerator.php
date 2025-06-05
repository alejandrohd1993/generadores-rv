<?php

namespace App\Filament\Resources\GeneratorResource\Pages;

use App\Filament\Resources\GeneratorResource;
use App\Models\Maintenance;
use App\Models\Service;
use App\Models\Usage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGenerator extends CreateRecord
{
    protected static string $resource = GeneratorResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        // Crear registro de uso para mantenimiento de filtro si se proporcionó la fecha
        if (isset($data['ultimo_mantenimiento_filtro']) && $data['ultimo_mantenimiento_filtro']) {
            $this->createUsageRecord('filtro', $data['ultimo_mantenimiento_filtro'], $data['filtro_suplly_id'] ?? null);
        }

        // Crear registro de uso para mantenimiento de aceite si se proporcionó la fecha
        if (isset($data['ultimo_mantenimiento_aceite']) && $data['ultimo_mantenimiento_aceite']) {
            $this->createUsageRecord('aceite', $data['ultimo_mantenimiento_aceite'], $data['aceite_suplly_id'] ?? null);
        }

        $this->createInitialUsage();
    }

    protected function createUsageRecord(string $tipoMantenimiento, string $horometro, ?int $supllyId = null): void
    {
        // Crear un mantenimiento ficticio para asociarlo al uso
        $maintenance = Maintenance::create([
            'nombre' => 'Mantenimiento inicial de ' . $tipoMantenimiento,
            'user_id' => 1,
            'generator_id' => $this->record->id,
            'tipo_mantenimiento' => $tipoMantenimiento,
            'categoria_mantenimiento' => 'preventivo',
            'fecha' => '2025-01-01',
            'provider_id' => 1,
            'estado' => 'Completado',
            'suplly_id' => $supllyId,
        ]);

        // Crear el registro de uso asociado al mantenimiento
        Usage::create([
            'fecha' => '2025-01-01',
            'generator_id' => $this->record->id,
            'tipo' => 'mantenimiento',
            'reference_id' => $maintenance->id,
            'horometro_inicio' => $horometro,
            'horometro_fin' => $horometro,
            'horas_trabajadas' => 0,
        ]);
    }

    protected function createInitialUsage(): void
    {
        Usage::make()->forceFill([
            'fecha' => '2025-01-01',
            'generator_id' => $this->record->id,
            'tipo' => 'preoperativo',
            'reference_id' => 1,
            'horometro_inicio' => $this->record->horometro,
            'horometro_fin' => $this->record->horometro,
            'horas_trabajadas' => 0,
            'created_at' => now()->addSeconds(5),
        ])->save();
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
