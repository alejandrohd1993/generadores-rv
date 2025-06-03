<?php

namespace App\Exports;

use App\Models\Generator;
use App\Models\Maintenance;
use App\Models\Usage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GeneratorReportExport implements FromCollection, WithHeadings
{
    protected $desde;
    protected $hasta;

    public function __construct($desde = null, $hasta = null)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        return Generator::all()->map(function ($generator) {
            $combustible = $this->getCombustible($generator->id);
            $otrosGastos = $this->getOtrosGastos($generator->id);
            $gastosMantenimiento = $this->getGastosMantenimiento($generator->id);
            $totalGastos = $combustible + $otrosGastos + $gastosMantenimiento;
            $horasTrabajadas = $this->getHorasTrabajadas($generator->id);
            $serviciosCount = $this->getServiciosCount($generator->id);

            return [
                'codigo' => $generator->codigo,
                'marca_modelo' => $generator->marca . ' ' . $generator->modelo,
                'combustible' => $combustible,
                'otros_gastos' => $otrosGastos,
                'gastos_mantenimiento' => $gastosMantenimiento,
                'total_gastos' => $totalGastos,
                'horas_trabajadas' => $horasTrabajadas,
                'servicios' => $serviciosCount,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Código',
            'Marca / Modelo',
            'Combustible',
            'Otros Gastos',
            'Gastos Mantenimiento',
            'Total Gastos',
            'Horas Trabajadas',
            'Servicios',
        ];
    }

    private function getCombustible($id)
    {
        $query = Usage::where('generator_id', $id);
        if ($this->desde) $query->where('fecha', '>=', $this->desde);
        if ($this->hasta) $query->where('fecha', '<=', $this->hasta);
        return $query->sum('combustible') ?? 0;
    }

    private function getOtrosGastos($id)
    {
        $query = Usage::where('generator_id', $id);
        if ($this->desde) $query->where('fecha', '>=', $this->desde);
        if ($this->hasta) $query->where('fecha', '<=', $this->hasta);
        return $query->sum('otros_gastos') ?? 0;
    }

    private function getGastosMantenimiento($id)
    {
        $query = Maintenance::where('generator_id', $id);
        if ($this->desde) $query->where('fecha', '>=', $this->desde);
        if ($this->hasta) $query->where('fecha', '<=', $this->hasta);
        return $query->sum('costo_mantenimiento') ?? 0;
    }

    private function getHorasTrabajadas($id)
    {
        $query = Usage::where('generator_id', $id);
        if ($this->desde) $query->where('fecha', '>=', $this->desde);
        if ($this->hasta) $query->where('fecha', '<=', $this->hasta);
        return $query->sum('horas_trabajadas') ?? 0;
    }

    private function getServiciosCount($id)
    {
        $query = DB::table('generator_service')
            ->join('services', 'services.id', '=', 'generator_service.service_id')
            ->where('generator_service.generator_id', $id);

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
}
