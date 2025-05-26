<?php

namespace App\Filament\Pages;

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

class ServiceReports extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static string $view = 'filament.pages.service-reports';
    
    protected static ?string $navigationLabel = 'Reportes de Servicios';
    
    protected static ?string $title = 'Reportes de Servicios';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Reportes';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Service::query()
                    ->select([
                        'services.id',
                        'services.nombre',
                        'services.date_start',
                        'services.date_final',
                        'services.valor_servicio',
                        'services.presupuesto_viaticos',
                        'services.presupuesto_otros_gastos',
                        DB::raw('(SELECT SUM(combustible) FROM usages WHERE tipo = "servicio" AND reference_id = services.id) as combustible'),
                        DB::raw('(SELECT SUM(otros_gastos) FROM usages WHERE tipo = "servicio" AND reference_id = services.id) as usages_otros_gastos'),
                    ])
            )
            ->columns([
                TextColumn::make('nombre')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('date_start')
                    ->label('Fecha Inicio')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('date_final')
                    ->label('Fecha Final')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('valor_servicio')
                    ->label('Valor Servicio')
                    ->money('COP')
                    ->sortable(),
                    
                TextColumn::make('presupuesto_viaticos')
                    ->label('Viáticos')
                    ->money('COP')
                    ->sortable(),
                    
                TextColumn::make('combustible')
                    ->label('Combustible')
                    ->money('COP')
                    ->getStateUsing(function ($record) {
                        return $record->combustible ?? 0;
                    }),
                    
                TextColumn::make('otros_gastos_total')
                    ->label('Otros Gastos')
                    ->money('COP')
                    ->getStateUsing(function ($record) {
                        return ($record->presupuesto_otros_gastos ?? 0) + ($record->usages_otros_gastos ?? 0);
                    }),
                    
                TextColumn::make('ingreso_neto')
                    ->label('Ingreso Neto')
                    ->money('COP')
                    ->getStateUsing(function ($record) {
                        $gastos = ($record->presupuesto_viaticos ?? 0) + 
                                 ($record->combustible ?? 0) + 
                                 ($record->presupuesto_otros_gastos ?? 0) + 
                                 ($record->usages_otros_gastos ?? 0);
                        
                        return ($record->valor_servicio ?? 0) - $gastos;
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
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_start', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_final', '<=', $date),
                            );
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
            ->defaultSort('date_start', 'desc')
            ->paginated([
                10, 25, 50, 100
            ]);
    }
}