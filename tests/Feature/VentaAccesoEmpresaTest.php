<?php

namespace Tests\Feature;

use App\Models\Comprobante;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaAccesoEmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_puede_ver_comprobante_de_otra_empresa_por_id_en_url(): void
    {
        $empresaA = $this->crearEmpresa('empresa-a');
        $empresaB = $this->crearEmpresa('empresa-b');

        $duenoA = $this->crearDueno($empresaA, 'dueno-a');
        $duenoB = $this->crearDueno($empresaB, 'dueno-b');

        $comprobanteB = Comprobante::create([
            'empresa_id' => $empresaB->id,
            'usuario_id' => $duenoB->id,
            'numero_interno' => 'B001-000099',
            'tipo_documento' => 'boleta',
            'serie' => 'B001',
            'numero' => 99,
            'subtotal' => 10,
            'valor_igv' => 1.8,
            'descuento' => 0,
            'total' => 11.8,
            'medio_pago' => 'efectivo',
            'estado' => 'emitido',
            'emitido_en' => now(),
        ]);

        $this->actingAs($duenoA)
            ->withSession(['tenant_empresa_id' => $empresaA->id, 'tenant_empresa_codigo' => $empresaA->codigo])
            ->get(route('ventas.detalle', $comprobanteB))
            ->assertNotFound();
    }

    public function test_puede_ver_comprobante_de_su_propia_empresa(): void
    {
        $empresa = $this->crearEmpresa('mi-bodega');
        $dueno = $this->crearDueno($empresa, 'admin');

        $comprobante = Comprobante::create([
            'empresa_id' => $empresa->id,
            'usuario_id' => $dueno->id,
            'numero_interno' => 'B001-000001',
            'tipo_documento' => 'boleta',
            'serie' => 'B001',
            'numero' => 1,
            'subtotal' => 5,
            'valor_igv' => 0.9,
            'descuento' => 0,
            'total' => 5.9,
            'medio_pago' => 'efectivo',
            'estado' => 'emitido',
            'emitido_en' => now(),
        ]);

        $this->actingAs($dueno)
            ->withSession(['tenant_empresa_id' => $empresa->id, 'tenant_empresa_codigo' => $empresa->codigo])
            ->get(route('ventas.detalle', $comprobante))
            ->assertOk()
            ->assertSee('B001-000001');
    }

    private function crearEmpresa(string $codigo): Empresa
    {
        return Empresa::create([
            'nombre' => 'Empresa '.strtoupper($codigo),
            'codigo' => $codigo,
            'activo' => true,
            'configuracion' => ['onboarding_completado' => true],
        ]);
    }

    private function crearDueno(Empresa $empresa, string $usuario): Usuario
    {
        return Usuario::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Admin '.$usuario,
            'usuario' => $usuario,
            'password' => 'secret123',
            'rol' => 'dueno',
            'activo' => true,
        ]);
    }
}
