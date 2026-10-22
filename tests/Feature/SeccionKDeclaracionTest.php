<?php

namespace Tests\Feature;

use App\Models\SeccionKDeclaracion;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use App\Support\SeccionKEsquema;
use App\Support\SeccionKEstadisticas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeccionKDeclaracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cerrar_evento_guarda_el_uso_sin_cambiar_aportes(): void
    {
        $evento = $this->datosDemo();
        $admin = $this->admin();
        $aportesAntes = SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'pago', 'monto_aportado'])->toArray();
        $eventoAntes = $evento->only(['id', 'nombre', 'monto', 'tipo', 'activo']);

        $respuesta = $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.eventos.cerrar', $evento), [
                'monto_utilizado' => 4.50,
                'observaciones' => 'Alquiler de cancha',
            ]);

        $respuesta->assertRedirect(route('seccionk.admin.eventos.detalle', $evento));

        $this->assertDatabaseHas('seccionk_declaraciones', [
            'evento_id' => $evento->id,
            'cerrado' => 1,
            'monto_utilizado' => 4.50,
            'observaciones' => 'Alquiler de cancha',
        ]);

        $this->assertSame(
            $aportesAntes,
            SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'pago', 'monto_aportado'])->toArray()
        );
        $this->assertSame($eventoAntes, $evento->fresh()->only(['id', 'nombre', 'monto', 'tipo', 'activo']));
        $this->assertSame(2, SeccionKEvento::count());
        $this->assertFalse(Schema::hasColumn('seccionk_eventos', 'monto_utilizado'));
        $this->assertFalse(Schema::hasColumn('seccionk_eventos', 'cerrado'));
    }

    public function test_actualizar_el_cierre_no_duplica_la_declaracion(): void
    {
        $evento = $this->datosDemo();
        $admin = $this->admin();

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.eventos.cerrar', $evento), [
                'monto_utilizado' => 5,
            ])
            ->assertRedirect();

        $this->actingAs($admin, 'seccionk')
            ->from(route('seccionk.admin.declaracion'))
            ->post(route('seccionk.admin.eventos.cerrar', $evento), [
                'monto_utilizado' => 3.25,
                'observaciones' => 'Ajuste de gasto',
                'volver' => 'declaracion',
            ])
            ->assertRedirect(route('seccionk.admin.declaracion'));

        $this->assertSame(1, SeccionKDeclaracion::where('evento_id', $evento->id)->count());
        $this->assertDatabaseHas('seccionk_declaraciones', [
            'evento_id' => $evento->id,
            'monto_utilizado' => 3.25,
            'observaciones' => 'Ajuste de gasto',
        ]);
    }

    public function test_el_modulo_de_declaracion_muestra_cuotas_y_eventos(): void
    {
        $evento = $this->datosDemo();
        $admin = $this->admin();

        SeccionKDeclaracion::registrarCierre($evento, 4, 'Refrigerio');

        $respuesta = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.declaracion'));

        $respuesta->assertOk();
        $respuesta->assertSee('Declaración de fondos');
        $respuesta->assertSee('Cuotas pagadas');
        $respuesta->assertSee('Eventos');
        $respuesta->assertSee('Partido de Fútbol');
        $respuesta->assertSee('Octubre 2026');
        $respuesta->assertSee('S/ 10.00');
        $respuesta->assertSee('S/ 5.00');
        $respuesta->assertSee('S/ 4.00');
        $respuesta->assertSee('Cerrar y declarar uso');
        $respuesta->assertSee('Actualizar declaración');
    }

    public function test_el_listado_de_eventos_permite_cerrar_con_un_clic(): void
    {
        $evento = $this->datosDemo();
        $admin = $this->admin();

        $respuesta = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.eventos.index'));

        $respuesta->assertOk();
        $respuesta->assertSee('Cerrar');
        $respuesta->assertSee('function abrirCierre', false);
        $respuesta->assertSee('/cerrar', false);
        $respuesta->assertSee('Partido de Fútbol');
    }

    public function test_la_declaracion_calcula_recaudado_vs_utilizado(): void
    {
        $evento = $this->datosDemo();
        SeccionKDeclaracion::registrarCierre($evento, 2);

        $datos = SeccionKEstadisticas::declaracionFondos();

        $this->assertSame(10.0, $datos['cuotas']['recaudado']);
        $this->assertSame(0.0, $datos['cuotas']['utilizado']);
        $this->assertSame(10.0, $datos['cuotas']['saldo']);
        $this->assertSame(5.0, $datos['eventos']['recaudado']);
        $this->assertSame(2.0, $datos['eventos']['utilizado']);
        $this->assertSame(3.0, $datos['eventos']['saldo']);
        $this->assertSame(15.0, $datos['total']['recaudado']);
        $this->assertSame(2.0, $datos['total']['utilizado']);
        $this->assertSame(13.0, $datos['total']['saldo']);
        $this->assertSame(1, $datos['total']['cerrados']);
        $this->assertSame(1, $datos['total']['abiertos']);
    }

    public function test_asegurar_esquema_crea_solo_la_tabla_nueva(): void
    {
        Schema::dropIfExists('seccionk_declaraciones');
        $this->assertFalse(Schema::hasTable('seccionk_declaraciones'));

        $promociones = SeccionKPromocion::count();
        $eventos = SeccionKEvento::count();
        $participaciones = SeccionKParticipacion::count();

        SeccionKEsquema::asegurarDeclaraciones();

        $this->assertTrue(Schema::hasTable('seccionk_declaraciones'));
        $this->assertSame($promociones, SeccionKPromocion::count());
        $this->assertSame($eventos, SeccionKEvento::count());
        $this->assertSame($participaciones, SeccionKParticipacion::count());

        SeccionKEsquema::asegurarDeclaraciones();
        $this->assertTrue(Schema::hasTable('seccionk_declaraciones'));
    }

    public function test_el_comando_artisan_no_toca_las_tablas_existentes(): void
    {
        $this->datosDemo();
        $huella = [
            'promociones' => SeccionKPromocion::orderBy('id')->pluck('dni')->all(),
            'eventos' => SeccionKEvento::orderBy('id')->pluck('nombre')->all(),
            'pagos' => SeccionKParticipacion::orderBy('id')->pluck('monto_aportado')->map(fn ($v) => (string) $v)->all(),
        ];

        $this->artisan('seccionk:asegurar-esquema')
            ->assertSuccessful()
            ->expectsOutput('Tabla seccionk_declaraciones lista. No se modificó la data existente.');

        $this->assertSame($huella['promociones'], SeccionKPromocion::orderBy('id')->pluck('dni')->all());
        $this->assertSame($huella['eventos'], SeccionKEvento::orderBy('id')->pluck('nombre')->all());
        $this->assertSame($huella['pagos'], SeccionKParticipacion::orderBy('id')->pluck('monto_aportado')->map(fn ($v) => (string) $v)->all());
        $this->assertSame(0, DB::table('seccionk_declaraciones')->count());
    }

    public function test_invitado_no_accede_a_declaracion_ni_cierre(): void
    {
        $evento = $this->datosDemo();

        $this->get(route('seccionk.admin.declaracion'))
            ->assertRedirect(route('seccionk.admin.login'));

        $this->post(route('seccionk.admin.eventos.cerrar', $evento), [
            'monto_utilizado' => 1,
        ])->assertRedirect(route('seccionk.admin.login'));
    }

    private function datosDemo(): SeccionKEvento
    {
        $yuri = $this->promocion('42867691', 'Yuri', 'Vargas', 'Lazo', true);
        $this->promocion('87654321', 'Ana', 'López', 'Díaz', true);

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
