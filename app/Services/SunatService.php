<?php



namespace App\Services;



use App\Models\Comprobante;

use App\Models\Empresa;

use Illuminate\Support\Facades\Storage;

use RuntimeException;



class SunatService

{

    public function __construct(private OseClient $ose) {}



    public function estaActivo(Empresa $empresa): bool

    {

        $cfg = $this->config($empresa);



        return (bool) ($cfg['activo'] ?? false);

    }



    /** @return array<string, mixed> */

    public function config(Empresa $empresa): array

    {

        $cfg = $empresa->configuracion['sunat'] ?? [];



        return array_merge([

            'activo' => false,

            'modo' => 'simulacion',

            'usuario_sol' => '',

            'clave_sol' => '',

            'certificado' => '',

            'ose_url' => '',

        ], is_array($cfg) ? $cfg : []);

    }



    /** @param  array<string, mixed>  $datos */

    public function guardarConfig(Empresa $empresa, array $datos): void

    {

        $config = $empresa->configuracion ?? [];

        $prev = $this->config($empresa);



        $config['sunat'] = [

            'activo' => (bool) ($datos['activo'] ?? false),

            'modo' => in_array($datos['modo'] ?? '', ['simulacion', 'produccion'], true) ? $datos['modo'] : 'simulacion',

            'usuario_sol' => trim($datos['usuario_sol'] ?? ''),

            'clave_sol' => $datos['clave_sol'] !== '' ? $datos['clave_sol'] : ($prev['clave_sol'] ?? ''),

            'certificado' => trim($datos['certificado'] ?? ''),

            'ose_url' => trim($datos['ose_url'] ?? ''),

        ];



        $empresa->update(['configuracion' => $config]);

    }



    public function emitir(Comprobante $comprobante): Comprobante

    {

        $comprobante->load(['detalles.producto', 'cliente', 'empresa', 'usuario']);



        if ($comprobante->estado !== 'emitido') {

            throw new RuntimeException('Solo se envían a SUNAT comprobantes emitidos.');

        }



        if (! in_array($comprobante->tipo_documento, ['boleta', 'factura'], true)) {

            $comprobante->update(['sunat_estado' => 'omitido']);



            return $comprobante;

        }



        $empresa = $comprobante->empresa;

        if (! $empresa || ! $this->estaActivo($empresa)) {

            $comprobante->update(['sunat_estado' => 'omitido']);



            return $comprobante;

        }



        if (empty($empresa->ruc) || strlen((string) $empresa->ruc) !== 11) {

            throw new RuntimeException('Configure el RUC de la empresa (11 dígitos) antes de emitir electrónicamente.');

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

        ]);



        if ($cfg['modo'] === 'simulacion') {

            $comprobante->update([

                'sunat_estado' => 'simulado',

                'sunat_codigo_respuesta' => '0',

                'sunat_mensaje' => 'Aceptado en modo simulación (sin envío a SUNAT).',

                'sunat_enviado_en' => now(),

            ]);



            return $comprobante->fresh();

        }



        return $this->enviarProduccion($comprobante, $cfg, $xml);

    }



    public function reenviar(Comprobante $comprobante): Comprobante

    {

        if (! in_array($comprobante->sunat_estado, ['rechazado', 'error', 'pendiente'], true)) {

            throw new RuntimeException('Este comprobante no requiere reenvío.');

        }



        return $this->emitir($comprobante);

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



        if (empty($cfg['ose_url'])) {

            $comprobante->update([

                'sunat_mensaje' => trim(($comprobante->sunat_mensaje ?? '').' | Anulación pendiente: configure OSE/PSE.'),

            ]);



            return;

        }



        $nombre = sprintf('RA-%s-%08d.xml', $comprobante->serie, $comprobante->numero);

        $xmlAnulacion = $this->generarXmlAnulacion($comprobante);

        $resultado = $this->ose->enviar(

            $cfg['ose_url'],

            $cfg['usuario_sol'],

            $cfg['clave_sol'],

            $xmlAnulacion,

            $nombre,

        );



        $comprobante->update([

            'sunat_mensaje' => trim(($comprobante->sunat_mensaje ?? '').' | Anulación: '.$resultado['mensaje']),

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

            ];

        }



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

                'subtotal' => (float) $comprobante->subtotal,

                'igv' => (float) $comprobante->valor_igv,

                'total' => (float) $comprobante->total,

            ],

            'lineas' => $lineas,

        ];

    }



    /** @param  array<string, mixed>  $payload */

    public function generarXml(array $payload): string

    {

        $lineasXml = '';

        foreach ($payload['lineas'] as $l) {

            $lineasXml .= sprintf(

                '<cac:InvoiceLine><cbc:ID>%d</cbc:ID><cbc:InvoicedQuantity>%s</cbc:InvoicedQuantity><cbc:LineExtensionAmount>%s</cbc:LineExtensionAmount><cac:Item><cbc:Description>%s</cbc:Description></cac:Item><cac:Price><cbc:PriceAmount>%s</cbc:PriceAmount></cac:Price></cac:InvoiceLine>',

                $l['numero'],

                number_format($l['cantidad'], 2, '.', ''),

                number_format($l['subtotal'], 2, '.', ''),

                htmlspecialchars($l['descripcion'], ENT_XML1),

                number_format($l['precio_unitario'], 2, '.', ''),

            );

        }



        return '<?xml version="1.0" encoding="UTF-8"?>'

            .'<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">'

            .'<cbc:UBLVersionID>'.$payload['ubl_version'].'</cbc:UBLVersionID>'

            .'<cbc:ID>'.$payload['serie'].'-'.$payload['correlativo'].'</cbc:ID>'

            .'<cbc:IssueDate>'.$payload['fecha_emision'].'</cbc:IssueDate>'

            .'<cbc:IssueTime>'.$payload['hora_emision'].'</cbc:IssueTime>'

            .'<cbc:InvoiceTypeCode>'.$payload['tipo_comprobante'].'</cbc:InvoiceTypeCode>'

            .'<cbc:DocumentCurrencyCode>'.$payload['moneda'].'</cbc:DocumentCurrencyCode>'

            .'<cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="6">'.$payload['emisor']['ruc'].'</cbc:ID></cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName>'.htmlspecialchars($payload['emisor']['razon_social'], ENT_XML1).'</cbc:RegistrationName></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty>'

            .'<cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="'.$payload['receptor']['tipo_doc'].'">'.$payload['receptor']['numero_doc'].'</cbc:ID></cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName>'.htmlspecialchars($payload['receptor']['nombre'], ENT_XML1).'</cbc:RegistrationName></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty>'

            .'<cac:TaxTotal><cbc:TaxAmount>'.number_format($payload['totales']['igv'], 2, '.', '').'</cbc:TaxAmount></cac:TaxTotal>'

            .'<cac:LegalMonetaryTotal><cbc:PayableAmount>'.number_format($payload['totales']['total'], 2, '.', '').'</cbc:PayableAmount></cac:LegalMonetaryTotal>'

            .$lineasXml

            .'</Invoice>';

    }



    private function generarXmlAnulacion(Comprobante $comprobante): string

    {

        $empresa = $comprobante->empresa;

        $tipo = config('sunat.tipos_comprobante.'.$comprobante->tipo_documento, '03');



        return '<?xml version="1.0" encoding="UTF-8"?>'

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

    }



    private function generarDigestCpe(string $xml): string

    {

        return substr(base64_encode(hash('sha256', $xml, true)), 0, 40);

    }



    private function guardarXml(int $empresaId, Comprobante $comprobante, string $xml): string

    {

        $nombre = sprintf('%s-%08d.xml', $comprobante->serie, $comprobante->numero);

        $ruta = "sunat/{$empresaId}/{$nombre}";

        Storage::disk('local')->put($ruta, $xml);



        return $ruta;

    }



    private function guardarCdr(int $empresaId, Comprobante $comprobante, string $cdr): string

    {

        $nombre = sprintf('R-%s-%08d.xml', $comprobante->serie, $comprobante->numero);

        $ruta = "sunat/{$empresaId}/cdr/{$nombre}";

        Storage::disk('local')->put($ruta, $cdr);



        return $ruta;

    }



    /** @param  array<string, mixed>  $cfg */

    private function enviarProduccion(Comprobante $comprobante, array $cfg, string $xml): Comprobante

    {

        if (empty($cfg['usuario_sol']) || empty($cfg['clave_sol'])) {

            $comprobante->update([

                'sunat_estado' => 'error',

                'sunat_mensaje' => 'Faltan credenciales SOL u OSE en configuración SUNAT.',

            ]);



            throw new RuntimeException('Configure usuario y clave SOL para modo producción.');

        }



        if (empty($cfg['ose_url'])) {

            $comprobante->update([

                'sunat_estado' => 'error',

                'sunat_codigo_respuesta' => 'CONFIG',

                'sunat_mensaje' => 'Modo producción: configure la URL del OSE/PSE. XML generado localmente.',

                'sunat_enviado_en' => now(),

            ]);



            return $comprobante->fresh();

        }



        $nombre = sprintf('%s-%08d.xml', $comprobante->serie, $comprobante->numero);

        $resultado = $this->ose->enviar(

            $cfg['ose_url'],

            $cfg['usuario_sol'],

            $cfg['clave_sol'],

            $xml,

            $nombre,

        );



        $datos = [

            'sunat_codigo_respuesta' => $resultado['codigo'],

            'sunat_mensaje' => $resultado['mensaje'],

            'sunat_enviado_en' => now(),

        ];



        if ($resultado['ok']) {

            $datos['sunat_estado'] = 'aceptado';

        } else {

            $datos['sunat_estado'] = in_array($resultado['codigo'], ['NET', 'CONFIG', 'PARSE'], true)

                ? 'error'

                : 'rechazado';

        }



        if (! empty($resultado['cdr'])) {

            $cdr = base64_decode((string) $resultado['cdr'], true);

            if ($cdr !== false && $cdr !== '') {

                $datos['sunat_cdr'] = $this->guardarCdr($comprobante->empresa_id, $comprobante, $cdr);

            }

        }



        $comprobante->update($datos);



        return $comprobante->fresh();

    }

}


