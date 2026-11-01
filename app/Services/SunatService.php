<?php

namespace App\Services;

use App\Models\Comprobante;
use App\Models\Empresa;
use App\Services\Sunat\OseProviderFactory;
use App\Services\Sunat\OseResult;
use App\Services\Sunat\SunatCredentialCipher;
use App\Services\Sunat\SunatXmlSigner;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SunatService
{
    public function __construct(
        private OseProviderFactory $oseFactory,
        private SunatCredentialCipher $cipher,
        private SunatXmlSigner $signer,
    ) {}

    public function estaActivo(Empresa $empresa): bool
    {
        return (bool) ($this->config($empresa)['activo'] ?? false);
    }

    /** @return array<string, mixed> */
    public function config(Empresa $empresa): array
    {
        $cfg = $empresa->configuracion['sunat'] ?? [];
        $merged = array_merge([
            'activo' => false,
            'modo' => 'simulacion',
            'proveedor_ose' => 'generico',
            'usuario_sol' => '',
            'clave_sol' => '',
            'certificado' => '',
            'clave_certificado' => '',
            'ose_url' => '',
            'nubefact_ruta' => '',
            'nubefact_token' => '',
        ], is_array($cfg) ? $cfg : []);

        $merged['clave_sol'] = $this->cipher->unseal((string) ($merged['clave_sol'] ?? ''));
        $merged['nubefact_token'] = $this->cipher->unseal((string) ($merged['nubefact_token'] ?? ''));

        return $merged;
    }

    /** @param  array<string, mixed>  $datos */
    public function guardarConfig(Empresa $empresa, array $datos): void
    {
        $config = $empresa->configuracion ?? [];
        $prev = $empresa->configuracion['sunat'] ?? [];

        $proveedor = in_array($datos['proveedor_ose'] ?? '', ['generico', 'nubefact'], true)
            ? $datos['proveedor_ose']
            : ($prev['proveedor_ose'] ?? 'generico');

        $claveSol = $datos['clave_sol'] !== '' ? $datos['clave_sol'] : ($prev['clave_sol'] ?? '');
        $nubefactToken = $datos['nubefact_token'] !== '' ? $datos['nubefact_token'] : ($prev['nubefact_token'] ?? '');

        if ($claveSol !== '' && ! str_starts_with((string) $claveSol, 'enc:')) {
            $claveSol = $this->cipher->seal((string) $claveSol);
        }
        if ($nubefactToken !== '' && ! str_starts_with((string) $nubefactToken, 'enc:')) {
            $nubefactToken = $this->cipher->seal((string) $nubefactToken);
        }

        $config['sunat'] = [
            'activo' => (bool) ($datos['activo'] ?? false),
            'modo' => in_array($datos['modo'] ?? '', ['simulacion', 'produccion'], true) ? $datos['modo'] : 'simulacion',
            'proveedor_ose' => $proveedor,
            'usuario_sol' => trim($datos['usuario_sol'] ?? ''),
            'clave_sol' => $claveSol,
            'certificado' => trim($datos['certificado'] ?? ''),
            'clave_certificado' => $datos['clave_certificado'] !== ''
                ? $this->cipher->seal((string) $datos['clave_certificado'])
                : ($prev['clave_certificado'] ?? ''),
            'ose_url' => trim($datos['ose_url'] ?? ''),
            'nubefact_ruta' => trim($datos['nubefact_ruta'] ?? ''),
            'nubefact_token' => $nubefactToken,
        ];

        $empresa->update(['configuracion' => $config]);
    }

    public function emitir(Comprobante $comprobante): Comprobante
    {
        $comprobante = $this->prepararEmision($comprobante);

        if (in_array($comprobante->sunat_estado, ['simulado', 'omitido'], true)) {
            return $comprobante;
        }

        return $this->completarEmision($comprobante);
    }

    /** Genera XML UBL y hash local (rápido). El ticket puede mostrar QR de inmediato. */
    public function prepararEmision(Comprobante $comprobante): Comprobante
    {
        $comprobante->load(['detalles.producto', 'cliente', 'empresa', 'usuario']);

        if ($comprobante->estado !== 'emitido') {
            throw new RuntimeException('Solo se envían a SUNAT comprobantes emitidos.');
        }

        if (! in_array($comprobante->tipo_documento, ['boleta', 'factura'], true)) {
            $comprobante->update(['sunat_estado' => 'omitido']);

            return $comprobante->fresh();
        }

        $empresa = $comprobante->empresa;
        if (! $empresa || ! $this->estaActivo($empresa)) {
            $comprobante->update(['sunat_estado' => 'omitido']);

            return $comprobante->fresh();
        }

        if (empty($empresa->ruc) || strlen((string) $empresa->ruc) !== 11) {
            throw new RuntimeException('Configure el RUC de la empresa (11 dígitos) antes de emitir electrónicamente.');
        }

        if ($comprobante->sunat_hash && $comprobante->sunat_xml) {
            return $comprobante->fresh();
        }

        $cfg = $this->config($empresa);
        $payload = $this->armarPayload($comprobante, $empresa);
        $xml = $this->generarXml($payload);
        $digest = $this->generarDigestCpe($xml);
        $ruta = $this->guardarXml($empresa->id, $comprobante, $xml);

        $comprobante->update([
            'sunat_estado' => 'pendiente',
            'sunat_hash' => $digest,
            'sunat_xml' => $ruta,
            'sunat_cdr' => null,
            'sunat_mensaje' => 'Enviando a SUNAT en segundo plano…',
        ]);

        if ($cfg['modo'] === 'simulacion') {
            $comprobante->update([
                'sunat_estado' => 'simulado',
                'sunat_codigo_respuesta' => '0',
                'sunat_mensaje' => 'Aceptado en modo simulación (sin envío a SUNAT).',
                'sunat_enviado_en' => now(),
            ]);
        }

        return $comprobante->fresh();
    }

    /** Garantiza XML + hash para el QR (boleta/factura, cualquier medio de pago). */
    public function asegurarHashQr(Comprobante $comprobante): Comprobante
    {
        if ($comprobante->estado !== 'emitido') {
            return $comprobante;
        }

        if (! in_array($comprobante->tipo_documento, ['boleta', 'factura'], true)) {
            return $comprobante;
        }

        $comprobante->loadMissing(['detalles.producto', 'cliente', 'empresa', 'usuario']);

        if (! $comprobante->empresa || ! $this->estaActivo($comprobante->empresa)) {
            return $comprobante;
        }

        if ($comprobante->sunat_hash && $comprobante->sunat_xml) {
            return $comprobante->fresh();
        }

        return $this->prepararEmision($comprobante);
    }

    /** Envío al OSE/PSE (lento). Se ejecuta en cola tras prepararEmision. */
    public function completarEmision(Comprobante $comprobante): Comprobante
    {
        $comprobante->load(['detalles.producto', 'cliente', 'empresa', 'usuario']);

        if (in_array($comprobante->sunat_estado, ['simulado', 'omitido', 'aceptado'], true)) {
            return $comprobante;
        }

        $empresa = $comprobante->empresa;
        if (! $empresa || ! $this->estaActivo($empresa)) {
            return $comprobante;
        }

        $cfg = $this->config($empresa);
        if ($cfg['modo'] === 'simulacion') {
            return $comprobante;
        }

        if (! $comprobante->sunat_hash || ! $comprobante->sunat_xml) {
            $comprobante = $this->prepararEmision($comprobante);
        }

        $xml = $this->obtenerORegenerarXml($comprobante);

        return $this->enviarProduccion($comprobante, $cfg, $xml);
    }

    public function reenviar(Comprobante $comprobante): Comprobante
    {
        if (! in_array($comprobante->sunat_estado, ['rechazado', 'error', 'pendiente'], true)) {
            throw new RuntimeException('Este comprobante no requiere reenvío.');
        }

        return $this->completarEmision($comprobante->fresh());
    }

    /** Regenera o lee el XML UBL guardado (útil si el archivo se perdió en el servidor). */
    public function obtenerORegenerarXml(Comprobante $comprobante): string
    {
        $comprobante->load(['detalles.producto', 'cliente', 'empresa']);

        if ($comprobante->sunat_xml) {
            if (Storage::disk('local')->exists($comprobante->sunat_xml)) {
                $contenido = Storage::disk('local')->get($comprobante->sunat_xml);

                return $contenido !== false && $contenido !== '' ? $contenido : $this->regenerarXml($comprobante);
            }
        }

        if (! in_array($comprobante->sunat_estado, ['simulado', 'aceptado', 'pendiente', 'rechazado', 'error'], true)) {
            throw new RuntimeException('Este comprobante no tiene XML electrónico.');
        }

        return $this->regenerarXml($comprobante);
    }

    private function regenerarXml(Comprobante $comprobante): string
    {
        $empresa = $comprobante->empresa;
        if (! $empresa) {
            throw new RuntimeException('Comprobante sin empresa asociada.');
        }

        $payload = $this->armarPayload($comprobante, $empresa);
        $xml = $this->generarXml($payload);
        $ruta = $this->guardarXml($empresa->id, $comprobante, $xml);

        $comprobante->update([
            'sunat_xml' => $ruta,
            'sunat_hash' => $this->generarDigestCpe($xml),
        ]);

        return $xml;
    }

    public function comunicarAnulacion(Comprobante $comprobante): void
    {
        $comprobante->load('empresa');

        if (! $comprobante->empresa || ! $this->estaActivo($comprobante->empresa)) {
            return;
        }

        if (! in_array($comprobante->sunat_estado, ['simulado', 'aceptado'], true)) {
            return;
        }

        $cfg = $this->config($comprobante->empresa);

        if ($cfg['modo'] === 'simulacion') {
            $comprobante->update([
                'sunat_mensaje' => trim(($comprobante->sunat_mensaje ?? '').' | Anulación registrada (simulación).'),
            ]);

            return;
        }

        if (! $this->produccionConfigurada($cfg)) {
            $comprobante->update([
                'sunat_mensaje' => trim(($comprobante->sunat_mensaje ?? '').' | Anulación pendiente: configure OSE/PSE.'),
            ]);

            return;
        }

        $resultado = $this->oseFactory->resolver($cfg)->anular($comprobante, $cfg);

        $comprobante->update([
            'sunat_mensaje' => trim(($comprobante->sunat_mensaje ?? '').' | Anulación: '.$resultado->mensaje),
        ]);
    }

    /** @return array<string, mixed> */
    public function armarPayload(Comprobante $comprobante, Empresa $empresa): array
    {
        $tipoSunat = config('sunat.tipos_comprobante.'.$comprobante->tipo_documento, '03');
        $clienteDoc = $comprobante->cliente?->numero_documento ?? '00000000';
        $clienteTipo = config('sunat.tipos_documento_cliente.'.$comprobante->cliente?->tipo_documento, '0');

        $lineas = [];
        foreach ($comprobante->detalles as $i => $d) {
            $lineas[] = [
                'numero' => $i + 1,
                'descripcion' => $d->descripcion_producto,
                'cantidad' => (float) $d->cantidad,
                'precio_unitario' => (float) $d->precio_unitario,
                'subtotal' => (float) $d->subtotal,
                'afecto_igv' => (bool) ($d->producto?->afecto_igv ?? true),
            ];
        }

        $total = round((float) $comprobante->total, 2);
        $igv = round((float) $comprobante->valor_igv, 2);

        return [
            'ubl_version' => '2.1',
            'tipo_comprobante' => $tipoSunat,
            'serie' => $comprobante->serie,
            'correlativo' => str_pad((string) $comprobante->numero, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => $comprobante->emitido_en->format('Y-m-d'),
            'hora_emision' => $comprobante->emitido_en->format('H:i:s'),
            'moneda' => 'PEN',
            'emisor' => [
                'ruc' => $empresa->ruc,
                'razon_social' => $empresa->nombre,
                'direccion' => $empresa->direccion ?? '-',
            ],
            'receptor' => [
                'tipo_doc' => $clienteTipo,
                'numero_doc' => $clienteDoc,
                'nombre' => $comprobante->cliente?->nombre ?? 'CLIENTE VARIOS',
            ],
            'totales' => [
                'subtotal' => round(max(0, $total - $igv), 2),
                'igv' => $igv,
                'total' => $total,
            ],
            'lineas' => $lineas,
        ];
    }

    /** @param  array<string, mixed>  $payload */
    public function generarXml(array $payload): string
    {
        $lineasXml = '';
        foreach ($payload['lineas'] as $l) {
            $base = $l['afecto_igv']
                ? round($l['subtotal'] / 1.18, 2)
                : round($l['subtotal'], 2);
            $igvLinea = round($l['subtotal'] - $base, 2);

            $lineasXml .= sprintf(
                '<cac:InvoiceLine>'
                .'<cbc:ID>%d</cbc:ID>'
                .'<cbc:InvoicedQuantity unitCode="NIU">%s</cbc:InvoicedQuantity>'
                .'<cbc:LineExtensionAmount currencyID="PEN">%s</cbc:LineExtensionAmount>'
                .'<cac:TaxTotal><cbc:TaxAmount currencyID="PEN">%s</cbc:TaxAmount>'
                .'<cac:TaxSubtotal><cbc:TaxableAmount currencyID="PEN">%s</cbc:TaxableAmount>'
                .'<cbc:TaxAmount currencyID="PEN">%s</cbc:TaxAmount>'
                .'<cac:TaxCategory><cbc:Percent>18</cbc:Percent>'
                .'<cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme>'
                .'</cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal>'
                .'<cac:Item><cbc:Description>%s</cbc:Description></cac:Item>'
                .'<cac:Price><cbc:PriceAmount currencyID="PEN">%s</cbc:PriceAmount></cac:Price>'
                .'</cac:InvoiceLine>',
                $l['numero'],
                number_format($l['cantidad'], 2, '.', ''),
                number_format($base, 2, '.', ''),
                number_format($igvLinea, 2, '.', ''),
                number_format($base, 2, '.', ''),
                number_format($igvLinea, 2, '.', ''),
                htmlspecialchars($l['descripcion'], ENT_XML1),
                number_format($l['subtotal'] / max(0.0001, $l['cantidad']), 4, '.', ''),
            );
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" '
            .'xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" '
            .'xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">'
            .'<cbc:UBLVersionID>'.$payload['ubl_version'].'</cbc:UBLVersionID>'
            .'<cbc:CustomizationID>2.0</cbc:CustomizationID>'
            .'<cbc:ID>'.$payload['serie'].'-'.$payload['correlativo'].'</cbc:ID>'
            .'<cbc:IssueDate>'.$payload['fecha_emision'].'</cbc:IssueDate>'
            .'<cbc:IssueTime>'.$payload['hora_emision'].'</cbc:IssueTime>'
            .'<cbc:InvoiceTypeCode listID="0101">'.$payload['tipo_comprobante'].'</cbc:InvoiceTypeCode>'
            .'<cbc:DocumentCurrencyCode>'.$payload['moneda'].'</cbc:DocumentCurrencyCode>'
            .'<cac:AccountingSupplierParty><cac:Party>'
            .'<cac:PartyIdentification><cbc:ID schemeID="6">'.$payload['emisor']['ruc'].'</cbc:ID></cac:PartyIdentification>'
            .'<cac:PartyLegalEntity><cbc:RegistrationName>'
            .htmlspecialchars($payload['emisor']['razon_social'], ENT_XML1)
            .'</cbc:RegistrationName></cac:PartyLegalEntity>'
            .'</cac:Party></cac:AccountingSupplierParty>'
            .'<cac:AccountingCustomerParty><cac:Party>'
            .'<cac:PartyIdentification><cbc:ID schemeID="'.$payload['receptor']['tipo_doc'].'">'
            .$payload['receptor']['numero_doc'].'</cbc:ID></cac:PartyIdentification>'
            .'<cac:PartyLegalEntity><cbc:RegistrationName>'
            .htmlspecialchars($payload['receptor']['nombre'], ENT_XML1)
            .'</cbc:RegistrationName></cac:PartyLegalEntity>'
            .'</cac:Party></cac:AccountingCustomerParty>'
            .'<cac:TaxTotal><cbc:TaxAmount currencyID="PEN">'
            .number_format($payload['totales']['igv'], 2, '.', '')
            .'</cbc:TaxAmount></cac:TaxTotal>'
            .'<cac:LegalMonetaryTotal>'
            .'<cbc:LineExtensionAmount currencyID="PEN">'
            .number_format($payload['totales']['subtotal'], 2, '.', '')
            .'</cbc:LineExtensionAmount>'
            .'<cbc:TaxInclusiveAmount currencyID="PEN">'
            .number_format($payload['totales']['total'], 2, '.', '')
            .'</cbc:TaxInclusiveAmount>'
            .'<cbc:PayableAmount currencyID="PEN">'
            .number_format($payload['totales']['total'], 2, '.', '')
            .'</cbc:PayableAmount>'
            .'</cac:LegalMonetaryTotal>'
            .$lineasXml
            .'</Invoice>';
    }

    /** @param  array<string, mixed>  $cfg */
    private function enviarProduccion(Comprobante $comprobante, array $cfg, string $xml): Comprobante
    {
        if (! $this->produccionConfigurada($cfg)) {
            $mensaje = ($cfg['proveedor_ose'] ?? '') === 'nubefact'
                ? 'Modo producción: configure ruta y token de Nubefact.'
                : 'Modo producción: configure credenciales y URL del OSE/PSE.';

            $comprobante->update([
                'sunat_estado' => 'error',
                'sunat_codigo_respuesta' => 'CONFIG',
                'sunat_mensaje' => $mensaje,
                'sunat_enviado_en' => now(),
            ]);

            return $comprobante->fresh();
        }

        if (($cfg['proveedor_ose'] ?? 'generico') === 'generico') {
            $xml = $this->intentarFirmarXml($cfg, $xml, $comprobante);
        }

        $resultado = $this->oseFactory->resolver($cfg)->emitir($comprobante, $xml, $cfg);

        return $this->aplicarResultadoOse($comprobante, $resultado, $xml);
    }

    /** @param  array<string, mixed>  $cfg */
    private function intentarFirmarXml(array $cfg, string $xml, Comprobante $comprobante): string
    {
        $rutaCert = $this->resolverRutaCertificado($cfg['certificado'] ?? '', $comprobante->empresa_id);
        if ($rutaCert === null) {
            return $xml;
        }

        $claveCert = $this->cipher->unseal((string) ($cfg['clave_certificado'] ?? ''));

        try {
            $firmado = $this->signer->firmar($xml, $rutaCert, $claveCert);
            $this->guardarXml($comprobante->empresa_id, $comprobante, $firmado);

            return $firmado;
        } catch (\Throwable $e) {
            $comprobante->update([
                'sunat_mensaje' => 'Advertencia firma XML: '.$e->getMessage(),
            ]);

            return $xml;
        }
    }

    private function aplicarResultadoOse(Comprobante $comprobante, OseResult $resultado, string $xmlLocal): Comprobante
    {
        $datos = [
            'sunat_codigo_respuesta' => $resultado->codigo,
            'sunat_mensaje' => $resultado->mensaje,
            'sunat_enviado_en' => now(),
        ];

        if ($resultado->ok) {
            $datos['sunat_estado'] = 'aceptado';
        } else {
            $datos['sunat_estado'] = in_array($resultado->codigo, ['NET', 'CONFIG', 'PARSE'], true)
                ? 'error'
                : 'rechazado';
        }

        if ($resultado->hash) {
            $datos['sunat_hash'] = substr($resultado->hash, 0, 40);
        }

        if ($resultado->xml) {
            $datos['sunat_xml'] = $this->guardarXml($comprobante->empresa_id, $comprobante, $resultado->xml);
        }

        if ($resultado->cdr) {
            $cdr = base64_decode((string) $resultado->cdr, true);
            if ($cdr !== false && $cdr !== '') {
                $datos['sunat_cdr'] = $this->guardarCdr($comprobante->empresa_id, $comprobante, $cdr);
            }
        }

        $comprobante->update($datos);

        return $comprobante->fresh();
    }

    /** @param  array<string, mixed>  $cfg */
    private function produccionConfigurada(array $cfg): bool
    {
        if (($cfg['proveedor_ose'] ?? 'generico') === 'nubefact') {
            return ($cfg['nubefact_ruta'] ?? '') !== '' && ($cfg['nubefact_token'] ?? '') !== '';
        }

        return ($cfg['usuario_sol'] ?? '') !== ''
            && ($cfg['clave_sol'] ?? '') !== ''
            && ($cfg['ose_url'] ?? '') !== '';
    }

    private function resolverRutaCertificado(string $certificado, int $empresaId): ?string
    {
        if ($certificado === '') {
            return null;
        }

        if (is_readable($certificado)) {
            return $certificado;
        }

        $relativa = storage_path('app/sunat/'.$empresaId.'/'.ltrim($certificado, '/'));
        if (is_readable($relativa)) {
            return $relativa;
        }

        return null;
    }

    private function generarDigestCpe(string $xml): string
    {
        return substr(base64_encode(hash('sha256', $xml, true)), 0, 40);
    }

    private function guardarXml(int $empresaId, Comprobante $comprobante, string $xml): string
    {
        $nombre = sprintf('%s-%08d.xml', $comprobante->serie, $comprobante->numero);
        $dir = "sunat/{$empresaId}";
        $ruta = "{$dir}/{$nombre}";

        Storage::disk('local')->makeDirectory($dir);
        if (! Storage::disk('local')->put($ruta, $xml)) {
            throw new RuntimeException('No se pudo guardar el XML SUNAT en almacenamiento.');
        }

        return $ruta;
    }

    private function guardarCdr(int $empresaId, Comprobante $comprobante, string $cdr): string
    {
        $nombre = sprintf('R-%s-%08d.xml', $comprobante->serie, $comprobante->numero);
        $dir = "sunat/{$empresaId}/cdr";
        $ruta = "{$dir}/{$nombre}";

        Storage::disk('local')->makeDirectory($dir);
        if (! Storage::disk('local')->put($ruta, $cdr)) {
            throw new RuntimeException('No se pudo guardar el CDR SUNAT en almacenamiento.');
        }

        return $ruta;
    }
}
