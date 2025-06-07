<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MantenimientoNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $generador;
    public $tipoMantenimiento;
    public $horasAcumuladas;
    public $horasFaltantes;
    public $esProximo;
    public $limiteHoras;

    /**
     * Create a new message instance.
     */
    public function __construct($generador, $tipoMantenimiento, $horasAcumuladas, $horasFaltantes = 0, $esProximo = false, $limiteHoras = null)
    {
        $this->generador = $generador;
        $this->tipoMantenimiento = $tipoMantenimiento;
        $this->horasAcumuladas = $horasAcumuladas;
        $this->horasFaltantes = $horasFaltantes;
        $this->esProximo = $esProximo;
        // Si no se proporciona un límite, usar los valores por defecto
        $this->limiteHoras = $limiteHoras ?? (($tipoMantenimiento === 'filtro') ? 100 : 200);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $asunto = $this->esProximo 
            ? "Mantenimiento próximo - Generador {$this->generador->codigo}" 
            : "Mantenimiento requerido - Generador {$this->generador->codigo}";
            
        return new Envelope(
            subject: $asunto,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mantenimiento-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}