<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionKExportarResumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_exportar_whatsapp_solo_incluye_meses_seleccionados_y_ordena_por_mes(): void
    {
        $admin = $this->admin();
        [$octubre, $noviembre] = $this->cuotasYPromociones();

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.resumen.cuotas.exportar', [
            'formato' => 'whatsapp',
            'orden' => 'mes',
            'meses' => [$octubre->id],
        ]));

        $respuesta->assertOk();
        $respuesta->assertSee('CUOTAS POR MES');
        $respuesta->assertSee('OCT26');
        $respuesta->assertSee('Juan García Pérez');
        $respuesta->assertDontSee('NOV26');
        $respuesta->assertDontSee('POR PROMOCIÓN');
    }

    public function test_exportar_whatsapp_puede_ordenarse_por_promocion(): void
    {
        $admin = $this->admin();
        [$octubre, $noviembre] = $this->cuotasYPromociones();

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.resumen.cuotas.exportar', [
            'formato' => 'whatsapp',
            'orden' => 'promocion',
            'meses' => [$octubre->id, $noviembre->id],
        ]));

        $respuesta->assertOk();
        $respuesta->assertSee('CUOTAS POR PROMOCIÓN');
        $respuesta->assertSee('OCT26');
        $respuesta->assertSee('NOV26');
        $respuesta->assertSee('Juan García Pérez');
        $respuesta->assertSee('Ana López Díaz');
    }

    public function test_exportar_excel_por_mes_omite_meses_no_marcados(): void
    {
        $admin = $this->admin();
        [$octubre, $noviembre] = $this->cuotasYPromociones();

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.resumen.cuotas.exportar', [
            'formato' => 'excel',
            'orden' => 'mes',
            'meses' => [$octubre->id],
        ]));

        $respuesta->assertOk();
        $contenido = $respuesta->getContent();
        $this->assertStringContainsString('OCT26', $contenido);
        $this->assertStringNotContainsString('NOV26', $contenido);
        $this->assertStringContainsString('Juan García Pérez', $contenido);
    }

    public function test_exportar_sin_meses_redirige_con_error(): void
    {
        $admin = $this->admin();
        $this->cuotasYPromociones();

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.resumen.cuotas.exportar', [
            'formato' => 'whatsapp',
            'orden' => 'mes',
        ]));

        $respuesta->assertRedirect(route('seccionk.admin.resumen.cuotas'));
        $respuesta->assertSessionHas('error', 'Selecciona al menos un mes para exportar.');
    }

    private function admin(): SeccionKUsuario
    {
        return SeccionKUsuario::create([
            'usuario' => 'admin',
            'password' => 'seccionk2026',
            'nombre' => 'Administrador',
            'activo' => true,
        ]);
    }

    private function cuotasYPromociones(): array
    {
        $octubre = SeccionKEvento::create([
            'nombre' => 'Cuota Octubre 2026',
            'fecha' => '2026-10-01',
            'monto' => 50,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Octubre 2026',
            'activo' => true,
        ]);
        $noviembre = SeccionKEvento::create([
            'nombre' => 'Cuota Noviembre 2026',
            'fecha' => '2026-11-01',
            'monto' => 50,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Noviembre 2026',
            'activo' => true,
        ]);

        $juan = SeccionKPromocion::create([
            'dni' => '12345678',
            'nombre' => 'Juan',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'Pérez',
            'participa_cuotas' => true,
            'activo' => true,
        ]);
        $ana = SeccionKPromocion::create([
            'dni' => '87654321',
            'nombre' => 'Ana',
            'apellido_paterno' => 'López',
            'apellido_materno' => 'Díaz',
            'participa_cuotas' => true,
            'activo' => true,
        ]);

        SeccionKParticipacion::create([
            'promocion_id' => $juan->id,
            'evento_id' => $octubre->id,
            'participo' => true,
            'pago' => true,
            'monto_aportado' => 50,
            'fecha_pago' => '2026-10-05',
        ]);

        SeccionKParticipacion::create([
            'promocion_id' => $ana->id,
            'evento_id' => $noviembre->id,
            'participo' => true,
            'pago' => true,
            'monto_aportado' => 50,
            'fecha_pago' => '2026-11-05',
        ]);

        return [$octubre, $noviembre];
    }
}
