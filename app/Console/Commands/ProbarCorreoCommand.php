<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProbarCorreoCommand extends Command
{
    protected $signature = 'ventapos:probar-correo {destino : Correo de prueba}';

    protected $description = 'Envía un correo de prueba (verifica configuración Brevo/SMTP)';

    public function handle(): int
    {
        $destino = (string) $this->argument('destino');
        $mailer = config('mail.default');

        $this->info("Mailer activo: {$mailer}");
        $this->info("From: ".config('mail.from.address'));

        try {
            Mail::raw(
                "Prueba VentasÁgil — ".now()->format('Y-m-d H:i:s')."\n\nSi recibe esto, el SMTP está bien configurado.",
                fn ($message) => $message->to($destino)->subject('Prueba correo VentasÁgil')
            );
        } catch (\Throwable $e) {
            $this->error('Fallo al enviar: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER=log — el mensaje se escribió en storage/logs/laravel.log, no se envió por internet.');
        } else {
            $this->info("Correo enviado a {$destino}. Revise bandeja y spam.");
        }

        return self::SUCCESS;
    }
}
