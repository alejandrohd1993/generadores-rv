<?php

namespace App\Filament\Resources\UsageResource\Pages;

use App\Filament\Resources\UsageResource;
use App\Mail\MantenimientoNotification;
use App\Models\Usage;
use App\Models\Suplly;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateUsage extends CreateRecord
{
    protected static string $resource = UsageResource::class;

    // Correo electrónico de gerencia
    protected ?string $emailGerencia = null;

    protected function beforeCreate(): void
    {
        $this->emailGerencia = \App\Models\Setting::first()?->maintenance_email ?? 'gerencia@example.com';
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.services.index');
    }

    protected function afterCreate(): void
    {
        // Verificar si se necesita mantenimiento
        $this->verificarMantenimientoRequerido();
    }

    protected function verificarMantenimientoRequerido(): void
    {
        // Obtener el generador del uso recién creado
        $usage = $this->record;
        $generatorId = $usage->generator_id;

        if (!$generatorId) {
            return;
        }

        // Obtener el último horometro registrado para este generador
        $ultimoHorometroStr = Usage::where('generator_id', $generatorId)
            ->orderBy('created_at', 'desc')
            ->value('horometro_fin');

        if (!$ultimoHorometroStr) {
            return;
        }

        // Convertir el horometro a horas numéricas
        $ultimoHorometro = $this->convertirHorometroAHoras($ultimoHorometroStr);

        // Obtener el último mantenimiento de filtro para este generador
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

        // Obtener el último mantenimiento de aceite para este generador
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

        // Obtener los insumos para mantenimiento de filtro y aceite
        $insumoFiltro = (float)optional($ultimoMantenimientoFiltro->reference_model?->suplly)->horas ?? 100;
        $insumoAceite = (float)optional($ultimoMantenimientoAceite->reference_model?->suplly)->horas ?? 250;

        // Obtener las horas límite para cada tipo de mantenimiento
        $horasLimiteFiltro = $insumoFiltro; // Valor por defecto si no hay insumo
        $horasLimiteAceite = $insumoAceite; // Valor por defecto si no hay insumo

        // Calcular el umbral para notificaciones de mantenimiento próximo (20% antes)
        $umbralProximoFiltro = $horasLimiteFiltro * 0.8; // 80% del límite (falta 20%)
        $umbralProximoAceite = $horasLimiteAceite * 0.8; // 80% del límite (falta 20%)

        // Calcular horas desde el último mantenimiento de filtro
        $horasDesdeUltimoFiltro = 0;
        if ($ultimoMantenimientoFiltro) {
            $horometroFiltroHoras = $this->convertirHorometroAHoras($ultimoMantenimientoFiltro->horometro_fin);
            $horasDesdeUltimoFiltro = $ultimoHorometro - $horometroFiltroHoras;
        } else {
            // Si no hay mantenimiento previo, usar todas las horas acumuladas
            $horasDesdeUltimoFiltro = $ultimoHorometro;
        }

        // Calcular horas desde el último mantenimiento de aceite
        $horasDesdeUltimoAceite = 0;
        if ($ultimoMantenimientoAceite) {
            $horometroAceiteHoras = $this->convertirHorometroAHoras($ultimoMantenimientoAceite->horometro_fin);
            $horasDesdeUltimoAceite = $ultimoHorometro - $horometroAceiteHoras;
        } else {
            // Si no hay mantenimiento previo, usar todas las horas acumuladas
            $horasDesdeUltimoAceite = $ultimoHorometro;
        }

        // Verificar si se necesita mantenimiento de filtro
        if ($horasDesdeUltimoFiltro >= $horasLimiteFiltro) {
            $this->notificarMantenimientoRequerido($usage->generator, 'filtro', $horasDesdeUltimoFiltro, $horasLimiteFiltro);
        }
        // Verificar si está próximo a necesitar mantenimiento de filtro (cuando falta el 20% de las horas)
        elseif ($horasDesdeUltimoFiltro >= $umbralProximoFiltro && $horasDesdeUltimoFiltro < $horasLimiteFiltro) {
            $this->notificarMantenimientoProximo($usage->generator, 'filtro', $horasDesdeUltimoFiltro, $horasLimiteFiltro - $horasDesdeUltimoFiltro, $horasLimiteFiltro);
        }

        // Verificar si se necesita mantenimiento de aceite
        if ($horasDesdeUltimoAceite >= $horasLimiteAceite) {
            $this->notificarMantenimientoRequerido($usage->generator, 'aceite', $horasDesdeUltimoAceite, $horasLimiteAceite);
        }
        // Verificar si está próximo a necesitar mantenimiento de aceite (cuando falta el 20% de las horas)
        elseif ($horasDesdeUltimoAceite >= $umbralProximoAceite && $horasDesdeUltimoAceite < $horasLimiteAceite) {
            $this->notificarMantenimientoProximo($usage->generator, 'aceite', $horasDesdeUltimoAceite, $horasLimiteAceite - $horasDesdeUltimoAceite, $horasLimiteAceite);
        }
    }

    /**
     * Convierte un horometro en formato "HHHH:MM:SS" a horas numéricas
     */
    protected function convertirHorometroAHoras(string $horometro): float
    {
        $partes = explode(':', $horometro);

        if (count($partes) !== 3) {
            return 0; // Formato inválido, devolver 0
        }

        $horas = (int) $partes[0];
        $minutos = (int) $partes[1];
        $segundos = (int) $partes[2];

        // Convertir todo a horas
        return $horas + ($minutos / 60) + ($segundos / 3600);
    }

    protected function notificarMantenimientoRequerido($generator, $tipoMantenimiento, $horasAcumuladas, $limiteHoras): void
    {
        // Formatear las horas acumuladas para mostrar solo 2 decimales
        $horasFormateadas = number_format($horasAcumuladas, 2);

        // Enviar notificación a la base de datos
        Notification::make()
            ->title('Mantenimiento requerido')
            ->body("El generador {$generator->codigo} requiere mantenimiento de {$tipoMantenimiento}. Ha acumulado {$horasFormateadas} horas desde el último mantenimiento (límite: {$limiteHoras} horas).")
            ->warning()
            ->actions([
                Action::make('ver_generador')
                    ->button()
                    ->url("/admin/generators/")
                    ->markAsRead()
            ])
            ->sendToDatabase(Auth::user());

        // Enviar correo electrónico
        Mail::to($this->emailGerencia)
            ->send(new MantenimientoNotification(
                $generator,
                $tipoMantenimiento,
                $horasAcumuladas,
                0,
                false,
                $limiteHoras // Pasar el límite de horas
            ));
    }

    protected function notificarMantenimientoProximo($generator, $tipoMantenimiento, $horasAcumuladas, $horasFaltantes, $limiteHoras): void
    {
        // Formatear las horas para mostrar solo 2 decimales
        $horasFormateadas = number_format($horasAcumuladas, 2);
        $horasFaltantesFormateadas = number_format($horasFaltantes, 2);

        // Enviar notificación a la base de datos
        Notification::make()
            ->title('Mantenimiento próximo')
            ->body("El generador {$generator->codigo} necesitará mantenimiento de {$tipoMantenimiento} pronto. Ha acumulado {$horasFormateadas} horas desde el último mantenimiento. Faltan aproximadamente {$horasFaltantesFormateadas} horas para alcanzar el límite de {$limiteHoras} horas.")
            ->info()
            ->persistent()
            ->actions([
                Action::make('ver_generador')
                    ->button()
                    ->url("/admin/generators/")
            ])
            ->sendToDatabase(Auth::user());

        // Enviar correo electrónico
        Mail::to($this->emailGerencia)
            ->send(new MantenimientoNotification(
                $generator,
                $tipoMantenimiento,
                $horasAcumuladas,
                $horasFaltantes,
                true,
                $limiteHoras // Pasar el límite de horas
            ));
    }
}
