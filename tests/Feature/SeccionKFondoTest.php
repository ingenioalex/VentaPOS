<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKMovimiento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use App\Support\SeccionKEsquema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeccionKFondoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_detalle_del_evento_muestra_ingresos_y_egresos(): void
    {
        [$admin, $evento] = $this->datos();

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.eventos.detalle', $evento))
            ->assertOk()
            ->assertSee('Ingresos / egresos del fondo')
            ->assertSee('Préstamo (egreso)')
            ->assertSee('Devolución de préstamo (ingreso)')
            ->assertSee('Registrar movimiento')
            ->assertDontSee('Préstamos pendientes');
    }

    public function test_un_prestamo_aparece_en_el_dashboard_hasta_que_lo_devuelven(): void
    {
        [$admin, $evento, $promo] = $this->datos();
        $huellaEventos = SeccionKEvento::orderBy('id')->get(['id', 'nombre', 'monto', 'tipo', 'activo'])->toArray();
        $huellaPagos = SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'monto_aportado'])->toArray();

        $this->actingAs($admin, 'seccionk')
            ->from(route('seccionk.admin.eventos.detalle', $evento))
            ->post(route('seccionk.admin.fondo.store'), [
                'tipo' => 'prestamo',
                'persona' => 'Juan',
                'monto' => 100,
                'fecha' => '2026-11-03',
                'comentario' => 'Se sacó 100 soles para prestar a Juan',
                'volver' => 'detalle',
                'evento' => $evento->id,
            ])
            ->assertRedirect(route('seccionk.admin.eventos.detalle', $evento))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('seccionk_movimientos', [
            'tipo' => 'prestamo',
            'persona' => 'Juan',
            'monto' => 100,
            'comentario' => 'Se sacó 100 soles para prestar a Juan',
        ]);
        $this->assertSame($huellaEventos, SeccionKEvento::orderBy('id')->get(['id', 'nombre', 'monto', 'tipo', 'activo'])->toArray());
        $this->assertSame($huellaPagos, SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'monto_aportado'])->toArray());
        $this->assertFalse(Schema::hasColumn('seccionk_eventos', 'prestamo'));

        $dashboard = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.dashboard'))
            ->assertOk();
        $dashboard->assertSee('Préstamos pendientes');
        $dashboard->assertSee('Juan');
        $dashboard->assertSee('03/11/2026');
        $dashboard->assertSee('S/ 100.00');
        $dashboard->assertSee('Fondo total disponible');
        $dashboard->assertSee('Se sacó 100 soles para prestar a Juan');

        $prestamo = SeccionKMovimiento::where('tipo', 'prestamo')->firstOrFail();

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.fondo.store'), [
                'tipo' => 'devolucion',
                'persona' => 'Juan',
                'prestamo_id' => $prestamo->id,
                'monto' => 100,
                'fecha' => '2026-11-04',
                'comentario' => 'Juan devolvió el préstamo',
                'volver' => 'fondo',
            ])
            ->assertRedirect(route('seccionk.admin.fondo'));

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Préstamos pendientes')
            ->assertDontSee('Fondo total disponible')
            ->assertDontSee('Juan');

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.fondo'))
            ->assertOk()
            ->assertSee('Se sacó 100 soles para prestar a Juan')
            ->assertSee('Juan devolvió el préstamo')
            ->assertSee('Ya devolvió el préstamo')
            ->assertDontSee('Préstamos pendientes ·');

        $this->assertSame(5.0, (float) SeccionKParticipacion::where('promocion_id', $promo->id)->value('monto_aportado'));
    }

    public function test_no_se_puede_devolver_mas_de_lo_pendiente(): void
    {
        [$admin] = $this->datos();
        $prestamo = SeccionKMovimiento::create([
            'tipo' => 'prestamo',
            'persona' => 'Juan',
            'monto' => 100,
            'fecha' => '2026-11-03',
            'comentario' => 'Préstamo inicial',
        ]);

        $this->actingAs($admin, 'seccionk')
            ->from(route('seccionk.admin.fondo'))
            ->post(route('seccionk.admin.fondo.store'), [
                'tipo' => 'devolucion',
                'persona' => 'Juan',
                'prestamo_id' => $prestamo->id,
                'monto' => 150,
                'fecha' => '2026-11-04',
                'comentario' => 'De más',
            ])
            ->assertRedirect(route('seccionk.admin.fondo'))
            ->assertSessionHasErrors('monto');

        $this->assertSame(1, SeccionKMovimiento::count());
    }

    public function test_sin_prestamos_el_dashboard_no_muestra_el_bloque(): void
    {
        [$admin] = $this->datos();

        $html = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Préstamos pendientes', $html);
        $this->assertStringContainsString('Ingresos / egresos', $html);
        $this->assertStringContainsString('Eventos · recaudado − declarado', $html);
    }

    public function test_asegurar_esquema_crea_movimientos_sin_tocar_data(): void
    {
        $this->datos();
        Schema::dropIfExists('seccionk_movimientos');
        $this->assertFalse(Schema::hasTable('seccionk_movimientos'));

        $huella = [
            'promociones' => SeccionKPromocion::orderBy('id')->pluck('dni')->all(),
            'eventos' => SeccionKEvento::orderBy('id')->pluck('nombre')->all(),
            'pagos' => SeccionKParticipacion::orderBy('id')->pluck('monto_aportado')->map(fn ($v) => (string) $v)->all(),
        ];

        SeccionKEsquema::asegurarMovimientos();

        $this->assertTrue(Schema::hasTable('seccionk_movimientos'));
        $this->assertSame($huella['promociones'], SeccionKPromocion::orderBy('id')->pluck('dni')->all());
        $this->assertSame($huella['eventos'], SeccionKEvento::orderBy('id')->pluck('nombre')->all());
        $this->assertSame($huella['pagos'], SeccionKParticipacion::orderBy('id')->pluck('monto_aportado')->map(fn ($v) => (string) $v)->all());

        $this->artisan('seccionk:asegurar-esquema')
            ->assertSuccessful()
            ->expectsOutput('Tabla seccionk_movimientos lista. No se modificó la data existente.');

        $this->assertSame(0, SeccionKMovimiento::count());
    }

    public function test_invitado_no_registra_movimientos(): void
    {
        $this->post(route('seccionk.admin.fondo.store'), [
            'tipo' => 'prestamo',
            'persona' => 'Juan',
            'monto' => 10,
            'fecha' => '2026-11-03',
        ])->assertRedirect(route('seccionk.admin.login'));
    }

    private function datos(): array
    {
        $admin = SeccionKUsuario::create([
            'usuario' => 'admin',
            'password' => 'seccionk2026',
            'nombre' => 'Administrador',
            'activo' => true,
        ]);
        $promo = SeccionKPromocion::create([
            'dni' => '42867691',
            'nombre' => 'Víctor',
            'apellido_paterno' => 'Chingay',
            'apellido_materno' => 'Sánchez',
            'participa_cuotas' => true,
            'activo' => true,
        ]);
        $evento = SeccionKEvento::create([
            'nombre' => 'Partido Futbol 04 Nov',
            'fecha' => '2026-11-04',
            'monto' => 5,
            'tipo' => 'evento',
            'activo' => true,
        ]);
        SeccionKParticipacion::create([
            'promocion_id' => $promo->id,
            'evento_id' => $evento->id,
            'pago' => true,
            'participo' => true,
            'monto_aportado' => 5,
        ]);

        return [$admin, $evento, $promo];
    }
}
