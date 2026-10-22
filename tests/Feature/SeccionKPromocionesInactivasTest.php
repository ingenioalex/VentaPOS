<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionKPromocionesInactivasTest extends TestCase
{
    use RefreshDatabase;

    public function test_mostrar_inactivos_lista_las_ocultas(): void
    {
        $admin = $this->admin();
        $activa = $this->promocion('11111111', 'Ana', true);
        $oculta = $this->promocion('22222222', 'Beto', false);

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.promociones.index'))
            ->assertOk()
            ->assertSee('Ana')
            ->assertDontSee('Beto');

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.promociones.index', ['mostrar_inactivos' => 1]))
            ->assertOk()
            ->assertSee('Ana')
            ->assertSee('Beto')
            ->assertSee('1 ocultas');
    }

    public function test_eliminar_borra_la_promocion_y_sus_pagos(): void
    {
        $admin = $this->admin();
        $promo = $this->promocion('33333333', 'Carla', true);
        $cuota = SeccionKEvento::create([
            'nombre' => 'Cuota Octubre 2026',
            'fecha' => '2026-10-01',
            'monto' => 10,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Octubre 2026',
            'activo' => true,
        ]);
        SeccionKParticipacion::create([
            'promocion_id' => $promo->id,
            'evento_id' => $cuota->id,
            'pago' => true,
            'participo' => true,
            'monto_aportado' => 10,
        ]);

        $this->actingAs($admin, 'seccionk')
            ->delete(route('seccionk.admin.promociones.eliminar', $promo))
            ->assertRedirect();

        $this->assertDatabaseMissing('seccionk_promociones', ['id' => $promo->id]);
        $this->assertDatabaseMissing('seccionk_participaciones', ['promocion_id' => $promo->id]);
    }

    public function test_reportes_no_cuentan_pagos_de_promociones_ocultas(): void
    {
        $admin = $this->admin();
        $activa = $this->promocion('44444444', 'Diana', true);
        $oculta = $this->promocion('55555555', 'Elías', false);
        $cuota = SeccionKEvento::create([
            'nombre' => 'Cuota Octubre 2026',
            'fecha' => '2026-10-01',
            'monto' => 10,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Octubre 2026',
            'activo' => true,
        ]);
        foreach ([$activa, $oculta] as $promo) {
            SeccionKParticipacion::create([
                'promocion_id' => $promo->id,
                'evento_id' => $cuota->id,
                'pago' => true,
                'participo' => true,
                'monto_aportado' => 10,
            ]);
        }

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.dashboard'))
            ->assertOk()
            ->assertSee('S/ 10.00')
            ->assertDontSee('Elías');

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.resumen.cuotas'))
            ->assertOk()
            ->assertSee('Diana')
            ->assertDontSee('Elías');
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

    private function promocion(string $dni, string $nombre, bool $activo): SeccionKPromocion
    {
        return SeccionKPromocion::create([
            'dni' => $dni,
            'nombre' => $nombre,
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Lazo',
            'participa_cuotas' => true,
            'activo' => $activo,
        ]);
    }
}
