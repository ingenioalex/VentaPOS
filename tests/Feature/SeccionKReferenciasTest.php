<?php

namespace Tests\Feature;

use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionKReferenciasTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_panel_lista_referencias_de_otras_promociones(): void
    {
        $admin = SeccionKUsuario::create([
            'usuario' => 'admin',
            'password' => 'seccionk2026',
            'nombre' => 'Administrador',
            'activo' => true,
        ]);

        SeccionKPromocion::create([
            'dni' => '40404040',
            'nombre' => 'Usuario',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Lazo',
            'celular' => '900900900',
            'referencias' => 'Manuel Llauce Pizarro celular 909090678',
            'participa_cuotas' => true,
            'activo' => true,
        ]);
        SeccionKPromocion::create([
            'dni' => '12345678',
            'nombre' => 'Ana',
            'apellido_paterno' => 'López',
            'apellido_materno' => 'Díaz',
            'referencias' => null,
            'participa_cuotas' => true,
            'activo' => true,
        ]);

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.referencias'));

        $respuesta->assertOk();
        $respuesta->assertSee('Referencias de promociones');
        $respuesta->assertSee('Usuario Prueba Lazo');
        $respuesta->assertSee('Manuel Llauce Pizarro celular 909090678');
        $respuesta->assertDontSee('Ana López Díaz');
    }

    public function test_exportar_referencias_whatsapp_incluye_el_contacto_indicado(): void
    {
        $admin = SeccionKUsuario::create([
            'usuario' => 'admin',
            'password' => 'seccionk2026',
            'nombre' => 'Administrador',
            'activo' => true,
        ]);
        SeccionKPromocion::create([
            'dni' => '40404040',
            'nombre' => 'Usuario',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Lazo',
            'celular' => '900900900',
            'referencias' => 'Manuel Llauce Pizarro celular 909090678',
            'participa_cuotas' => true,
            'activo' => true,
        ]);

        $respuesta = $this->actingAs($admin, 'seccionk')->get(route('seccionk.admin.referencias.exportar', [
            'formato' => 'whatsapp',
        ]));

        $respuesta->assertOk();
        $respuesta->assertSee('REFERENCIAS');
        $respuesta->assertSee('Manuel Llauce Pizarro celular 909090678');
        $respuesta->assertSee('900900900');
    }
}
