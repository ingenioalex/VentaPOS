<?php

namespace Tests\Unit;

use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\Sunat\NubefactOseProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NubefactOseProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_nubefact_acepta_comprobante_exitoso(): void
    {
        Http::fake([
            'https://api.nubefact.com/api/v1/*' => Http::response([
                'errors' => '',
                'codigo' => 0,
                'sunat_description' => 'ACEPTADO',
                'cadena_para_codigo_qr' => '20123456789|03|B001|00000001|1.80|11.80|08-06-2026|1|12345678|abc123hash|',
                'codigo_hash' => 'abc123hash',
            ], 200),
        ]);

        $comprobante = $this->comprobanteDemo();

        $resultado = (new NubefactOseProvider)->emitir($comprobante, '<xml/>', [
            'nubefact_ruta' => 'mi-ruta-test',
            'nubefact_token' => 'token-test',
        ]);

        $this->assertTrue($resultado->ok);
        $this->assertSame('abc123hash', $resultado->hash);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.nubefact.com/api/v1/mi-ruta-test'
                && ($body['operacion'] ?? '') === 'generar_comprobante'
                && ($body['tipo_de_comprobante'] ?? null) === 2
                && ($body['serie'] ?? '') === 'B001';
        });
    }

    private function comprobanteDemo(): Comprobante
    {
        $empresa = Empresa::create([
            'nombre' => 'Demo SAC',
            'codigo' => 'demo-sac',
            'ruc' => '20123456789',
            'activo' => true,
        ]);

        $usuario = Usuario::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Cajero',
            'usuario' => 'cajero',
            'password' => 'secret123',
            'rol' => 'cajero',
            'activo' => true,
        ]);

        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Arroz',
            'sku' => 'ARZ',
            'precio_venta' => 10,
            'costo_compra' => 5,
            'stock_disponible' => 50,
            'afecto_igv' => true,
            'activo' => true,
        ]);

        $comprobante = Comprobante::create([
            'empresa_id' => $empresa->id,
            'usuario_id' => $usuario->id,
            'numero_interno' => 'B001-000001',
            'tipo_documento' => 'boleta',
            'serie' => 'B001',
            'numero' => 1,
            'subtotal' => 11.8,
            'valor_igv' => 1.8,
            'total' => 11.8,
            'medio_pago' => 'efectivo',
            'estado' => 'emitido',
            'emitido_en' => now(),
        ]);

        DetalleComprobante::create([
            'comprobante_id' => $comprobante->id,
            'producto_id' => $producto->id,
            'descripcion_producto' => 'Arroz',
            'cantidad' => 1,
            'precio_unitario' => 10,
            'subtotal' => 11.8,
        ]);

        return $comprobante->fresh(['detalles.producto', 'empresa']);
    }
}
