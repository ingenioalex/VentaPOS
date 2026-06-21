<?php

namespace Tests\Unit;

use App\Services\OseClient;
use App\Services\SunatService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SunatServiceTest extends TestCase
{
    public function test_generar_xml_incluye_datos_del_comprobante(): void
    {
        $service = app(SunatService::class);

        $xml = $service->generarXml([
            'ubl_version' => '2.1',
            'tipo_comprobante' => '03',
            'serie' => 'B001',
            'correlativo' => '00000042',
            'fecha_emision' => '2026-06-08',
            'hora_emision' => '10:30:00',
            'moneda' => 'PEN',
            'emisor' => ['ruc' => '20123456789', 'razon_social' => 'Demo SAC'],
            'receptor' => ['tipo_doc' => '1', 'numero_doc' => '12345678', 'nombre' => 'Cliente'],
            'totales' => ['subtotal' => 10.0, 'igv' => 1.8, 'total' => 11.8],
            'lineas' => [
                ['numero' => 1, 'descripcion' => 'Arroz', 'cantidad' => 1, 'precio_unitario' => 10, 'subtotal' => 10],
            ],
        ]);

        $this->assertStringContainsString('B001-00000042', $xml);
        $this->assertStringContainsString('20123456789', $xml);
        $this->assertStringContainsString('11.80', $xml);
        $this->assertStringContainsString('Arroz', $xml);
    }

    public function test_ose_client_acepta_respuesta_exitosa(): void
    {
        Http::fake([
            'https://ose.test/enviar' => Http::response([
                'aceptado' => true,
                'codigo' => '0',
                'mensaje' => 'Aceptado',
                'cdr_base64' => base64_encode('<cdr>ok</cdr>'),
            ], 200),
        ]);

        $client = new OseClient;
        $resultado = $client->enviar('https://ose.test/enviar', 'user', 'pass', '<xml/>', 'B001-1.xml');

        $this->assertTrue($resultado['ok']);
        $this->assertSame('0', $resultado['codigo']);
        $this->assertSame(base64_encode('<cdr>ok</cdr>'), $resultado['cdr']);
    }
}
