<?php

namespace App\Filament\Resources\UsageResource\Pages;

use App\Filament\Resources\UsageResource;
use App\Mail\MantenimientoNotification;
use App\Models\Usage;
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
    protected $emailGerencia = 'anthonyjdiaz89@gmail.com';

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
        
        // Verificar si se necesita mantenimiento de filtro (cada 100 horas)
        if ($horasDesdeUltimoFiltro >= 100) {
            $this->notificarMantenimientoRequerido($usage->generator, 'filtro', $horasDesdeUltimoFiltro);
        }
        // Verificar si está próximo a necesitar mantenimiento de filtro (30 horas antes)
        elseif ($horasDesdeUltimoFiltro >= 70 && $horasDesdeUltimoFiltro < 100) {
            $this->notificarMantenimientoProximo($usage->generator, 'filtro', $horasDesdeUltimoFiltro, 100 - $horasDesdeUltimoFiltro);
        }
        
        // Verificar si se necesita mantenimiento de aceite (cada 200 horas)
        if ($horasDesdeUltimoAceite >= 200) {
            $this->notificarMantenimientoRequerido($usage->generator, 'aceite', $horasDesdeUltimoAceite);
        }
        // Verificar si está próximo a necesitar mantenimiento de aceite (50 horas antes)
        elseif ($horasDesdeUltimoAceite >= 150 && $horasDesdeUltimoAceite < 200) {
            $this->notificarMantenimientoProximo($usage->generator, 'aceite', $horasDesdeUltimoAceite, 200 - $horasDesdeUltimoAceite);
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
    
    protected function notificarMantenimientoRequerido($generator, $tipoMantenimiento, $horasAcumuladas): void
    {
        $limiteHoras = ($tipoMantenimiento === 'filtro') ? 100 : 200;
        
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
                false
            ));
    }
    
    protected function notificarMantenimientoProximo($generator, $tipoMantenimiento, $horasAcumuladas, $horasFaltantes): void
    {
        $limiteHoras = ($tipoMantenimiento === 'filtro') ? 100 : 200;
        
        // Formatear las horas para mostrar solo 2 decimales
        $horasFormateadas = number_format($horasAcumuladas, 2);
        $horasFaltantesFormateadas = number_format($horasFaltantes, 2);
        
        // Enviar notificación a la base de datos
        Notification::make()
            ->title('Mantenimiento próximo')
            ->body("El generador {$generator->codigo} necesitará mantenimiento de {$tipoMantenimiento} pronto. Ha acumulado {$horasFormateadas} horas desde el último mantenimiento. Faltan aproximadamente {$horasFaltantesFormateadas} horas para alcanzar el límite de {$limiteHoras} horas.")
            ->info()
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
                true
            ));
    }
}