<?php

namespace App\Services\Sunat;

use App\Models\Comprobante;
use Illuminate\Support\Facades\Http;

class GenericJsonOseProvider implements OseProviderInterface
{
    /** @param  array<string, mixed>  $cfg */
    public function emitir(Comprobante $comprobante, string $xml, array $cfg): OseResult
    {
        $url = trim((string) ($cfg['ose_url'] ?? ''));
        $usuario = trim((string) ($cfg['usuario_sol'] ?? ''));
        $clave = trim((string) ($cfg['clave_sol'] ?? ''));

        if ($url === '') {
            return new OseResult(false, 'CONFIG', 'Configure la URL del OSE/PSE.');
        }

        if ($usuario === '' || $clave === '') {
            return new OseResult(false, 'CONFIG', 'Faltan credenciales SOL u OSE.');
        }

        $nombre = sprintf('%s-%08d.xml', $comprobante->serie, $comprobante->numero);

        try {
            $response = Http::timeout(45)
                ->acceptJson()
                ->post($url, [
                    'usuario' => $usuario,
                    'clave' => $clave,
                    'nombre_archivo' => $nombre,
                    'archivo_base64' => base64_encode($xml),
                ]);

            if (! $response->successful()) {
                return new OseResult(
                    false,
                    (string) $response->status(),
                    'Error HTTP del OSE/PSE ('.$response->status().').',
                );
            }

            $data = $response->json();
            if (! is_array($data)) {
                return new OseResult(false, 'PARSE', 'Respuesta del OSE/PSE no reconocida.');
            }

            $codigo = (string) ($data['codigo'] ?? $data['codigo_respuesta'] ?? '');
            $aceptado = ($data['aceptado'] ?? $data['success'] ?? false) === true
                || in_array($codigo, ['0', '0000', '0001'], true);

            return new OseResult(
                $aceptado,
                $codigo !== '' ? $codigo : ($aceptado ? '0' : 'ERR'),
                (string) ($data['mensaje'] ?? $data['descripcion'] ?? ($aceptado ? 'Aceptado por SUNAT' : 'Rechazado por SUNAT')),
                $data['cdr_base64'] ?? $data['cdr'] ?? null,
            );
        } catch (\Throwable $e) {
            return new OseResult(false, 'NET', $e->getMessage());
        }
    }

    /** @param  array<string, mixed>  $cfg */
    public function anular(Comprobante $comprobante, array $cfg): OseResult
    {
        $empresa = $comprobante->empresa;
        $tipo = config('sunat.tipos_comprobante.'.$comprobante->tipo_documento, '03');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<VoidedDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1">'
            .'<cbc:ID>RA-'.now()->format('Ymd').'</cbc:ID>'
            .'<cbc:ReferenceDate>'.$comprobante->emitido_en->format('Y-m-d').'</cbc:ReferenceDate>'
            .'<cbc:IssueDate>'.now()->format('Y-m-d').'</cbc:IssueDate>'
            .'<cac:AccountingSupplierParty><cbc:CustomerAssignedAccountID>'.$empresa->ruc.'</cbc:CustomerAssignedAccountID></cac:AccountingSupplierParty>'
            .'<sac:VoidedDocumentsLine>'
            .'<cbc:LineID>1</cbc:LineID>'
            .'<cbc:DocumentTypeCode>'.$tipo.'</cbc:DocumentTypeCode>'
            .'<sac:DocumentSerialID>'.$comprobante->serie.'</sac:DocumentSerialID>'
            .'<sac:DocumentNumberID>'.str_pad((string) $comprobante->numero, 8, '0', STR_PAD_LEFT).'</sac:DocumentNumberID>'
            .'<sac:VoidReasonDescription>'.htmlspecialchars($comprobante->motivo_anulacion ?? 'Anulación', ENT_XML1).'</sac:VoidReasonDescription>'
            .'</sac:VoidedDocumentsLine>'
            .'</VoidedDocuments>';

        $nombre = sprintf('RA-%s-%08d.xml', $comprobante->serie, $comprobante->numero);

        return $this->emitir($comprobante, $xml, $cfg);
    }
}
