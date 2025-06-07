<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Usage;
use App\Models\Suplly;
use App\Models\Setting;

Route::redirect('/', '/admin');

Route::get('/test-mantenimiento', function () {
    $generatorId = 1;
    $emailGerencia = Setting::first()?->maintenance_email ?? 'gerencia@example.com';

    // Obtener el último horómetro
    $ultimoHorometroStr = Usage::where('generator_id', $generatorId)
        ->orderBy('created_at', 'desc')
        ->value('horometro_fin');

    if (!$ultimoHorometroStr) {
        return 'No hay horómetro registrado.';
    }

    $ultimoHorometro = convertirHorometroAHoras($ultimoHorometroStr);

    // Horas límite de mantenimiento desde los insumos
    
    // Últimos mantenimientos
    $ultimoMantenimientoFiltro = Usage::where('generator_id', $generatorId)
        ->where('tipo', 'mantenimiento')
        ->whereHas('generator', function ($query) {
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('maintenances')
                    ->whereColumn('maintenances.id', 'usages.reference_id')
                    ->where('maintenances.tipo_mantenimiento', 'filtro');
            });
        })
        ->orderBy('created_at', 'desc')
        ->first();

    $ultimoMantenimientoAceite = Usage::where('generator_id', $generatorId)
        ->where('tipo', 'mantenimiento')
        ->whereHas('generator', function ($query) {
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('maintenances')
                    ->whereColumn('maintenances.id', 'usages.reference_id')
                    ->where('maintenances.tipo_mantenimiento', 'aceite');
            });
        })
        ->orderBy('created_at', 'desc')
        ->first();

    $horasLimiteFiltro = (float)optional($ultimoMantenimientoFiltro->reference_model?->suplly)->horas ?? 100;
    $horasLimiteAceite = (float)optional($ultimoMantenimientoAceite->reference_model?->suplly)->horas ?? 250;

    $umbralProximoFiltro = $horasLimiteFiltro * 0.8;
    $umbralProximoAceite = $horasLimiteAceite * 0.8;
    

    $horasDesdeUltimoFiltro = $ultimoMantenimientoFiltro
        ? $ultimoHorometro - convertirHorometroAHoras($ultimoMantenimientoFiltro->horometro_fin)
        : $ultimoHorometro;

    $horasDesdeUltimoAceite = $ultimoMantenimientoAceite
        ? $ultimoHorometro - convertirHorometroAHoras($ultimoMantenimientoAceite->horometro_fin)
        : $ultimoHorometro;

    $estado = [];

    // Estado de filtro
    if ($horasDesdeUltimoFiltro >= $horasLimiteFiltro) {
        $estado['filtro'] = 'REQUIERE MANTENIMIENTO';
    } elseif ($horasDesdeUltimoFiltro >= $umbralProximoFiltro) {
        $estado['filtro'] = 'PRÓXIMO MANTENIMIENTO';
    } else {
        $estado['filtro'] = 'FUNCIONANDO NORMAL';
    }

    // Estado de aceite
    if ($horasDesdeUltimoAceite >= $horasLimiteAceite) {
        $estado['aceite'] = 'REQUIERE MANTENIMIENTO';
    } elseif ($horasDesdeUltimoAceite >= $umbralProximoAceite) {
        $estado['aceite'] = 'PRÓXIMO MANTENIMIENTO';
    } else {
        $estado['aceite'] = 'FUNCIONANDO NORMAL';
    }

    return response()->json([
        'email_gerencia' => $emailGerencia,
        'horometro_actual' => $ultimoHorometroStr,
        'horas_actuales' => $ultimoHorometro,
        'horas_limite_filtro' => $horasLimiteFiltro,
        'horas_limite_aceite' => $horasLimiteAceite,        
        'horas_desde_ultimo_filtro' => $horasDesdeUltimoFiltro,
        'horas_desde_ultimo_aceite' => $horasDesdeUltimoAceite,
        'estado_mantenimiento' => $estado,
    ]);
});

function convertirHorometroAHoras(string $horometro): float
{
    $partes = explode(':', $horometro);
    if (count($partes) !== 3) {
        return 0;
    }

    $horas = (int)$partes[0];
    $minutos = (int)$partes[1];
    $segundos = (int)$partes[2];

    return $horas + ($minutos / 60) + ($segundos / 3600);
}