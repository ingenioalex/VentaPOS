<?php

namespace Tests\Feature;

use App\Models\SeccionKDeclaracion;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionKEstadisticasEventosTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_inicio_publico_no_muestra_montos_hasta_identificarse(): void
    {
        $this->datosDemo();

        $respuesta = $this->get(route('seccionk.index'));

        $respuesta->assertOk();
        $respuesta->assertSee('Identifícate con tu DNI');
        $respuesta->assertDontSee('Promociones participando');
        $respuesta->assertDontSee('Monto cuotas');
        $respuesta->assertDontSee('S/ 90.00');
    }

    public function test_el_perfil_pide_dni_si_no_esta_identificado(): void
    {
        $this->datosDemo();
        $yuri = SeccionKPromocion::where('dni', '42867691')->firstOrFail();

        $this->get(route('seccionk.perfil', $yuri))
            ->assertRedirect(route('seccionk.index'));
    }

    public function test_al_identificarse_con_dni_ve_el_resumen_de_la_seccion(): void
    {
        $this->datosDemo();
        $yuri = SeccionKPromocion::where('dni', '42867691')->firstOrFail();

        $this->post(route('seccionk.buscar'), [
            'dni' => '42867691',
            'apellido' => 'Vargas',
        ])->assertRedirect(route('seccionk.perfil', $yuri));

        $respuesta = $this->get(route('seccionk.perfil', $yuri));

        $respuesta->assertOk();
        $respuesta->assertSee('Resumen de la Sección K');
        $respuesta->assertSee('Promociones participando');
        $respuesta->assertSee('Promociones aportando');
        $respuesta->assertSee('Monto cuotas');
        $respuesta->assertSee('S/ 10.00');
        $respuesta->assertSee('Monto eventos');
        $respuesta->assertSee('S/ 5.00');
        $respuesta->assertSee('Tu participación');
    }

    public function test_el_dashboard_muestra_el_evento_con_monto_y_participantes(): void
    {
        $evento = $this->datosDemo();
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.dashboard'));

        $respuesta->assertOk();
        $respuesta->assertSee('Eventos · recaudado − declarado');
        $respuesta->assertSee('S/ 5.00');
        $respuesta->assertSee('Partido de Fútbol');
        $respuesta->assertSee('1/2');
        $respuesta->assertSee('Ver detalle');
        $respuesta->assertSee('Excel');
        $respuesta->assertSee('WhatsApp');
        $respuesta->assertSee('Ordenar por fecha');
        $respuesta->assertSee('Declaración de fondos');
        $respuesta->assertSee(route('seccionk.admin.eventos.detalle', $evento), false);
        $respuesta->assertSee(route('seccionk.admin.declaracion'), false);
        $respuesta->assertSee(route('seccionk.admin.dashboard.exportar'), false);
    }

    public function test_el_dashboard_resta_la_declaracion_de_los_eventos(): void
    {
        $evento = $this->datosDemo();
        SeccionKDeclaracion::registrarCierre($evento, 4);
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.dashboard'));

        $respuesta->assertOk();
        $respuesta->assertSee('Recaudado S/ 5.00');
        $respuesta->assertSee('declarado S/ 4.00');
        $respuesta->assertSee('S/ 1.00');
    }

    public function test_exportar_dashboard_excel_ordena_eventos_por_fecha(): void
    {
        $primero = $this->datosDemo();
        $segundo = SeccionKEvento::create([
            'nombre' => 'Asado de confraternidad',
            'fecha' => '2026-11-02',
            'monto' => 20,
            'tipo' => 'evento',
            'activo' => true,
        ]);
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.dashboard.exportar', ['formato' => 'excel', 'orden' => 'fecha']));

        $respuesta->assertOk();
        $contenido = $respuesta->getContent();
        $this->assertStringContainsString('Partido de Fútbol', $contenido);
        $this->assertStringContainsString('Asado de confraternidad', $contenido);
        $this->assertTrue(strpos($contenido, 'Partido de Fútbol') < strpos($contenido, 'Asado de confraternidad'));
        $this->assertStringContainsString('Recaudado S/', $contenido);
        $this->assertStringContainsString('Declarado S/', $contenido);
        $this->assertStringContainsString('Saldo S/', $contenido);
        $this->assertSame($primero->nombre, 'Partido de Fútbol');
        $this->assertSame($segundo->tipo, 'evento');
    }

    public function test_exportar_dashboard_whatsapp_ordena_por_nombre(): void
    {
        $this->datosDemo();
        SeccionKEvento::create([
            'nombre' => 'Asado de confraternidad',
            'fecha' => '2026-11-02',
            'monto' => 20,
            'tipo' => 'evento',
            'activo' => true,
        ]);
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.dashboard.exportar', ['formato' => 'whatsapp', 'orden' => 'nombre']));

        $respuesta->assertOk();
        $respuesta->assertSee('Dashboard Sección K para WhatsApp');
        $texto = $respuesta->viewData('texto');
        $this->assertStringContainsString('ordenados por nombre', $texto);
        $this->assertTrue(strpos($texto, 'Asado de confraternidad') < strpos($texto, 'Partido de Fútbol'));
        $this->assertStringContainsString('recaudado − declaración', $texto);
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
        $respuesta->assertSee('Cerrar evento y declarar el dinero usado');
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

        $respuesta = $this->withSession(['seccionk_identificado' => $yuri->id])
            ->get(route('seccionk.perfil', $yuri));

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
