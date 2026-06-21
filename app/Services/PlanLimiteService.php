<?php

namespace App\Services;

use App\Models\Empresa;

class PlanLimiteService
{
    /** @return array{bonificados: int, extras_pago: int, nota_interna: ?string} */
    public function cuposPersonalizados(Empresa $empresa): array
    {
        $cupos = ($empresa->configuracion ?? [])['cupos_usuarios'] ?? [];

        return [
            'bonificados' => max(0, (int) ($cupos['bonificados'] ?? 0)),
            'extras_pago' => max(0, (int) ($cupos['extras_pago'] ?? 0)),
            'nota_interna' => isset($cupos['nota_interna']) ? trim((string) $cupos['nota_interna']) : null,
        ];
    }

    public function limiteBasePlan(Empresa $empresa): int
    {
        $plan = $empresa->plan ?? 'beta';
        $limite = config("planes.{$plan}.limites.usuarios_max");

        return is_numeric($limite) ? (int) $limite : 3;
    }

    public function precioUsuarioExtraMensual(): float
    {
        return (float) config('planes.usuario_extra.precio_mensual', 5);
    }

    public function limiteUsuarios(Empresa $empresa): int
    {
        $cupos = $this->cuposPersonalizados($empresa);

        return $this->limiteBasePlan($empresa) + $cupos['bonificados'] + $cupos['extras_pago'];
    }

    public function limiteProductos(Empresa $empresa): ?int
    {
        $plan = $empresa->plan ?? 'beta';
        $limite = config("planes.{$plan}.limites.productos_max");

        return is_numeric($limite) ? (int) $limite : null;
    }

    public function usuariosActivos(Empresa $empresa): int
    {
        return $empresa->usuarios()->where('activo', true)->count();
    }

    public function usuariosRestantes(Empresa $empresa): int
    {
        return max(0, $this->limiteUsuarios($empresa) - $this->usuariosActivos($empresa));
    }

    public function puedeCrearUsuario(Empresa $empresa): bool
    {
        return $this->usuariosActivos($empresa) < $this->limiteUsuarios($empresa);
    }

    public function puedeActivarUsuario(Empresa $empresa, bool $yaActivo): bool
    {
        if ($yaActivo) {
            return true;
        }

        return $this->puedeCrearUsuario($empresa);
    }

    /** @return array<string, int|float|string|null> */
    public function desglose(Empresa $empresa): array
    {
        $cupos = $this->cuposPersonalizados($empresa);
        $base = $this->limiteBasePlan($empresa);
        $extrasPago = $cupos['extras_pago'];
        $precioExtra = $this->precioUsuarioExtraMensual();
        $activos = $this->usuariosActivos($empresa);
        $total = $base + $cupos['bonificados'] + $extrasPago;

        return [
            'plan_base' => $base,
            'bonificados' => $cupos['bonificados'],
            'extras_pago' => $extrasPago,
            'total' => $total,
            'activos' => $activos,
            'restantes' => max(0, $total - $activos),
            'precio_extra_mensual' => $precioExtra,
            'costo_extras_mensual' => round($extrasPago * $precioExtra, 2),
            'nota_interna' => $cupos['nota_interna'],
            'plan_nombre' => $this->etiquetaPlan($empresa),
        ];
    }

    public function guardarCupos(Empresa $empresa, int $bonificados, int $extrasPago, ?string $notaInterna = null): void
    {
        $config = $empresa->configuracion ?? [];
        $config['cupos_usuarios'] = [
            'bonificados' => max(0, $bonificados),
            'extras_pago' => max(0, $extrasPago),
            'nota_interna' => $notaInterna ? trim($notaInterna) : null,
        ];
        $empresa->update(['configuracion' => $config]);
    }

    public function etiquetaPlan(Empresa $empresa): string
    {
        $plan = $empresa->plan ?? 'beta';

        return config("planes.{$plan}.nombre", ucfirst($plan));
    }

    public function resumenUsuarios(Empresa $empresa): string
    {
        $d = $this->desglose($empresa);

        return "{$d['activos']} de {$d['total']} usuarios activos (plan {$d['plan_nombre']})";
    }

    public function equipoSugerido(Empresa $empresa): ?string
    {
        $plan = $empresa->plan ?? 'beta';

        return config("planes.{$plan}.equipo_sugerido");
    }

    public function mensajeLimiteAlcanzado(Empresa $empresa): string
    {
        $d = $this->desglose($empresa);
        $partes = ["Cupo máximo alcanzado: {$d['activos']} de {$d['total']} usuarios activos."];
        $partes[] = "Plan {$d['plan_nombre']}: {$d['plan_base']} incluidos";

        if ($d['bonificados'] > 0) {
            $partes[] = "+ {$d['bonificados']} bonificados (sin costo)";
        }
        if ($d['extras_pago'] > 0) {
            $partes[] = "+ {$d['extras_pago']} add-on de pago";
        }

        $partes[] = 'Contacte a soporte VentaPOS para ampliar cupos.';

        return implode(' ', $partes);
    }
}
