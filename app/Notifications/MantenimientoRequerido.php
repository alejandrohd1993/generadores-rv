<?php

namespace App\Notifications;

use App\Models\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// class MantenimientoRequerido extends Notification
// {
//     use Queueable;

//     protected $generator;
//     protected $tipoMantenimiento;

//     /**
//      * Create a new notification instance.
//      */
//     public function __construct(Generator $generator, string $tipoMantenimiento)
//     {
//         $this->generator = $generator;
//         $this->tipoMantenimiento = $tipoMantenimiento;
//     }

//     /**
//      * Get the notification's delivery channels.
//      *
//      * @return array<int, string>
//      */
//     public function via(object $notifiable): array
//     {
//         return ['database'];
//     }

//     /**
//      * Get the mail representation of the notification.
//      */
//     public function toMail(object $notifiable): MailMessage
//     {
//         return (new MailMessage)
//                     ->subject("¡URGENTE! Mantenimiento requerido para generador {$this->generator->codigo}")
//                     ->line("¡URGENTE! El generador {$this->generator->codigo} ha alcanzado el límite de horas para mantenimiento de {$this->tipoMantenimiento}.")
//                     ->action('Ver Generador', url("/admin/resources/generators/{$this->generator->id}/edit"))
//                     ->line('¡Gracias por usar nuestra aplicación!');
//     }

//     /**
//      * Get the array representation of the notification.
//      *
//      * @return array<string, mixed>
//      */
//     public function toArray(object $notifiable): array
//     {
//         return [
//             'title' => "¡URGENTE! Mantenimiento requerido: {$this->generator->codigo}",
//             'icon' => 'heroicon-o-exclamation-circle',
//             'iconColor' => 'danger',
//             'body' => "El generador {$this->generator->codigo} ha alcanzado el límite de horas para mantenimiento de {$this->tipoMantenimiento}.",
//             'actions' => [
//                 [
//                     'label' => 'Ver Generador',
//                     'url' => "/admin/resources/generators/{$this->generator->id}/edit",
//                 ],
//             ],
//             // Datos adicionales para referencia
//             'generator_id' => $this->generator->id,
//             'generator_codigo' => $this->generator->codigo,
//             'tipo_mantenimiento' => $this->tipoMantenimiento,
//         ];
//     }
// }