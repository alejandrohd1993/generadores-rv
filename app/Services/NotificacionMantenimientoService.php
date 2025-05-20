<?php

namespace App\Services;

use App\Models\Generator;
use App\Models\User;
use App\Notifications\MantenimientoProximo;
use App\Notifications\MantenimientoRequerido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// class NotificacionMantenimientoService
// {
//     /**
//      * Verifica si es necesario enviar notificaciones de mantenimiento para un generador
//      */
//     public function verificarNotificaciones(Generator $generator): void
//     {
//         $this->verificarMantenimientoFiltro($generator);
//         $this->verificarMantenimientoAceite($generator);
//     }

//     /**
//      * Verifica si es necesario enviar notificación para mantenimiento de filtro
//      */
//     private function verificarMantenimientoFiltro(Generator $generator): void
//     {
//         $ultimoHorometro = $generator->usages()->orderBy('created_at', 'desc')->first()?->horometro_fin ?? $generator->horometro;
        
//         // Obtener el último mantenimiento de filtro
//         $ultimoMantenimientoFiltro = $generator->usages()
//             ->where('tipo', 'mantenimiento')
//             ->whereExists(function ($query) {
//                 $query->select(DB::raw(1))
//                     ->from('maintenances')
//                     ->whereColumn('maintenances.id', 'usages.reference_id')
//                     ->where('maintenances.tipo_mantenimiento', 'Filtro');
//             })
//             ->orderBy('created_at', 'desc')
//             ->first();
        
//         $horometroUltimoMantenimiento = $ultimoMantenimientoFiltro 
//             ? $ultimoMantenimientoFiltro->horometro_fin 
//             : $generator->ultimo_mantenimiento_filtro;
        
//         if (!$ultimoHorometro || !$horometroUltimoMantenimiento) {
//             return;
//         }
        
//         $horasUltimoMantenimiento = $this->convertirHorasADecimal($horometroUltimoMantenimiento);
//         $horasActuales = $this->convertirHorasADecimal($ultimoHorometro);
//         $horasTranscurridas = $horasActuales - $horasUltimoMantenimiento;
//         $horasRestantes = 100 - $horasTranscurridas;
        
//         // Verificar si ya existe una notificación pendiente para este mantenimiento
//         $notificacionExistente = $this->existeNotificacionReciente($generator->id, 'Filtro');
        
//         // Enviar notificación cuando falten 30 horas
//         if ($horasRestantes <= 30 && $horasRestantes > 0 && !$notificacionExistente) {
//             $this->notificarMantenimientoProximo($generator, 'Filtro', $this->convertirDecimalAHoras($horasRestantes));
//         }
        
//         // Enviar notificación cuando se cumpla el tiempo
//         if ($horasRestantes <= 0 && !$notificacionExistente) {
//             $this->notificarMantenimientoRequerido($generator, 'Filtro');
//         }
//     }

//     /**
//      * Verifica si es necesario enviar notificación para mantenimiento de aceite
//      */
//     private function verificarMantenimientoAceite(Generator $generator): void
//     {
//         $ultimoHorometro = $generator->usages()->orderBy('created_at', 'desc')->first()?->horometro_fin ?? $generator->horometro;
        
//         // Obtener el último mantenimiento de aceite
//         $ultimoMantenimientoAceite = $generator->usages()
//             ->where('tipo', 'mantenimiento')
//             ->whereExists(function ($query) {
//                 $query->select(DB::raw(1))
//                     ->from('maintenances')
//                     ->whereColumn('maintenances.id', 'usages.reference_id')
//                     ->where('maintenances.tipo_mantenimiento', 'Aceite');
//             })
//             ->orderBy('created_at', 'desc')
//             ->first();
        
//         $horometroUltimoMantenimiento = $ultimoMantenimientoAceite 
//             ? $ultimoMantenimientoAceite->horometro_fin 
//             : $generator->ultimo_mantenimiento_aceite;
        
//         if (!$ultimoHorometro || !$horometroUltimoMantenimiento) {
//             return;
//         }
        
//         $horasUltimoMantenimiento = $this->convertirHorasADecimal($horometroUltimoMantenimiento);
//         $horasActuales = $this->convertirHorasADecimal($ultimoHorometro);
//         $horasTranscurridas = $horasActuales - $horasUltimoMantenimiento;
//         $horasRestantes = 200 - $horasTranscurridas;
        
//         // Verificar si ya existe una notificación pendiente para este mantenimiento
//         $notificacionExistente = $this->existeNotificacionReciente($generator->id, 'Aceite');
        
//         // Enviar notificación cuando falten 50 horas
//         if ($horasRestantes <= 50 && $horasRestantes > 0 && !$notificacionExistente) {
//             $this->notificarMantenimientoProximo($generator, 'Aceite', $this->convertirDecimalAHoras($horasRestantes));
//         }
        
//         // Enviar notificación cuando se cumpla el tiempo
//         if ($horasRestantes <= 0 && !$notificacionExistente) {
//             $this->notificarMantenimientoRequerido($generator, 'Aceite');
//         }
//     }

//     /**
//      * Verifica si existe una notificación reciente para este generador y tipo de mantenimiento
//      */
//     private function existeNotificacionReciente(int $generatorId, string $tipoMantenimiento): bool
//     {
//         return DB::table('notifications')
//             ->where('created_at', '>=', now()->subHours(24))
//             ->whereJsonContains('data->generator_id', $generatorId)
//             ->whereJsonContains('data->tipo_mantenimiento', $tipoMantenimiento)
//             ->exists();
//     }

//     /**
//      * Envía una notificación de mantenimiento próximo a todos los usuarios
//      */
//     private function notificarMantenimientoProximo(Generator $generator, string $tipoMantenimiento, string $horasRestantes): void
//     {
//         $users = User::all();
//         Notification::send($users, new MantenimientoProximo($generator, $tipoMantenimiento, $horasRestantes));
//     }

//     /**
//      * Envía una notificación de mantenimiento requerido a todos los usuarios
//      */
//     private function notificarMantenimientoRequerido(Generator $generator, string $tipoMantenimiento): void
//     {
//         $users = User::all();
//         Notification::send($users, new MantenimientoRequerido($generator, $tipoMantenimiento));
//     }

//     /**
//      * Convierte un formato de horas (HH:MM:SS) a decimal
//      */
//     private function convertirHorasADecimal(string $horometro): float
//     {
//         $partes = explode(':', $horometro);
        
//         if (count($partes) !== 3) {
//             return 0;
//         }
        
//         $horas = (int) $partes[0];
//         $minutos = (int) $partes[1];
//         $segundos = (int) $partes[2];
        
//         return $horas + ($minutos / 60) + ($segundos / 3600);
//     }

//     /**
//      * Convierte un valor decimal a formato de horas (HH:MM:SS)
//      */
//     private function convertirDecimalAHoras(float $decimal): string
//     {
//         $horas = floor($decimal);
//         $minutos = floor(($decimal - $horas) * 60);
//         $segundos = floor((($decimal - $horas) * 60 - $minutos) * 60);
        
//         return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
//     }
// }