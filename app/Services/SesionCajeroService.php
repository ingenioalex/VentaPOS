<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SesionCajeroService
{
    public function __construct(private CierreCajaService $cierreService) {}

    public function abrirSesion(Usuario $cajero, Request $request): void
    {
        if ($cajero->rol !== 'cajero') {
            return;
        }

        if ($cajero->en_sesion) {
            throw new RuntimeException('Este cajero ya tiene una sesión activa. El supervisor debe cerrarla primero.');
        }

        $cajero->update([
            'en_sesion' => true,
            'sesion_inicio_en' => now(),
            'sesion_laravel_id' => $request->session()->getId(),
        ]);
    }

    public function cerrarSesion(Usuario $usuario): void
    {
        if (! $usuario->en_sesion) {
            return;
        }

        $usuario->update([
            'en_sesion' => false,
            'sesion_inicio_en' => null,
            'sesion_laravel_id' => null,
        ]);
    }

    public function cerrarSesionForzada(Usuario $cajero): void
    {
        if ($cajero->sesion_laravel_id) {
            DB::table('sessions')->where('id', $cajero->sesion_laravel_id)->delete();
        }

        $this->cerrarSesion($cajero);
    }

    /**
     * @return Collection<int, array{usuario: Usuario, resumen: array}>
     */
    public function cajerosConectados(int $empresaId): Collection
    {
        return Usuario::where('empresa_id', $empresaId)
            ->where('rol', 'cajero')
            ->where('en_sesion', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Usuario $c) => [
                'usuario' => $c,
                'resumen' => $this->cierreService->resumenActual($c),
            ]);
    }

    public function cierreXDesdePos(
        Usuario $cajero,
        Usuario $supervisor,
        float $montoEfectivoContado,
        ?string $observaciones = null,
    ) {
        $this->validarCajeroEnSesion($cajero, $supervisor);

        if ($montoEfectivoContado < 0) {
            throw new RuntimeException('El monto contado no puede ser negativo.');
        }

        return $this->cierreService->cerrarX(
            $cajero,
            $montoEfectivoContado,
            $observaciones,
            $supervisor,
        );
    }

    public function cierreSupervisorYCerrarSesion(
        Usuario $cajero,
        Usuario $supervisor,
        float $montoEfectivoContado,
        ?string $observaciones = null,
    ) {
        $this->validarCajeroEnSesion($cajero, $supervisor);

        $cierre = $this->cierreService->cerrarX(
            $cajero,
            $montoEfectivoContado,
            $observaciones,
            $supervisor,
        );

        $this->cerrarSesionForzada($cajero);

        return $cierre;
    }

    private function validarCajeroEnSesion(Usuario $cajero, Usuario $supervisor): void
    {
        if ($cajero->empresa_id !== $supervisor->empresa_id) {
            throw new RuntimeException('El cajero no pertenece a su empresa.');
        }

        if ($cajero->rol !== 'cajero') {
            throw new RuntimeException('Solo aplica a usuarios cajero.');
        }

        if (! $cajero->en_sesion) {
            throw new RuntimeException('El cajero no tiene sesión activa.');
        }

        if (! $supervisor->puedeGestionar()) {
            throw new RuntimeException('Credenciales de supervisor inválidas.');
        }
    }
}
