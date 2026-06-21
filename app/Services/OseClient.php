<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OseClient
{
    /**
     * Envía XML al OSE/PSE (API JSON genérica).
     *
     * @return array{ok: bool, codigo: string, mensaje: string, cdr?: string|null}
     */
    public function enviar(string $url, string $usuario, string $clave, string $xml, string $nombreArchivo): array
    {
        if ($url === '') {
            return [
                'ok' => false,
                'codigo' => 'CONFIG',
                'mensaje' => 'Configure la URL del OSE/PSE.',
            ];
        }

        try {
            $response = Http::timeout(45)
                ->acceptJson()
                ->post($url, [
                    'usuario' => $usuario,
                    'clave' => $clave,
                    'nombre_archivo' => $nombreArchivo,
                    'archivo_base64' => base64_encode($xml),
                ]);

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'codigo' => (string) $response->status(),
                    'mensaje' => 'Error HTTP del OSE/PSE ('.$response->status().').',
                ];
            }

            $data = $response->json();
            if (! is_array($data)) {
                return [
                    'ok' => false,
                    'codigo' => 'PARSE',
                    'mensaje' => 'Respuesta del OSE/PSE no reconocida.',
                ];
            }

            $codigo = (string) ($data['codigo'] ?? $data['codigo_respuesta'] ?? '');
            $aceptado = ($data['aceptado'] ?? $data['success'] ?? false) === true
                || in_array($codigo, ['0', '0000', '0001'], true);

            return [
                'ok' => $aceptado,
                'codigo' => $codigo !== '' ? $codigo : ($aceptado ? '0' : 'ERR'),
                'mensaje' => (string) ($data['mensaje'] ?? $data['descripcion'] ?? ($aceptado ? 'Aceptado por SUNAT' : 'Rechazado por SUNAT')),
                'cdr' => $data['cdr_base64'] ?? $data['cdr'] ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'codigo' => 'NET',
                'mensaje' => $e->getMessage(),
            ];
        }
    }
}
