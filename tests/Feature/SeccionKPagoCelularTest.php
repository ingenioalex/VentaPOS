<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SeccionKPagoCelularTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_pago_sin_foto_guarda_el_aporte(): void
    {
        [$admin, $promo, $cuota] = $this->datos();

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.promociones.pagos.store', [$promo, $cuota]), [
                'monto_aportado' => 50,
                'numero_transaccion' => 'YAPE-123',
                'fecha_pago' => '2026-10-22',
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo));

        $this->assertDatabaseHas('seccionk_participaciones', [
            'promocion_id' => $promo->id,
            'evento_id' => $cuota->id,
            'pago' => 1,
            'monto_aportado' => 50,
            'numero_transaccion' => 'YAPE-123',
        ]);
    }

    public function test_registrar_pago_con_imagen_pequena_guarda_el_sustento(): void
    {
        [$admin, $promo, $cuota] = $this->datos();
        $imagen = UploadedFile::fake()->image('yape.jpg', 200, 200);

        $this->actingAs($admin, 'seccionk')
            ->post(route('seccionk.admin.promociones.pagos.store', [$promo, $cuota]), [
                'monto_aportado' => 50,
                'imagen' => $imagen,
            ])
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo));

        $pago = SeccionKParticipacion::where('promocion_id', $promo->id)->where('evento_id', $cuota->id)->first();
        $this->assertNotNull($pago?->imagen_sustento);
        $this->assertFileExists(public_path('uploads/seccionk/'.$pago->imagen_sustento));
        @unlink(public_path('uploads/seccionk/'.$pago->imagen_sustento));
    }

    public function test_abrir_la_url_de_pago_con_get_no_da_405(): void
    {
        [$admin, $promo, $cuota] = $this->datos();

        $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.promociones.pagos.store', [$promo, $cuota]))
            ->assertRedirect(route('seccionk.admin.promociones.show', $promo))
            ->assertSessionHas('error');
    }

    public function test_el_formulario_de_pagos_reduce_fotos_de_celular(): void
    {
        [$admin, $promo] = $this->datos();

        $html = $this->actingAs($admin, 'seccionk')
            ->get(route('seccionk.admin.promociones.show', $promo))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Reduciendo foto', $html);
        $this->assertStringContainsString('Foto opcional', $html);
        $this->assertStringContainsString('el pago se guarda igual', $html);
        $this->assertStringContainsString('HTMLFormElement.prototype.submit', $html);
        $this->assertStringContainsString('accept="image/jpeg,image/png,image/webp,image/*"', $html);
    }

    public function test_la_pagina_de_foto_pesada_no_exige_post(): void
    {
        $this->get(route('seccionk.error-subida'))
            ->assertOk()
            ->assertSee('La foto no se pudo subir')
            ->assertSee('sin foto');
    }

    public function test_un_get_a_una_ruta_post_no_muestra_405(): void
    {
        $admin = $this->datos()[0];

        $this->actingAs($admin, 'seccionk')
            ->from(route('seccionk.admin.dashboard'))
            ->get('/seccionk/admin/logout')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_el_perfil_publico_tambien_reduce_la_foto(): void
    {
        [, $promo, $cuota] = $this->datos();

        $html = $this->withSession(['seccionk_identificado' => $promo->id])
            ->get(route('seccionk.perfil', $promo))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Reduciendo foto', $html);
        $this->assertStringContainsString('Cargar imagen (opcional)', $html);

        $this->withSession(['seccionk_identificado' => $promo->id])
            ->get(route('seccionk.perfil.cuota.comentario', [$promo->id, $cuota->id]))
            ->assertRedirect(route('seccionk.perfil', $promo->id))
            ->assertSessionHas('error');
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
            'nombre' => 'Yuri',
            'apellido_paterno' => 'Vargas',
            'apellido_materno' => 'Lazo',
            'participa_cuotas' => true,
            'activo' => true,
        ]);
        $cuota = SeccionKEvento::create([
            'nombre' => 'Cuota Octubre 2026',
            'fecha' => '2026-10-01',
            'monto' => 50,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => 'Octubre 2026',
            'activo' => true,
        ]);

        return [$admin, $promo, $cuota];
    }
}
