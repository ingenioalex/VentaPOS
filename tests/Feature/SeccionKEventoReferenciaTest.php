<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKEventoEdicion;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use App\Support\SeccionKEsquema;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeccionKEventoReferenciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-04 15:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_la_fecha_del_evento_se_evalua_en_horario_de_lima(): void
    {
        $evento = SeccionKEvento::create([
            'nombre' => 'Partido 04 Nov',
            'fecha' => '2026-11-04',
            'monto' => 5,
            'tipo' => 'evento',
            'activo' => true,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-05 03:30:00', 'UTC'));
        $this->assertFalse($evento->fechaYaPaso());

        Carbon::setTestNow(Carbon::parse('2026-11-05 05:00:00', 'UTC'));
        $this->assertTrue($evento->fresh()->fechaYaPaso());
    }

    public function test_un_evento_pasado_se_muestra_como_referencia_en_promociones(): void
    {
        [$admin, $promo, $pasado, $vigente, $cuota] = $this->datos();

        $html = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.promociones.show', $promo))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Partido de Fútbol', $html);
        $this->assertStringContainsString('Solo referencia · evento ya realizado', $html);
        $this->assertStringContainsString('Activar para modificar', $html);
        $this->assertStringContainsString('Partido Futbol 04 Nov', $html);
        $this->assertStringContainsString('Registrar pago', $html);
        $this->assertStringContainsString('name="monto_aportado"', $html);
        $this->assertStringNotContainsString('Actualizar pago', $html);
        $this->assertTrue(strpos($html, 'Octubre 2026') < strpos($html, 'Registrar pago'));
        $this->assertNotFalse(strpos($html, route('seccionk.admin.promociones.pagos.store', [$promo, $vigente])));
        $this->assertNotFalse(strpos($html, route('seccionk.admin.promociones.pagos.store', [$promo, $cuota])));
        $this->assertFalse(strpos($html, route('seccionk.admin.promociones.pagos.store', [$promo, $pasado])));
    }

    public function test_el_perfil_publico_muestra_el_evento_pasado_como_referencia(): void
    {
        [, $promo] = $this->datos();

        $this->withSession(['seccionk_identificado' => $promo->id])
            ->get(route('seccionk.perfil', $promo->id))
            ->assertOk()
            ->assertSee('Partido de Fútbol')
            ->assertSee('Solo referencia · evento ya realizado')
            ->assertDontSee('Activar para modificar');
    }

    public function test_no_se_puede_cambiar_un_pago_de_evento_pasado_sin_activarlo(): void
    {
        [$admin, $promo, $pasado] = $this->datos();
        $antes = SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'pago', 'monto_aportado'])->toArray();
        $eventoAntes = $pasado->only(['id', 'nombre', 'monto', 'tipo', 'activo']);
        $fechaAntes = $pasado->fecha->toDateString();

        $this->actingAs($admin, 'seccionk')
            ->from(route('seccionk.admin.promociones.show', $promo))
            ->post(route('seccionk.admin.promociones.pagos.store', [$promo, $pasado]), [
                'monto_aportado' => 99,
                'fecha_pago' => '2026-11-04',
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo))
            ->assertSessionHas('error');

        $this->assertSame(
            $antes,
            SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'pago', 'monto_aportado'])->toArray()
        );
        $this->assertSame($eventoAntes, $pasado->fresh()->only(['id', 'nombre', 'monto', 'tipo', 'activo']));
        $this->assertSame($fechaAntes, $pasado->fresh()->fecha->toDateString());
    }

    public function test_el_admin_activa_el_evento_pasado_sin_mover_la_data_existente(): void
    {
        [$admin, $promo, $pasado] = $this->datos();
        $eventoAntes = $pasado->only(['id', 'nombre', 'monto', 'tipo', 'activo']);
        $fechaAntes = $pasado->fecha->toDateString();
        $aportesAntes = SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'pago', 'monto_aportado'])->toArray();

        $this->actingAs($admin, 'seccionk')
            ->from(route('seccionk.admin.promociones.show', $promo))
            ->post(route('seccionk.admin.eventos.edicion', $pasado), [
                'abierto' => 1,
                'volver' => 'promocion',
                'promocion' => $promo->id,
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('seccionk_eventos_edicion', [
            'evento_id' => $pasado->id,
            'abierto' => 1,
        ]);
        $this->assertSame($eventoAntes, $pasado->fresh()->only(['id', 'nombre', 'monto', 'tipo', 'activo']));
        $this->assertSame($fechaAntes, $pasado->fresh()->fecha->toDateString());
        $this->assertSame(
            $aportesAntes,
            SeccionKParticipacion::orderBy('id')->get(['id', 'promocion_id', 'evento_id', 'pago', 'monto_aportado'])->toArray()
        );
        $this->assertFalse(Schema::hasColumn('seccionk_eventos', 'abierto'));
        $this->assertFalse(Schema::hasColumn('seccionk_eventos', 'editable'));

        $html = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.promociones.show', $promo))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Activado para modificar', $html);
        $this->assertStringContainsString('Actualizar pago', $html);
        $this->assertStringContainsString('Volver a referencia', $html);
    }

    public function test_despues_de_activar_se_puede_actualizar_el_pago_y_volver_a_referencia(): void
    {
        [$admin, $promo, $pasado] = $this->datos();

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.eventos.edicion', $pasado), [
                'abierto' => 1,
                'volver' => 'promocion',
                'promocion' => $promo->id,
            ])
            ->assertRedirect();

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.promociones.pagos.store', [$promo, $pasado]), [
                'monto_aportado' => 12.5,
                'observaciones' => 'Ajuste posterior',
                'fecha_pago' => '2026-11-04',
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo));

        $this->assertDatabaseHas('seccionk_participaciones', [
            'promocion_id' => $promo->id,
            'evento_id' => $pasado->id,
            'monto_aportado' => 12.5,
            'observaciones' => 'Ajuste posterior',
        ]);

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.eventos.edicion', $pasado), [
                'abierto' => 0,
                'volver' => 'promocion',
                'promocion' => $promo->id,
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo));

        $this->assertDatabaseHas('seccionk_eventos_edicion', [
            'evento_id' => $pasado->id,
            'abierto' => 0,
        ]);

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.promociones.pagos.store', [$promo, $pasado]), [
                'monto_aportado' => 1,
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('seccionk_participaciones', [
            'promocion_id' => $promo->id,
            'evento_id' => $pasado->id,
            'monto_aportado' => 12.5,
        ]);
    }

    public function test_las_cuotas_siguen_editables_aunque_su_fecha_haya_pasado(): void
    {
        [$admin, $promo, , , $cuota] = $this->datos();

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.promociones.pagos.store', [$promo, $cuota]), [
                'monto_aportado' => 10,
                'fecha_pago' => '2026-11-04',
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('seccionk_participaciones', [
            'promocion_id' => $promo->id,
            'evento_id' => $cuota->id,
            'monto_aportado' => 10,
            'pago' => 1,
        ]);
    }

    public function test_el_admin_ve_referencia_en_eventos_y_no_guarda_participantes_si_esta_cerrado(): void
    {
        [$admin, $promo, $pasado] = $this->datos();

        $html = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.eventos.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Solo referencia', $html);
        $this->assertStringContainsString('Activar para modificar', $html);

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.eventos.participantes', $pasado))
            ->assertOk()
            ->assertSee('Solo referencia · evento ya realizado')
            ->assertDontSee('Guardar participantes');

        $this->actingAs($admin, 'seccionk')
            ->from(route('seccionk.admin.eventos.participantes', $pasado))
            ->post(route('seccionk.admin.eventos.participantes.store', $pasado), [
                'participantes' => [
                    $promo->id => [
                        'participo' => 1,
                        'pago' => 1,
                        'monto_aportado' => 80,
                    ],
                ],
            ])
            ->assertRedirect(route('seccionk.admin.eventos.participantes', $pasado))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('seccionk_participaciones', [
            'promocion_id' => $promo->id,
            'evento_id' => $pasado->id,
            'monto_aportado' => 5,
        ]);
    }

    public function test_asegurar_esquema_crea_la_tabla_de_edicion_sin_tocar_eventos(): void
    {
        $this->datos();
        Schema::dropIfExists('seccionk_eventos_edicion');
        $this->assertFalse(Schema::hasTable('seccionk_eventos_edicion'));

        $huella = [
            'promociones' => SeccionKPromocion::orderBy('id')->pluck('dni')->all(),
            'eventos' => SeccionKEvento::orderBy('id')->get(['id', 'nombre', 'fecha', 'monto', 'tipo', 'activo'])->toArray(),
            'pagos' => SeccionKParticipacion::orderBy('id')->pluck('monto_aportado')->map(fn ($v) => (string) $v)->all(),
        ];

        SeccionKEsquema::asegurarEdicionEventos();

        $this->assertTrue(Schema::hasTable('seccionk_eventos_edicion'));
        $this->assertSame($huella['promociones'], SeccionKPromocion::orderBy('id')->pluck('dni')->all());
        $this->assertSame(
            $huella['eventos'],
            SeccionKEvento::orderBy('id')->get(['id', 'nombre', 'fecha', 'monto', 'tipo', 'activo'])->toArray()
        );
        $this->assertSame(
            $huella['pagos'],
            SeccionKParticipacion::orderBy('id')->pluck('monto_aportado')->map(fn ($v) => (string) $v)->all()
        );

        $this->artisan('seccionk:asegurar-esquema')
            ->assertSuccessful()
            ->expectsOutput('Tabla seccionk_eventos_edicion lista. No se modificó la data existente.');

        $this->assertSame(0, SeccionKEventoEdicion::count());
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
        $cuota = SeccionKEvento::create([
            'nombre' => 'Octubre 2026',
            'fecha' => '2026-10-30',
            'monto' => 10,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Octubre 2026',
            'activo' => true,
        ]);
        $pasado = SeccionKEvento::create([
            'nombre' => 'Partido de Fútbol',
            'fecha' => '2026-10-21',
            'monto' => 5,
            'tipo' => 'evento',
            'activo' => true,
        ]);
        $vigente = SeccionKEvento::create([
            'nombre' => 'Partido Futbol 04 Nov',
            'fecha' => '2026-11-04',
            'monto' => 5,
            'tipo' => 'evento',
            'activo' => true,
        ]);

        SeccionKParticipacion::create([
            'promocion_id' => $promo->id,
            'evento_id' => $pasado->id,
            'pago' => true,
            'participo' => true,
            'monto_aportado' => 5,
            'fecha_pago' => '2026-10-22',
            'observaciones' => 'Pago efectivo dio 10 resta 5',
        ]);

        return [$admin, $promo, $pasado, $vigente, $cuota];
    }
}
