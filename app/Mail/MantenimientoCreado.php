<?php

namespace App\Mail;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MantenimientoCreado extends Mailable
{
    use Queueable, SerializesModels;

    public $mantenimiento;
    public $generador;
    public $proveedor;

    /**
     * Create a new message instance.
     */
    public function __construct(Maintenance $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
        $this->generador = $mantenimiento->generator;
        $this->proveedor = $mantenimiento->provider;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo mantenimiento asignado: {$this->mantenimiento->nombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mantenimiento-creado',
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