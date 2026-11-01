<?php

namespace App\Services\Sunat;

use App\Models\Comprobante;
use Illuminate\Support\Facades\Http;

class NubefactOseProvider implements OseProviderInterface
{
    private const API_BASE = 'https://api.nubefact.com/api/v1';

    /** @param  array<string, mixed>  $cfg */
    public function emitir(Comprobante $comprobante, string $xml, array $cfg): OseResult
    {
        $ruta = trim((string) ($cfg['nubefact_ruta'] ?? ''));
        $token = trim((string) ($cfg['nubefact_token'] ?? ''));

        if ($ruta === '' || $token === '') {
            return new OseResult(false, 'CONFIG', 'Configure la ruta y token de Nubefact.');
        }

        $payload = $this->armarPayload($comprobante);

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Token token="'.$token.'"',
                ])
                ->acceptJson()
                ->post(self::API_BASE.'/'.$ruta, $payload);

            if (! $response->successful()) {
                return new OseResult(
                    false,
                    (string) $response->status(),
                    'Error HTTP Nubefact ('.$response->status().').',
                );
            }

            $data = $response->json();
            if (! is_array($data)) {
                return new OseResult(false, 'PARSE', 'Respuesta Nubefact no reconocida.');
            }

            $errors = trim((string) ($data['errors'] ?? ''));
            if ($errors !== '') {
                return new OseResult(false, 'NUBEFACT', $errors);
            }

            $codigo = (string) ($data['codigo'] ?? $data['sunat_responsecode'] ?? '');
            $descripcion = trim((string) ($data['sunat_description'] ?? ''));
            $aceptado = in_array(strtoupper($descripcion), ['ACEPTADO', 'ACEPTADA'], true)
                || in_array($codigo, ['0', '0000'], true);

            $hash = $this->extraerHash($data);

            return new OseResult(
                $aceptado,
                $codigo !== '' ? $codigo : ($aceptado ? '0' : 'ERR'),
                $descripcion !== '' ? $descripcion : ($aceptado ? 'Aceptado por SUNAT vía Nubefact' : 'Rechazado por Nubefact/SUNAT'),
                $data['cdr_base64'] ?? $data['cdr'] ?? null,
                $hash,
                $data['xml'] ?? null,
            );
        } catch (\Throwable $e) {
            return new OseResult(false, 'NET', $e->getMessage());
        }
    }

    /** @param  array<string, mixed>  $cfg */
    public function anular(Comprobante $comprobante, array $cfg): OseResult
    {
        $ruta = trim((string) ($cfg['nubefact_ruta'] ?? ''));
        $token = trim((string) ($cfg['nubefact_token'] ?? ''));

        if ($ruta === '' || $token === '') {
            return new OseResult(false, 'CONFIG', 'Configure la ruta y token de Nubefact.');
        }

        $tipo = $comprobante->tipo_documento === 'factura' ? 1 : 2;

        $payload = [
            'operacion' => 'generar_anulacion',
            'tipo_de_comprobante' => $tipo,
            'serie' => $comprobante->serie,
            'numero' => $comprobante->numero,
            'motivo' => $comprobante->motivo_anulacion ?? 'Anulación de comprobante',
        ];

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Authorization' => 'Token token="'.$token.'"'])
                ->acceptJson()
                ->post(self::API_BASE.'/'.$ruta, $payload);

            if (! $response->successful()) {
                return new OseResult(false, (string) $response->status(), 'Error HTTP Nubefact en anulación.');
            }

            $data = $response->json();
            if (! is_array($data)) {
                return new OseResult(false, 'PARSE', 'Respuesta Nubefact no reconocida.');
            }

            $errors = trim((string) ($data['errors'] ?? ''));
            if ($errors !== '') {
                return new OseResult(false, 'NUBEFACT', $errors);
            }

            $descripcion = trim((string) ($data['sunat_description'] ?? 'Anulación registrada'));

            return new OseResult(true, '0', $descripcion);
        } catch (\Throwable $e) {
            return new OseResult(false, 'NET', $e->getMessage());
        }
    }

    /** @return array<string, mixed> */
    private function armarPayload(Comprobante $comprobante): array
    {
        $comprobante->loadMissing(['detalles.producto', 'cliente', 'empresa']);

        $tipo = $comprobante->tipo_documento === 'factura' ? 1 : 2;
        [$tipoCliente, $numCliente, $nombreCliente] = $this->datosCliente($comprobante);

        $total = round((float) $comprobante->total, 2);
        $igv = round((float) $comprobante->valor_igv, 2);
        $gravada = round(max(0, $total - $igv), 2);

        $items = [];
        foreach ($comprobante->detalles as $i => $detalle) {
            $afecto = (bool) ($detalle->producto?->afecto_igv ?? true);
            $lineaTotal = round((float) $detalle->subtotal, 2);
            $lineaIgv = $afecto ? round($lineaTotal * 0.18 / 1.18, 2) : 0;
            $lineaBase = round($lineaTotal - $lineaIgv, 2);
            $cantidad = (float) $detalle->cantidad;
            $valorUnitario = $cantidad > 0 ? round($lineaBase / $cantidad, 4) : 0;
            $precioUnitario = $cantidad > 0 ? round($lineaTotal / $cantidad, 4) : 0;

            $items[] = [
                'unidad_de_medida' => 'NIU',
                'codigo' => $detalle->producto?->sku ?: ('P'.($i + 1)),
                'descripcion' => $detalle->descripcion_producto,
                'cantidad' => $cantidad,
                'valor_unitario' => $valorUnitario,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $lineaBase,
                'tipo_de_igv' => $afecto ? 1 : 8,
                'igv' => $lineaIgv,
                'total' => $lineaTotal,
            ];
        }

        return [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => $tipo,
            'serie' => $comprobante->serie,
            'numero' => $comprobante->numero,
            'sunat_transaction' => 1,
            'cliente_tipo_de_documento' => $tipoCliente,
            'cliente_numero_de_documento' => $numCliente,
            'cliente_denominacion' => $nombreCliente,
            'cliente_direccion' => $comprobante->cliente?->direccion ?? '',
            'cliente_email' => $comprobante->cliente?->email ?? '',
            'fecha_de_emision' => $comprobante->emitido_en->format('d-m-Y'),
            'moneda' => 1,
            'porcentaje_de_igv' => 18.00,
            'total_gravada' => $gravada,
            'total_igv' => $igv,
            'total' => $total,
            'enviar_automaticamente_a_la_sunat' => true,
            'codigo_unico' => $comprobante->numero_interno,
            'items' => $items,
        ];
    }

    /** @return array{0: int|string, 1: string, 2: string} */
    private function datosCliente(Comprobante $comprobante): array
    {
        if (! $comprobante->cliente) {
            return ['-', '00000000', 'CLIENTE VARIOS'];
        }

        $map = config('sunat.nubefact_tipos_documento_cliente', []);
        $tipo = $map[$comprobante->cliente->tipo_documento] ?? '-';
        $numero = $comprobante->cliente->numero_documento ?: '00000000';
        $nombre = $comprobante->cliente->nombre ?: 'CLIENTE VARIOS';

        return [$tipo, $numero, $nombre];
    }

    /** @param  array<string, mixed>  $data */
    private function extraerHash(array $data): ?string
    {
        if (! empty($data['codigo_hash'])) {
            return substr((string) $data['codigo_hash'], 0, 40);
        }

        $cadena = (string) ($data['cadena_para_codigo_qr'] ?? '');
        if ($cadena === '') {
            return null;
        }

        $partes = explode('|', $cadena);

        return isset($partes[9]) && $partes[9] !== '' ? substr($partes[9], 0, 40) : null;
    }
}
