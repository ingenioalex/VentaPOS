<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\SunatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SunatQrMediosPagoTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('mediosPagoProvider')]
    public function test_preparar_emision_genera_qr_para_boleta_con_cualquier_medio(string $medio): void
    {
        $comprobante = $this->crearComprobante('boleta', $medio);

        $resultado = app(SunatService::class)->asegurarHashQr($comprobante);

        $this->assertNotNull($resultado->sunat_hash);
        $this->assertNotNull($resultado->textoQrSunat());
        $this->assertSame('simulado', $resultado->sunat_estado);
    }

    #[DataProvider('mediosPagoProvider')]
    public function test_preparar_emision_genera_qr_para_factura_con_cualquier_medio(string $medio): void
    {
        $comprobante = $this->crearComprobante('factura', $medio, conCliente: true);

        $resultado = app(SunatService::class)->asegurarHashQr($comprobante);

        $this->assertNotNull($resultado->sunat_hash);
        $this->assertStringContainsString('|01|', $resultado->textoQrSunat() ?? '');
        $this->assertSame('simulado', $resultado->sunat_estado);
    }

    public static function mediosPagoProvider(): array
    {
        return [
            ['efectivo'],
            ['tarjeta'],
            ['yape'],
            ['plin'],
            ['mixto'],
        ];
    }

    private function crearComprobante(string $tipo, string $medio, bool $conCliente = false): Comprobante
    {
        Storage::fake('local');

        $empresa = Empresa::create([
            'nombre' => 'Bodega Test',
            'codigo' => 'bodega-test',
            'ruc' => '20123456789',
            'activo' => true,
            'configuracion' => [
                'sunat' => ['activo' => true, 'modo' => 'simulacion'],
            ],
        ]);

        $usuario = Usuario::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Cajero',
            'usuario' => 'cajero',
            'password' => 'secret123',
            'rol' => 'cajero',
            'activo' => true,
        ]);

        $clienteId = null;
        if ($conCliente) {
            $cliente = Cliente::create([
                'empresa_id' => $empresa->id,
                'tipo_documento' => 'ruc',
                'numero_documento' => '20100070970',
                'nombre' => 'Cliente Factura SAC',
                'activo' => true,
            ]);
            $clienteId = $cliente->id;
        }

        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Arroz',
            'sku' => 'ARZ-01',
            'precio_venta' => 10,
            'costo_compra' => 5,
            'stock_disponible' => 100,
            'activo' => true,
        ]);

        $serie = $tipo === 'factura' ? 'F001' : 'B001';
        $comprobante = Comprobante::create([
            'empresa_id' => $empresa->id,
            'usuario_id' => $usuario->id,
            'cliente_id' => $clienteId,
            'numero_interno' => "{$serie}-000001",
            'tipo_documento' => $tipo,
            'serie' => $serie,
            'numero' => 1,
            'subtotal' => 10,
            'valor_igv' => 1.53,
            'descuento' => 0,
            'total' => 10,
            'medio_pago' => $medio,
            'observaciones' => $medio === 'mixto'
                ? json_encode(['pagos' => [
                    ['medio' => 'tarjeta', 'monto' => 6],
                    ['medio' => 'yape', 'monto' => 4],
                ]])
                : null,
            'estado' => 'emitido',
            'emitido_en' => now(),
        ]);

        DetalleComprobante::create([
            'comprobante_id' => $comprobante->id,
            'producto_id' => $producto->id,
            'descripcion_producto' => 'Arroz',
            'cantidad' => 1,
            'precio_unitario' => 10,
            'subtotal' => 10,
        ]);

        return $comprobante->fresh(['detalles.producto', 'cliente', 'empresa']);
    }
}
