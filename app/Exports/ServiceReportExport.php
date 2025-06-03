<?php

namespace App\Exports;

use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ServiceReportExport
{
    public static function make(): ExcelExport
    {
        return ExcelExport::make()
            ->fromTable()
            ->withColumns([
                Column::make('valor_servicio')
                    ->format(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1),

                Column::make('presupuesto_viaticos')
                    ->format(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1),

                Column::make('combustible')
                    ->format(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1),

                Column::make('otros_gastos_total')
                    ->getStateUsing(function ($record) {
                        return ($record->presupuesto_otros_gastos ?? 0) + ($record->usages_otros_gastos ?? 0);
                    })
                    ->format(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1),

                Column::make('ingreso_neto')
                    ->getStateUsing(function ($record) {
                        return ($record->valor_servicio ?? 0)
                            - ($record->presupuesto_viaticos ?? 0)
                            - ($record->combustible ?? 0)
                            - ($record->presupuesto_otros_gastos ?? 0)
                            - ($record->usages_otros_gastos ?? 0);
                    })
                    ->format(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1),
            ]);
    }
}
