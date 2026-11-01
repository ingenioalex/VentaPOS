<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminRecuperacionClaveMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $tokenPlano,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecer clave — Panel VentasÁgil',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-recuperacion-clave',
            with: [
                'nombre' => $this->usuario->nombre,
                'url' => route('admin.recuperar.restablecer', ['token' => $this->tokenPlano]),
                'expiraMinutos' => 60,
            ],
        );
    }
}
