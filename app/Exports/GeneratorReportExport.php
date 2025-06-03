<?php

namespace App\Exports;

use App\Models\Generator;
use App\Models\Maintenance;
use App\Models\Service;
use App\Models\Usage;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GeneratorReportExport implements FromCollection, WithHeadings, WithTitle
{
    protected ?string $desde;
    protected ?string $hasta;

    public function __construct(?string $desde, ?string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection(): Collection
    {
        return Generator::all()->map(function ($generator) {
            return [
                'Generador' => $generator->codigo,
                'Marca y Modelo' => "{$generator->marca} {$generator->modelo}",
                'Combustible (COP)' => $this->getCombustible($generator->id),
                'Otros Gastos (COP)' => $this->getOtrosGastos($generator->id),
                'Gastos Mantenimiento (COP)' => $this->getGastosMantenimiento($generator->id),
                'Total Gastos (COP)' => $this->getCombustible($generator->id)
                    + $this->getOtrosGastos($generator->id)
                    + $this->getGastosMantenimiento($generator->id),
                'Servicios' => $this->getServiciosCount($generator->id),
                'Horas Trabajadas' => $this->getHorasTrabajadas($generator->id),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Generador',
            'Marca y Modelo',
            'Combustible (COP)',
            'Otros Gastos (COP)',
            'Gastos Mantenimiento (COP)',
            'Total Gastos (COP)',
            'Servicios',
            'Horas Trabajadas',
        ];
    }

    public function title(): string
    {
        return 'Reporte Generadores';
    }

    private function getCombustible($generatorId): float
    {
        $query = Usage::where('generator_id', $generatorId);

        if ($this->desde) {
            $query->where('fecha', '>=', $this->desde);
        }

        if ($this->hasta) {
            $query->where('fecha', '<=', $this->hasta);
        }

        return $query->sum('combustible') ?? 0;
    }

    private function getOtrosGastos($generatorId): float
    {
        $query = Usage::where('generator_id', $generatorId);

        if ($this->desde) {
            $query->where('fecha', '>=', $this->desde);
        }

        if ($this->hasta) {
            $query->where('fecha', '<=', $this->hasta);
        }

        return $query->sum('otros_gastos') ?? 0;
    }

    private function getGastosMantenimiento($generatorId): float
    {
        $query = Maintenance::where('generator_id', $generatorId);

        if ($this->desde) {
            $query->where('fecha', '>=', $this->desde);
        }

        if ($this->hasta) {
            $query->where('fecha', '<=', $this->hasta);
        }

        return $query->sum('costo_mantenimiento') ?? 0;
    }

    private function getServiciosCount($generatorId): int
    {
        $query = DB::table('generator_service')
            ->join('services', 'services.id', '=', 'generator_service.service_id')
            ->where('generator_service.generator_id', $generatorId);

        if ($this->desde) {
            $query->where(function ($q) {
                $q->where('services.date_start', '>=', $this->desde)
                  ->orWhere('services.date_final', '>=', $this->desde);
            });
        }

        if ($this->hasta) {
            $query->where(function ($q) {
                $q->where('services.date_start', '<=', $this->hasta)
                  ->orWhere('services.date_final', '<=', $this->hasta);
            });
        }

        return $query->distinct('generator_service.service_id')->count('generator_service.service_id');
    }

    private function getHorasTrabajadas($generatorId): float
    {
        $query = Usage::where('generator_id', $generatorId);

        if ($this->desde) {
            $query->where('fecha', '>=', $this->desde);
        }

        if ($this->hasta) {
            $query->where('fecha', '<=', $this->hasta);
        }

        return $query->sum('horas_trabajadas') ?? 0;
    }
}
