<?php

namespace App\Notifications;

use App\Models\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// class MantenimientoProximo extends Notification
// {
//     use Queueable;

//     protected $generator;
//     protected $tipoMantenimiento;
//     protected $horasRestantes;

//     /**
//      * Create a new notification instance.
//      */
//     public function __construct(Generator $generator, string $tipoMantenimiento, string $horasRestantes)
//     {
//         $this->generator = $generator;
//         $this->tipoMantenimiento = $tipoMantenimiento;
//         $this->horasRestantes = $horasRestantes;
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
//                     ->subject("Mantenimiento próximo para generador {$this->generator->codigo}")
//                     ->line("El generador {$this->generator->codigo} necesitará mantenimiento de {$this->tipoMantenimiento} en aproximadamente {$this->horasRestantes} horas.")
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
//             'title' => "Mantenimiento próximo: {$this->generator->codigo}",
//             'icon' => 'heroicon-o-clock',
//             'iconColor' => 'warning',
//             'body' => "El generador {$this->generator->codigo} necesitará mantenimiento de {$this->tipoMantenimiento} en aproximadamente {$this->horasRestantes} horas.",
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
//             'horas_restantes' => $this->horasRestantes,
//         ];
//     }
// }