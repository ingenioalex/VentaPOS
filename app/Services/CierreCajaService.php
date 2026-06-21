<?php

namespace App\Services;

use App\Models\CierreCaja;
use App\Models\Comprobante;
use App\Models\Empresa;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CierreCajaService
{
    public function __construct(
        private TerminalSesionService $terminalSesion,
    ) {}
    public function resumenActual(Usuario $usuario): array
    {
        $empresaId = $usuario->empresa_id;
        $ultimoCierre = CierreCaja::where('empresa_id', $empresaId)
            ->where('usuario_id', $usuario->id)
            ->where('tipo_cierre', 'X')
            ->orderByDesc('fecha_fin')
            ->first();

        $fechaDesde = $ultimoCierre
            ? $ultimoCierre->fecha_fin
            : Carbon::today()->startOfDay();

        $empresa = Empresa::find($empresaId);
        $terminal = $this->terminalSesion->datosActivos();

        return array_merge(
            $this->agregarVentas($empresaId, $usuario->id, $fechaDesde),
            [
                'fecha_desde' => $fechaDesde->format('Y-m-d H:i'),
                'ultimo_cierre' => $ultimoCierre,
                'terminal' => $terminal['codigo'],
                'terminal_id' => $terminal['id'],
                'terminal_nombre' => $terminal['nombre'],
                'terminal_local' => $terminal['local'],
                'caja' => $terminal['caja'],
                'empresa_id' => $empresaId,
                'empresa_nombre' => $empresa?->nombre ?? '',
            ]
        );
    }

    public function resumenDiaEmpresa(int $empresaId): array
    {
        return $this->resumenZCierre($empresaId);
    }

    public function resumenZCierre(int $empresaId): array
    {
        $ultimoZ = CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'Z')
            ->orderByDesc('fecha_fin')
            ->first();

        $fechaDesde = $ultimoZ
            ? $ultimoZ->fecha_fin
            : Carbon::today()->startOfDay();

        $empresa = Empresa::find($empresaId);

        $cierresX = CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'X')
            ->where('fecha_fin', '>=', $fechaDesde)
            ->with(['usuario:id,nombre,usuario', 'supervisor:id,nombre'])
            ->orderBy('fecha_fin')
            ->get();

        $porCajero = $cierresX
            ->groupBy('usuario_id')
            ->map(fn ($grupo) => [
                'cajero' => $grupo->first()->usuario,
                'cantidad_cierres' => $grupo->count(),
                'cantidad_ventas' => $grupo->sum('cantidad_ventas'),
                'total_ventas' => round((float) $grupo->sum('total_ventas'), 2),
                'total_efectivo' => round((float) $grupo->sum('total_efectivo'), 2),
                'monto_contado' => round((float) $grupo->sum('monto_efectivo_contado'), 2),
                'diferencia' => round((float) $grupo->sum('diferencia_efectivo'), 2),
                'ultimo_cierre' => $grupo->last()->fecha_fin,
            ])
            ->values();

        $cajerosEnSesion = Usuario::where('empresa_id', $empresaId)
            ->where('rol', 'cajero')
            ->where('en_sesion', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'usuario', 'sesion_inicio_en']);

        return array_merge(
            $this->agregarVentas($empresaId, null, $fechaDesde),
            [
                'fecha_desde' => $fechaDesde->format('Y-m-d H:i'),
                'ultimo_cierre' => $ultimoZ,
                'terminal' => ($empresa?->configuracion ?? [])['terminal'] ?? '01',
                'caja' => ($empresa?->configuracion ?? [])['caja'] ?? '01',
                'empresa_id' => $empresaId,
                'empresa_nombre' => $empresa?->nombre ?? '',
                'cierres_x' => $cierresX,
                'por_cajero' => $porCajero,
                'cantidad_cajeros' => $porCajero->count(),
                'cantidad_cierres_x' => $cierresX->count(),
                'total_contado' => round((float) $cierresX->sum('monto_efectivo_contado'), 2),
                'total_diferencia' => round((float) $cierresX->sum('diferencia_efectivo'), 2),
                'cajeros_en_sesion' => $cajerosEnSesion,
                'cantidad_cajeros_en_sesion' => $cajerosEnSesion->count(),
                'puede_cerrar_z' => $cajerosEnSesion->isEmpty(),
            ]
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, CierreCaja>
     */
    public function cierresXEnPeriodo(CierreCaja $cierreZ)
    {
        return CierreCaja::where('empresa_id', $cierreZ->empresa_id)
            ->where('tipo_cierre', 'X')
            ->where('fecha_fin', '>=', $cierreZ->fecha_inicio)
            ->where('fecha_fin', '<=', $cierreZ->fecha_fin)
            ->with(['usuario:id,nombre,usuario', 'supervisor:id,nombre'])
            ->orderBy('fecha_fin')
            ->get();
    }

    public function cerrarX(
        Usuario $usuario,
        float $montoEfectivoContado,
        ?string $observaciones = null,
        ?Usuario $supervisor = null,
    ): CierreCaja {
        $resumen = $this->resumenActual($usuario);

        if ($montoEfectivoContado < 0) {
            throw new RuntimeException('El monto contado no puede ser negativo.');
        }

        return $this->guardarCierre(
            $usuario,
            'X',
            Carbon::parse($resumen['fecha_desde']),
            now(),
            $resumen,
            $montoEfectivoContado,
            $observaciones,
            $supervisor,
        );
    }

    public function cerrarZ(Usuario $supervisor, ?string $observaciones = null): CierreCaja
    {
        $resumen = $this->resumenZCierre($supervisor->empresa_id);

        if ($resumen['cantidad_ventas'] === 0 && $resumen['cantidad_cierres_x'] === 0) {
            throw new RuntimeException('No hay ventas ni cierres X para generar el cierre Z.');
        }

        if (! $resumen['puede_cerrar_z']) {
            $nombres = $resumen['cajeros_en_sesion']->pluck('nombre')->join(', ');
            throw new RuntimeException(
                "No se puede generar el Cierre Z: hay cajeros con sesión abierta sin Cierre X ({$nombres})."
            );
        }

        return $this->guardarCierre(
            $supervisor,
            'Z',
            Carbon::parse($resumen['fecha_desde']),
            now(),
            $resumen,
            (float) $resumen['total_contado'],
            $observaciones,
            $supervisor,
        );
    }

    private function agregarVentas(int $empresaId, ?int $usuarioId, Carbon $fechaDesde): array
    {
        $query = Comprobante::where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->where('emitido_en', '>=', $fechaDesde);

        if ($usuarioId !== null) {
            $query->where('usuario_id', $usuarioId);
        }

        $totales = (clone $query)->selectRaw('
            COUNT(*) as cantidad_ventas,
            COALESCE(SUM(total), 0) as total_ventas,
            COALESCE(AVG(total), 0) as ticket_promedio,
            COALESCE(SUM(valor_igv), 0) as total_igv
        ')->first();

        $porMedio = (clone $query)
            ->select('medio_pago', DB::raw('SUM(total) as monto'))
            ->groupBy('medio_pago')
            ->pluck('monto', 'medio_pago');

        $efectivo = (float) ($porMedio['efectivo'] ?? 0);
        $tarjeta = (float) ($porMedio['tarjeta'] ?? 0);
        $otros = (float) ($porMedio['yape'] ?? 0)
            + (float) ($porMedio['plin'] ?? 0)
            + (float) ($porMedio['mixto'] ?? 0);

        return [
            'cantidad_ventas' => (int) $totales->cantidad_ventas,
            'total_ventas' => round((float) $totales->total_ventas, 2),
            'ticket_promedio' => round((float) $totales->ticket_promedio, 2),
            'total_igv' => round((float) $totales->total_igv, 2),
            'total_efectivo' => round($efectivo, 2),
            'total_tarjeta' => round($tarjeta, 2),
            'total_otros' => round($otros, 2),
        ];
    }

    private function guardarCierre(
        Usuario $usuario,
        string $tipo,
        Carbon $fechaInicio,
        Carbon $fechaFin,
        array $resumen,
        float $montoEfectivoContado,
        ?string $observaciones,
        ?Usuario $supervisor,
    ): CierreCaja {
        $diferencia = $tipo === 'Z'
            ? round((float) ($resumen['total_diferencia'] ?? 0), 2)
            : round($montoEfectivoContado - $resumen['total_efectivo'], 2);

        return CierreCaja::create([
            'empresa_id' => $usuario->empresa_id,
            'usuario_id' => $usuario->id,
            'supervisor_usuario_id' => $supervisor?->id,
            'tipo_cierre' => $tipo,
            'terminal_id' => $resumen['terminal_id'] ?? null,
            'terminal' => $resumen['terminal'] ?? '01',
            'caja' => $resumen['caja'] ?? '01',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cantidad_ventas' => $resumen['cantidad_ventas'],
            'total_ventas' => $resumen['total_ventas'],
            'ticket_promedio' => $resumen['ticket_promedio'],
            'total_efectivo' => $resumen['total_efectivo'],
            'monto_efectivo_contado' => $montoEfectivoContado,
            'diferencia_efectivo' => $diferencia,
            'total_tarjeta' => $resumen['total_tarjeta'],
            'total_otros' => $resumen['total_otros'],
            'total_igv' => $resumen['total_igv'],
            'observaciones' => $observaciones,
        ]);
    }
}
