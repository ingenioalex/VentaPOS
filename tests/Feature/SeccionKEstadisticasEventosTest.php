<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionKEstadisticasEventosTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_inicio_publico_muestra_promociones_y_montos_separados(): void
    {
        $this->datosDemo();

        $respuesta = $this->get(route('seccionk.index'));

        $respuesta->assertOk();
        $respuesta->assertSee('Promociones participando');
        $respuesta->assertSee('2/2');
        $respuesta->assertSee('Promociones aportando');
        $respuesta->assertSee('Monto cuotas');
        $respuesta->assertSee('S/ 10.00');
        $respuesta->assertSee('Monto eventos');
        $respuesta->assertSee('S/ 5.00');
    }

    public function test_el_dashboard_muestra_el_evento_con_monto_y_participantes(): void
    {
        $evento = $this->datosDemo();
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.dashboard'));

        $respuesta->assertOk();
        $respuesta->assertSee('Monto recaudado (eventos)');
        $respuesta->assertSee('S/ 5.00');
        $respuesta->assertSee('Partido de Fútbol');
        $respuesta->assertSee('1/2');
        $respuesta->assertSee('Ver detalle');
        $respuesta->assertSee(route('seccionk.admin.eventos.detalle', $evento), false);
    }

    public function test_el_detalle_del_evento_lista_aportes_y_el_total(): void
    {
        $evento = $this->datosDemo();
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.eventos.detalle', $evento));

        $respuesta->assertOk();
        $respuesta->assertSee('Yuri Vargas Lazo');
        $respuesta->assertSee('Ana López Díaz');
        $respuesta->assertSee('S/ 5.00');
        $respuesta->assertSee('Total del evento');
        $respuesta->assertSee('Monto total del evento');
        $respuesta->assertDontSee('Elías Oculto Pérez');
    }

    public function test_el_dashboard_no_borra_cuotas_duplicadas(): void
    {
        $this->cuota('Octubre 2026', '2026-10-01', 10);
        $this->cuota('Octubre 2026', '2026-10-15', 10);
        $admin = $this->admin();

        $this->assertSame(2, SeccionKEvento::cuotasMensuales()->count());

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.dashboard'))
            ->assertOk();

        $this->assertSame(2, SeccionKEvento::cuotasMensuales()->count());
    }

    public function test_el_perfil_separa_aporte_de_cuotas_y_eventos(): void
    {
        $this->datosDemo();
        $yuri = SeccionKPromocion::where('dni', '42867691')->firstOrFail();

        $respuesta = $this->get(route('seccionk.perfil', $yuri));

        $respuesta->assertOk();
        $respuesta->assertSee('Aporte en cuotas');
        $respuesta->assertSee('Aporte en eventos');
        $respuesta->assertSee('S/ 10.00');
        $respuesta->assertSee('S/ 5.00');
    }

    private function datosDemo(): SeccionKEvento
    {
        $yuri = $this->promocion('42867691', 'Yuri', 'Vargas', 'Lazo', true);
        $ana = $this->promocion('87654321', 'Ana', 'López', 'Díaz', true);
        $this->promocion('55555555', 'Elías', 'Oculto', 'Pérez', false);

        $cuota = $this->cuota('Octubre 2026', '2026-10-30', 10);
        $evento = SeccionKEvento::create([
            'nombre' => 'Partido de Fútbol',
            'fecha' => '2026-10-21',
            'monto' => 5,
            'tipo' => 'evento',
            'activo' => true,
        ]);

        SeccionKParticipacion::create([
            'promocion_id' => $yuri->id,
            'evento_id' => $cuota->id,
            'pago' => true,
            'participo' => true,
            'monto_aportado' => 10,
        ]);
        SeccionKParticipacion::create([
            'promocion_id' => $yuri->id,
            'evento_id' => $evento->id,
            'pago' => true,
            'participo' => true,
            'monto_aportado' => 5,
        ]);
        SeccionKParticipacion::create([
            'promocion_id' => $ana->id,
            'evento_id' => $evento->id,
            'pago' => false,
            'participo' => false,
            'monto_aportado' => 0,
        ]);

        return $evento;
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

    private function promocion(string $dni, string $nombre, string $paterno, string $materno, bool $activo): SeccionKPromocion
    {
        return SeccionKPromocion::create([
            'dni' => $dni,
            'nombre' => $nombre,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'participa_cuotas' => true,
            'activo' => $activo,
        ]);
    }

    private function cuota(string $mes, string $fecha, float $monto): SeccionKEvento
    {
        return SeccionKEvento::create([
            'nombre' => 'Cuota '.$mes,
            'fecha' => $fecha,
            'monto' => $monto,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => $mes,
            'activo' => true,
        ]);
    }
}
