<?php

namespace Tests\Feature;

use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\SunatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SunatEmitirTest extends TestCase
{
    use RefreshDatabase;

    public function test_emitir_en_simulacion_marca_comprobante_y_guarda_xml(): void
    {
        Storage::fake('local');

        $empresa = Empresa::create([
            'nombre' => 'Bodega Test',
            'codigo' => 'bodega-test',
            'ruc' => '20123456789',
            'activo' => true,
            'configuracion' => [
                'sunat' => [
                    'activo' => true,
                    'modo' => 'simulacion',
                ],
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

        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Arroz',
            'sku' => 'ARZ-01',
            'precio_venta' => 10,
            'costo_compra' => 5,
            'stock_disponible' => 100,
            'activo' => true,
        ]);

        $comprobante = Comprobante::create([
            'empresa_id' => $empresa->id,
            'usuario_id' => $usuario->id,
            'numero_interno' => 'B001-000001',
            'tipo_documento' => 'boleta',
            'serie' => 'B001',
            'numero' => 1,
            'subtotal' => 10,
            'valor_igv' => 1.8,
            'descuento' => 0,
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
            'subtotal' => 10,
        ]);

        $resultado = app(SunatService::class)->emitir($comprobante->fresh());

        $this->assertSame('simulado', $resultado->sunat_estado);
        $this->assertNotNull($resultado->sunat_xml);
        $this->assertNotNull($resultado->sunat_hash);
        $this->assertNotNull($resultado->textoQrSunat());
        Storage::disk('local')->assertExists($resultado->sunat_xml);
    }
}
