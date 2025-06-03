<?php

namespace App\Filament\Pages;

use App\Models\Generator;
use App\Models\Maintenance;
use App\Models\Service;
use App\Models\Usage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Exports\GeneratorReportExport;
use App\Exports\GeneratorReportsExport;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;


class GeneratorReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.generator-reports';

    protected static ?string $navigationLabel = 'Reportes de Generadores';

    protected static ?string $title = 'Reportes de Generadores';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Reportes';

    // Variables para almacenar las fechas de filtro
    public $desde = null;
    public $hasta = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Generator::query()
                    ->select([
                        'generators.id',
                        'generators.codigo',
                        'generators.marca',
                        'generators.modelo',
                        'generators.estado',
                    ])
            )
            ->columns([
                TextColumn::make('codigo')
                    ->label('Generador')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Generator $record): string => "{$record->marca} {$record->modelo}"),

                TextColumn::make('combustible')
                    ->label('Combustible')
                    ->money('COP')
                    ->getStateUsing(function ($record) {
                        return $this->getCombustible($record->id);
                    }),

                TextColumn::make('otros_gastos')
                    ->label('Otros Gastos')
                    ->money('COP')
                    ->getStateUsing(function ($record) {
                        return $this->getOtrosGastos($record->id);
                    }),

                TextColumn::make('gastos_mantenimiento')
                    ->label('Gastos por Mto')
                    ->money('COP')
                    ->getStateUsing(function ($record) {
                        return $this->getGastosMantenimiento($record->id);
                    }),

                TextColumn::make('total_gastos')
                    ->label('Total Gastos')
                    ->money('COP')
                    ->getStateUsing(function ($record) {
                        return $this->getCombustible($record->id) +
                            $this->getOtrosGastos($record->id) +
                            $this->getGastosMantenimiento($record->id);
                    }),

                TextColumn::make('servicios_count')
                    ->label('Servicios')
                    ->getStateUsing(function ($record) {
                        return $this->getServiciosCount($record->id);
                    }),

                TextColumn::make('horas_trabajadas')
                    ->label('Horas Trabajadas')
                    ->getStateUsing(function ($record) {
                        return $this->getHorasTrabajadas($record->id);
                    }),
            ])
            ->headerActions([
                ExportAction::make('exportar_excel')
                    ->label('Exportar Excel')
                    ->action(function () {
                        return Excel::download(
                            new GeneratorReportExport($this->desde, $this->hasta),
                            'reporte_generadores.xlsx'
                        );
                    }),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde'),
                        DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Guardamos las fechas para usarlas en las subconsultas
                        $this->desde = $data['desde'] ?? null;
                        $this->hasta = $data['hasta'] ?? null;

                        // No necesitamos filtrar aquí, ya que lo hacemos en las subconsultas
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde ' . $data['desde'];
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta ' . $data['hasta'];
                        }

                        return $indicators;
                    }),
            ])
            ->defaultSort('codigo', 'asc')
            ->paginated([
                10,
                25,
                50,
                100
            ]);
    }

    // Métodos para obtener los datos con filtros de fecha aplicados
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
