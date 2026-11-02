<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionKPromocionesExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_tiene_el_boton_whatsapp_nombres(): void
    {
        $this->datos();

        $this->actingAs($this->admin(), 'seccionk')
            ->get(route('seccionk.admin.promociones.index'))
            ->assertOk()
            ->assertSee('WhatsApp nombres')
            ->assertSee('formato=nombres', false);
    }

    public function test_whatsapp_nombres_detalla_meses_y_eventos_sin_dni_ni_celular(): void
    {
        [$promo, $evento] = $this->datos();
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.promociones.exportar', ['formato' => 'nombres']));

        $respuesta->assertOk();
        $respuesta->assertSee('Promociones para WhatsApp (solo nombres)');
        $texto = $respuesta->viewData('texto');

        $this->assertStringContainsString('👤 *'.$promo->nombreCompleto().'*', $texto);
        $this->assertStringContainsString('Cuotas: 2/2 pagadas · 0 por pagar', $texto);
        $this->assertStringContainsString('Mes pagado: OCT, NOV', $texto);
        $this->assertStringContainsString('Eventos: 1/1 pagados · 0 por pagar', $texto);
        $this->assertStringContainsString('evento: '.$evento->nombre.' OCT', $texto);
        $this->assertStringNotContainsString($promo->dni, $texto);
        $this->assertStringNotContainsString('DNI:', $texto);
        $this->assertStringNotContainsString($promo->celular, $texto);
        $this->assertStringNotContainsString('Cel:', $texto);
    }

    public function test_el_whatsapp_completo_sigue_incluyendo_dni(): void
    {
        [$promo] = $this->datos();

        $texto = $this->actingAs($this->admin(), 'seccionk')
            ->get(route('seccionk.admin.promociones.exportar', ['formato' => 'whatsapp']))
            ->assertOk()
            ->viewData('texto');

        $this->assertStringContainsString('DNI: '.$promo->dni, $texto);
        $this->assertStringContainsString('Cel: '.$promo->celular, $texto);
        $this->assertStringNotContainsString('Mes pagado:', $texto);
    }

    private function datos(): array
    {
        $promo = SeccionKPromocion::create([
            'dni' => '45399354',
            'nombre' => 'Hilber Omar',
            'apellido_paterno' => 'Avila',
            'apellido_materno' => 'Valle',
            'celular' => '906731699',
            'participa_cuotas' => true,
            'activo' => true,
        ]);
        $octubre = SeccionKEvento::create([
            'nombre' => 'Cuota Octubre 2026',
            'fecha' => '2026-10-01',
            'monto' => 10,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Octubre 2026',
            'activo' => true,
        ]);
        $noviembre = SeccionKEvento::create([
            'nombre' => 'Cuota Noviembre 2026',
            'fecha' => '2026-11-01',
            'monto' => 10,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Noviembre 2026',
            'activo' => true,
        ]);
        $evento = SeccionKEvento::create([
            'nombre' => 'Partido de Fútbol',
            'fecha' => '2026-10-21',
            'monto' => 5,
            'tipo' => 'evento',
            'activo' => true,
        ]);
        foreach ([$octubre, $noviembre, $evento] as $item) {
            SeccionKParticipacion::create([
                'promocion_id' => $promo->id,
                'evento_id' => $item->id,
                'pago' => true,
                'participo' => true,
                'monto_aportado' => $item->monto,
            ]);
        }

        return [$promo, $evento];
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
}
