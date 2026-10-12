<?php

namespace Tests\Feature;

use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKUsuario;
use App\Support\SeccionKLimpiarCuotas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionKLimpiarCuotasTest extends TestCase
{
    use RefreshDatabase;

    public function test_fusiona_cuotas_del_mismo_mes_y_conserva_pagos(): void
    {
        $primera = $this->cuota('Noviembre 2026', '2026-11-01', 50);
        $segunda = $this->cuota('Noviembre 2026', '2026-11-15', 10);

        $juan = $this->promocion('12345678', 'Juan', 'García', 'Pérez');
        $ana = $this->promocion('87654321', 'Ana', 'López', 'Díaz');

        $this->pago($juan, $primera, 50);
        $this->pago($ana, $segunda, 10);

        $resultado = SeccionKLimpiarCuotas::ejecutar();

        $this->assertSame(1, $resultado['meses']);
        $this->assertSame(1, $resultado['eliminados']);
        $this->assertSame(1, SeccionKEvento::cuotasMensuales()->count());

        $unica = SeccionKEvento::cuotasMensuales()->first();
        $this->assertSame('Noviembre 2026', $unica->mes_cuota);
        $this->assertTrue(
            SeccionKParticipacion::where('evento_id', $unica->id)->where('promocion_id', $juan->id)->where('pago', true)->exists()
        );
        $this->assertTrue(
            SeccionKParticipacion::where('evento_id', $unica->id)->where('promocion_id', $ana->id)->where('pago', true)->exists()
        );
    }

    public function test_no_permite_crear_otra_cuota_del_mismo_mes(): void
    {
        $this->cuota('Octubre 2026', '2026-10-01', 50);
        $admin = SeccionKUsuario::create([
            'usuario' => 'admin',
            'password' => 'seccionk2026',
            'nombre' => 'Administrador',
            'activo' => true,
        ]);

        $respuesta = $this->actingAs($admin, 'seccionk')->post(route('seccionk.admin.eventos.store'), [
            'nombre' => 'Otra cuota octubre',
            'fecha' => '2026-10-20',
            'monto' => 10,
            'tipo' => 'cuota_mensual',
        ]);

        $respuesta->assertSessionHasErrors('fecha');
        $this->assertSame(1, SeccionKEvento::cuotasMensuales()->count());
    }

    private function cuota(string $mes, string $fecha, float $monto): SeccionKEvento
    {
        return SeccionKEvento::create([
            'nombre' => 'Cuota '.$mes.' '.$monto,
            'fecha' => $fecha,
            'monto' => $monto,
            'tipo' => 'cuota_mensual',
            'mes_cuota' => $mes,
            'activo' => true,
        ]);
    }

    private function promocion(string $dni, string $nombre, string $paterno, string $materno): SeccionKPromocion
    {
        return SeccionKPromocion::create([
            'dni' => $dni,
            'nombre' => $nombre,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'participa_cuotas' => true,
            'activo' => true,
        ]);
    }

    private function pago(SeccionKPromocion $promo, SeccionKEvento $cuota, float $monto): SeccionKParticipacion
    {
        return SeccionKParticipacion::create([
            'promocion_id' => $promo->id,
            'evento_id' => $cuota->id,
            'participo' => true,
            'pago' => true,
            'monto_aportado' => $monto,
            'fecha_pago' => $cuota->fecha,
        ]);
    }
}
