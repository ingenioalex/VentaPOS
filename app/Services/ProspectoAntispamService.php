<?php

namespace App\Services;

use Illuminate\Http\Request;

class ProspectoAntispamService
{
    /** @var list<string> */
    private const PATRONES_SPAM = [
        'seo',
        'outreach',
        'telegram',
        'whatsapp me',
        'automated website',
        'ranking',
        'backlink',
        'optimizar el proceso',
        'implementación en ia',
        'implementacion en ia',
        'artificial intelligence',
        'marketing agency',
        'click here',
        'ventasagil.com',
        'hi!',
        'hello ventasagil',
    ];

    public function esBot(Request $request): bool
    {
        if (filled($request->input('empresa_web'))) {
            return true;
        }

        $inicio = (int) $request->input('formulario_inicio', 0);
        if ($inicio > 0 && (time() - $inicio) < 4) {
            return true;
        }

        $mensaje = strtolower((string) $request->input('mensaje', ''));
        $contacto = strtolower((string) $request->input('nombre_contacto', ''));
        $negocio = strtolower((string) $request->input('nombre_negocio', ''));

        foreach (self::PATRONES_SPAM as $patron) {
            if (str_contains($mensaje, $patron) || str_contains($contacto, $patron)) {
                return true;
            }
        }

        if (preg_match('/^[a-z]+bof$/i', $negocio) || preg_match('/^[a-z]+bof$/i', $contacto)) {
            return true;
        }

        if (preg_match('/\b(mkt|marketing)\b/i', $contacto) && strlen($mensaje) > 80) {
            return true;
        }

        return false;
    }
}
