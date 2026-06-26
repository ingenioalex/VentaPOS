<?php

namespace App\Services;

use App\Models\CierreCaja;
use App\Models\Comprobante;
use App\Models\Empresa;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CierreCajaService
{
    public function __construct(
        private TerminalSesionService $terminalSesion,
    ) {}

    private function zonaHoraria(): string
    {
        return config('app.timezone', 'America/Lima');
    }

    private function ahora(): Carbon
    {
        return now($this->zonaHoraria());
    }

    private function inicioHoy(): Carbon
    {
        return $this->ahora()->copy()->startOfDay();
    }

    /** @return array{periodo_desde: Carbon, periodo_hasta: Carbon, fecha_desde: string, fecha_hasta: string, es_hoy: bool} */
    private function metadatosPeriodo(Carbon $desde): array
    {
        $tz = $this->zonaHoraria();
        $desde = $desde->copy()->timezone($tz);
        $hasta = $this->ahora();

        return [
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'fecha_desde' => $desde->format('d/m/Y H:i'),
            'fecha_hasta' => $hasta->format('d/m/Y H:i'),
            'es_hoy' => $desde->isSameDay($hasta),
        ];
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function normalizarPeriodo(Carbon $inicio, Carbon $fin): array
    {
        $tz = $this->zonaHoraria();
        $inicio = $inicio->copy()->timezone($tz);
        $fin = $fin->copy()->timezone($tz);

        if ($inicio->gt($fin)) {
            $inicio = $fin->copy()->startOfDay();
        }

        return [$inicio, $fin];
    }

    private function inicioJornadaEmpresa(int $empresaId): Carbon
    {
        return $this->inicioJornadaHoy($empresaId);
    }

    /** Ventas de hoy desde medianoche (no arrastra días anteriores). */
    private function inicioJornadaHoy(int $empresaId): Carbon
    {
        return $this->inicioHoy();
    }

    private function inicioJornadaAntesDe(int $empresaId, Carbon $momento, ?int $excluirCierreId = null): Carbon
    {
        $momento = $momento->copy()->timezone($this->zonaHoraria());

        $query = CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'Z')
            ->where('fecha_fin', '<', $momento);

        if ($excluirCierreId !== null) {
            $query->where('id', '!=', $excluirCierreId);
        }

        $ultimoZ = $query->orderByDesc('fecha_fin')->first();

        return $ultimoZ
            ? $ultimoZ->fecha_fin->copy()->timezone($this->zonaHoraria())
            : $momento->copy()->startOfDay();
    }

    private function inicioJornadaParaMomento(int $empresaId, Carbon $momento): Carbon
    {
        return $this->inicioJornadaAntesDe($empresaId, $momento);
    }

    private function inicioTurnoCajero(Usuario $usuario): Carbon
    {
        $ultimoX = CierreCaja::where('empresa_id', $usuario->empresa_id)
            ->where('usuario_id', $usuario->id)
            ->where('tipo_cierre', 'X')
            ->orderByDesc('fecha_fin')
            ->first();

        if ($ultimoX) {
            return $ultimoX->fecha_fin->copy()->timezone($this->zonaHoraria());
        }

        $inicioJornada = $this->inicioJornadaEmpresa($usuario->empresa_id);
        $sesionInicio = $usuario->sesion_inicio_en
            ? $usuario->sesion_inicio_en->copy()->timezone($this->zonaHoraria())
            : null;

        if ($sesionInicio && $sesionInicio->gt($inicioJornada)) {
            return $sesionInicio;
        }

        return $inicioJornada;
    }

    /** @param  Collection<int, CierreCaja>  $cierres */
    private function sumarTotalesCierres(Collection $cierres): array
    {
        $cantidad = (int) $cierres->sum('cantidad_ventas');
        $totalVentas = round((float) $cierres->sum('total_ventas'), 2);

        return [
            'cantidad_ventas' => $cantidad,
            'total_ventas' => $totalVentas,
            'ticket_promedio' => $cantidad > 0 ? round($totalVentas / $cantidad, 2) : 0.0,
            'total_igv' => round((float) $cierres->sum('total_igv'), 2),
            'total_efectivo' => round((float) $cierres->sum('total_efectivo'), 2),
            'total_tarjeta' => round((float) $cierres->sum('total_tarjeta'), 2),
            'total_otros' => round((float) $cierres->sum('total_otros'), 2),
        ];
    }

    private function diferenciaArqueo(float $contado, float $efectivoSistema): float
    {
        return round($contado - $efectivoSistema, 2);
    }

    public function resumenActual(Usuario $usuario): array
    {
        $empresaId = $usuario->empresa_id;
        $fechaDesde = $this->inicioTurnoCajero($usuario);
        $fechaHasta = $this->ahora();

        $ultimoCierre = CierreCaja::where('empresa_id', $empresaId)
            ->where('usuario_id', $usuario->id)
            ->where('tipo_cierre', 'X')
            ->orderByDesc('fecha_fin')
            ->first();

        $empresa = Empresa::find($empresaId);
        $terminal = $this->terminalSesion->datosActivos();

        return array_merge(
            $this->ventasEnPeriodo($empresaId, $usuario->id, $fechaDesde, $fechaHasta),
            $this->metadatosPeriodo($fechaDesde),
            [
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
        $fechaDesde = $this->inicioJornadaEmpresa($empresaId);
        $fechaHasta = $this->ahora();

        $ultimoZ = CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'Z')
            ->orderByDesc('fecha_fin')
            ->first();

        $empresa = Empresa::find($empresaId);

        $cierresX = CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'X')
            ->where('fecha_fin', '>', $fechaDesde)
            ->where('fecha_fin', '<=', $fechaHasta)
            ->with(['usuario:id,nombre,usuario', 'supervisor:id,nombre'])
            ->orderBy('fecha_fin')
            ->get();

        $cierresX = $this->deduplicarCierres($cierresX);

        $totalesX = $this->sumarTotalesCierres($cierresX);
        $totalesComprobantes = $this->ventasEnPeriodo($empresaId, null, $fechaDesde, $fechaHasta);

        $porCajero = $cierresX
            ->groupBy('usuario_id')
            ->map(fn ($grupo) => [
                'cajero' => $grupo->first()->usuario,
                'cantidad_cierres' => $grupo->count(),
                'cantidad_ventas' => $grupo->sum('cantidad_ventas'),
                'total_ventas' => round((float) $grupo->sum('total_ventas'), 2),
                'total_efectivo' => round((float) $grupo->sum('total_efectivo'), 2),
                'total_tarjeta' => round((float) $grupo->sum('total_tarjeta'), 2),
                'total_otros' => round((float) $grupo->sum('total_otros'), 2),
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

        $totalContado = round((float) $cierresX->sum('monto_efectivo_contado'), 2);
        $efectivoSistemaX = $totalesX['total_efectivo'];
        $diasPendientes = $this->cantidadDiasPendientes($empresaId);

        return array_merge(
            $totalesComprobantes,
            $this->metadatosPeriodo($fechaDesde),
            [
                'ultimo_cierre' => $ultimoZ,
                'terminal' => ($empresa?->configuracion ?? [])['terminal'] ?? '01',
                'caja' => ($empresa?->configuracion ?? [])['caja'] ?? '01',
                'empresa_id' => $empresaId,
                'empresa_nombre' => $empresa?->nombre ?? '',
                'cierres_x' => $cierresX,
                'por_cajero' => $porCajero,
                'cantidad_cajeros' => $porCajero->count(),
                'cantidad_cierres_x' => $cierresX->count(),
                'totales_x' => $totalesX,
                'totales_comprobantes' => $totalesComprobantes,
                'coincide_x_comprobantes' => abs($totalesX['total_ventas'] - $totalesComprobantes['total_ventas']) < 0.02
                    && $totalesX['cantidad_ventas'] === $totalesComprobantes['cantidad_ventas'],
                'efectivo_sistema_arqueo' => $efectivoSistemaX,
                'total_contado' => $totalContado,
                'total_diferencia' => $this->diferenciaArqueo($totalContado, $efectivoSistemaX),
                'cajeros_en_sesion' => $cajerosEnSesion,
                'cantidad_cajeros_en_sesion' => $cajerosEnSesion->count(),
                'dias_pendientes' => $diasPendientes,
                'puede_cerrar_z' => $diasPendientes === 0,
            ]
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, CierreCaja>
     */
    public function cierresXEnPeriodo(CierreCaja $cierreZ)
    {
        $cierres = CierreCaja::where('empresa_id', $cierreZ->empresa_id)
            ->where('tipo_cierre', 'X')
            ->where('fecha_fin', '>', $cierreZ->fecha_inicio)
            ->where('fecha_fin', '<=', $cierreZ->fecha_fin)
            ->with(['usuario:id,nombre,usuario', 'supervisor:id,nombre'])
            ->orderBy('fecha_fin')
            ->get();

        return $this->deduplicarCierres($cierres);
    }

    /**
     * @param  Collection<int, CierreCaja>  $cierres
     * @return Collection<int, CierreCaja>
     */
    public function deduplicarCierres(Collection $cierres): Collection
    {
        return $cierres
            ->groupBy(fn (CierreCaja $c) => $this->huellaCierre($c))
            ->map(fn (Collection $grupo) => $grupo->sortByDesc('id')->first())
            ->sortByDesc(fn (CierreCaja $c) => $c->id)
            ->values();
    }

    /**
     * Corrige fechas de cierre incoherentes (inicio posterior al fin o cierre en el futuro).
     *
     * @return array{reparados: int}
     */
    public function repararFechasInconsistentes(?int $empresaId = null): array
    {
        $tz = $this->zonaHoraria();
        $ahora = $this->ahora();
        $query = CierreCaja::query()->orderBy('id');
        if ($empresaId !== null) {
            $query->where('empresa_id', $empresaId);
        }

        $reparados = 0;
        foreach ($query->get() as $cierre) {
            $inicio = $cierre->fecha_inicio->copy()->timezone($tz);
            $fin = $cierre->fecha_fin->copy()->timezone($tz);
            $cambio = false;

            if ($fin->gt($ahora)) {
                $fin = $ahora->copy();
                $cambio = true;
            }

            if ($inicio->gt($fin)) {
                $inicio = $cierre->tipo_cierre === 'X'
                    ? $this->inicioEsperadoParaCierreX($cierre)
                    : $this->inicioEsperadoParaCierreZ($cierre);
                $cambio = true;
            }

            if ($cambio) {
                $cierre->update([
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                ]);
                $reparados++;
            }
        }

        return ['reparados' => $reparados];
    }

    /**
     * @return array{eliminados: int, conservados: int, grupos_duplicados: int}
     */
    public function eliminarDuplicadosEnBd(?int $empresaId = null): array
    {
        $query = CierreCaja::query()->orderBy('id');
        if ($empresaId !== null) {
            $query->where('empresa_id', $empresaId);
        }

        $totalInicial = (clone $query)->count();
        $gruposDuplicados = 0;
        $todos = $query->get();
        $eliminados = $this->purgarPorHuella($todos, $gruposDuplicados);

        return [
            'eliminados' => $eliminados,
            'conservados' => $totalInicial - $eliminados,
            'grupos_duplicados' => $gruposDuplicados,
        ];
    }

    private function purgarPorHuella(Collection $todos, int &$gruposDuplicados): int
    {
        $idsEliminar = [];

        foreach ($todos->groupBy(fn (CierreCaja $c) => $this->huellaCierre($c)) as $grupo) {
            if ($grupo->count() <= 1) {
                continue;
            }

            $gruposDuplicados++;
            $conservarId = $grupo->sortByDesc('id')->first()->id;
            foreach ($grupo as $cierre) {
                if ($cierre->id !== $conservarId) {
                    $idsEliminar[] = $cierre->id;
                }
            }
        }

        if ($idsEliminar !== []) {
            CierreCaja::whereIn('id', $idsEliminar)->delete();
        }

        return count($idsEliminar);
    }

    private function huellaCierre(CierreCaja $c): string
    {
        return implode(':', [
            $c->empresa_id,
            $c->usuario_id,
            $c->tipo_cierre,
            $c->fecha_inicio?->format('Y-m-d H:i:s') ?? '',
            $c->cantidad_ventas,
            number_format((float) $c->total_ventas, 2, '.', ''),
            number_format((float) $c->total_efectivo, 2, '.', ''),
            number_format((float) ($c->monto_efectivo_contado ?? 0), 2, '.', ''),
        ]);
    }

    /** Huella para limpiar duplicados exactos del mismo turno. */
    private function huellaCierreDuplicado(CierreCaja $c): string
    {
        return $this->huellaCierre($c);
    }

    /**
     * Recalcula períodos y totales de cada X/Z según comprobantes y suma de X.
     *
     * @return array{reparados: int}
     */
    public function repararPeriodosYTotales(?int $empresaId = null): array
    {
        $query = CierreCaja::query()->orderBy('fecha_fin');
        if ($empresaId !== null) {
            $query->where('empresa_id', $empresaId);
        }

        $reparados = 0;

        foreach ($query->clone()->where('tipo_cierre', 'X')->get() as $cierre) {
            $inicio = $this->inicioEsperadoParaCierreX($cierre);
            $fin = $cierre->fecha_fin->copy()->timezone($this->zonaHoraria());
            if ($inicio->gt($fin)) {
                continue;
            }

            $totales = $this->ventasEnPeriodo($cierre->empresa_id, $cierre->usuario_id, $inicio, $fin);
            $contado = (float) ($cierre->monto_efectivo_contado ?? 0);

            $cierre->update(array_merge(
                ['fecha_inicio' => $inicio],
                $this->camposTotales($totales),
                [
                    'diferencia_efectivo' => $this->diferenciaArqueo($contado, $totales['total_efectivo']),
                ],
            ));
            $reparados++;
        }

        foreach ($query->clone()->where('tipo_cierre', 'Z')->get() as $cierre) {
            $inicio = $this->inicioEsperadoParaCierreZ($cierre);
            $fin = $cierre->fecha_fin->copy()->timezone($this->zonaHoraria());
            $cierre->fecha_inicio = $inicio;
            $cierresX = $this->cierresXEnPeriodo($cierre);

            $totalesX = $this->sumarTotalesCierres($cierresX);
            $totalContado = round((float) $cierresX->sum('monto_efectivo_contado'), 2);

            $cierre->update(array_merge(
                ['fecha_inicio' => $inicio],
                $this->camposTotales($totalesX),
                [
                    'monto_efectivo_contado' => $totalContado,
                    'diferencia_efectivo' => $this->diferenciaArqueo($totalContado, $totalesX['total_efectivo']),
                ],
            ));
            $reparados++;
        }

        return ['reparados' => $reparados];
    }

    /** Sincroniza fechas y totales antes de mostrar o cerrar la jornada. */
    public function prepararModuloCierres(int $empresaId, bool $forzar = false): void
    {
        static $preparado = [];

        if (isset($preparado[$empresaId])) {
            return;
        }

        $preparado[$empresaId] = true;
        $cacheKey = "ventapos:cierres:sync:{$empresaId}";

        if (! $forzar && Cache::has($cacheKey)) {
            return;
        }

        $this->repararFechasInconsistentes($empresaId);
        $this->repararPeriodosYTotales($empresaId);
        Cache::put($cacheKey, true, now()->addMinutes(15));
    }

    private function inicioEsperadoParaCierreX(CierreCaja $cierre): Carbon
    {
        $tz = $this->zonaHoraria();
        $inicioJornada = $this->inicioJornadaParaMomento($cierre->empresa_id, $cierre->fecha_fin);

        $previoX = CierreCaja::where('empresa_id', $cierre->empresa_id)
            ->where('usuario_id', $cierre->usuario_id)
            ->where('tipo_cierre', 'X')
            ->where('id', '!=', $cierre->id)
            ->where('fecha_fin', '<', $cierre->fecha_fin)
            ->orderByDesc('fecha_fin')
            ->first();

        if ($previoX) {
            $desdeUltimoX = $previoX->fecha_fin->copy()->timezone($tz);
            if ($desdeUltimoX->gt($inicioJornada)) {
                return $desdeUltimoX;
            }
        }

        return $inicioJornada;
    }

    private function inicioEsperadoParaCierreZ(CierreCaja $cierre): Carbon
    {
        return $this->inicioJornadaAntesDe($cierre->empresa_id, $cierre->fecha_fin, $cierre->id);
    }

    /** @return array<string, float|int> */
    private function camposTotales(array $totales): array
    {
        return [
            'cantidad_ventas' => $totales['cantidad_ventas'],
            'total_ventas' => $totales['total_ventas'],
            'ticket_promedio' => $totales['ticket_promedio'],
            'total_efectivo' => $totales['total_efectivo'],
            'total_tarjeta' => $totales['total_tarjeta'],
            'total_otros' => $totales['total_otros'],
            'total_igv' => $totales['total_igv'],
        ];
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

        if ($resumen['cantidad_ventas'] === 0) {
            throw new RuntimeException('No hay ventas en este turno para registrar un Cierre X.');
        }

        $fechaInicio = $resumen['periodo_desde'];
        $fechaFin = $this->ahora();
        $resumen = array_merge(
            $resumen,
            $this->ventasEnPeriodo($usuario->empresa_id, $usuario->id, $fechaInicio, $fechaFin),
        );
        $this->assertNoCierreXDuplicado($usuario, $resumen, $fechaInicio);

        return $this->guardarCierre(
            $usuario,
            'X',
            $fechaInicio,
            $fechaFin,
            $resumen,
            $montoEfectivoContado,
            $observaciones,
            $supervisor,
        );
    }

    public function cerrarZ(Usuario $supervisor, ?string $observaciones = null): CierreCaja
    {
        $this->prepararModuloCierres($supervisor->empresa_id, true);

        $hoy = $this->inicioHoy()->toDateString();
        $this->assertSinDiasPendientesAnteriores($supervisor->empresa_id, $this->ahora());
        $this->assertDiaSinZCierre($supervisor->empresa_id, $hoy);

        $this->registrarXAutomaticosDelDia($supervisor, $hoy);

        $resumen = $this->resumenZCierre($supervisor->empresa_id);

        if ($resumen['cantidad_ventas'] === 0) {
            throw new RuntimeException('No hay ventas hoy para cerrar.');
        }

        $totales = $resumen['totales_comprobantes'];
        $paraGuardar = array_merge($resumen, $totales);
        $paraGuardar['total_diferencia'] = $resumen['total_diferencia'];

        return $this->guardarCierre(
            $supervisor,
            'Z',
            $resumen['periodo_desde'],
            $this->ahora(),
            $paraGuardar,
            (float) ($resumen['total_contado'] > 0 ? $resumen['total_contado'] : $totales['total_efectivo']),
            $observaciones,
            $supervisor,
        );
    }

    /** Registra Cierre X con efectivo del sistema para cada vendedor pendiente del día. */
    private function registrarXAutomaticosDelDia(Usuario $supervisor, string $fechaYmd): void
    {
        $empresaId = $supervisor->empresa_id;
        $dia = $this->validarFechaJornada($fechaYmd);
        $inicio = $dia->copy()->startOfDay();
        $fin = $fechaYmd === $this->inicioHoy()->toDateString()
            ? $this->ahora()
            : $dia->copy()->endOfDay();
        [$inicio, $fin] = $this->normalizarPeriodo($inicio, $fin);

        $procesados = [];

        for ($i = 0; $i < 20; $i++) {
            $pendientes = $this->cajerosPendientesX($empresaId, $inicio, $fin);
            if ($pendientes->isEmpty()) {
                break;
            }

            $item = $pendientes->first();
            $vendedorId = $item['cajero']->id;
            if (in_array($vendedorId, $procesados, true)) {
                break;
            }

            try {
                $this->cerrarXExcepcional(
                    $item['cajero'],
                    $supervisor,
                    $fechaYmd,
                    (float) $item['ventas']['total_efectivo'],
                    'Arqueo automático al cerrar el día',
                );
            } catch (RuntimeException $e) {
                if (str_contains($e->getMessage(), 'Ya existe un Cierre X')) {
                    $procesados[] = $vendedorId;

                    continue;
                }
                throw $e;
            }

            $procesados[] = $vendedorId;
        }
    }

    private function ventasEnPeriodo(
        int $empresaId,
        ?int $usuarioId,
        Carbon $fechaDesde,
        Carbon $fechaHasta,
    ): array {
        $desde = $fechaDesde->copy()->timezone($this->zonaHoraria());
        $hasta = $fechaHasta->copy()->timezone($this->zonaHoraria());

        $query = Comprobante::where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->where('emitido_en', '>=', $desde)
            ->where('emitido_en', '<=', $hasta);

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
        [$fechaInicio, $fechaFin] = $this->normalizarPeriodo($fechaInicio, $fechaFin);

        if ($tipo === 'Z') {
            $this->assertPeriodoZNoSolapa($usuario->empresa_id, $fechaInicio, $fechaFin);
        }

        $diferencia = $tipo === 'Z'
            ? round((float) ($resumen['total_diferencia'] ?? 0), 2)
            : round($montoEfectivoContado - $resumen['total_efectivo'], 2);

        $cierre = CierreCaja::create([
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

        Cache::forget("ventapos:cierres:sync:{$usuario->empresa_id}");

        return $cierre;
    }

    private function assertNoCierreXDuplicado(Usuario $usuario, array $resumen, Carbon $fechaInicio): void
    {
        $existe = CierreCaja::where('empresa_id', $usuario->empresa_id)
            ->where('usuario_id', $usuario->id)
            ->where('tipo_cierre', 'X')
            ->where('fecha_inicio', $fechaInicio)
            ->where('cantidad_ventas', $resumen['cantidad_ventas'])
            ->where('total_ventas', $resumen['total_ventas'])
            ->where('total_efectivo', $resumen['total_efectivo'])
            ->exists();

        if ($existe) {
            throw new RuntimeException(
                'Ya existe un Cierre X con los mismos datos para este turno. Use Reimprimir en el historial si necesita el ticket.'
            );
        }
    }

    private function validarFechaJornada(string $fechaYmd): Carbon
    {
        $tz = $this->zonaHoraria();
        try {
            $dia = Carbon::parse($fechaYmd, $tz)->startOfDay();
        } catch (\Throwable) {
            throw new RuntimeException('Fecha de jornada no válida.');
        }

        if ($dia->gt($this->ahora()->copy()->startOfDay())) {
            throw new RuntimeException('No puede cerrar una jornada futura.');
        }

        if ($dia->lt($this->ahora()->copy()->subDays(90)->startOfDay())) {
            throw new RuntimeException('La fecha supera el límite de 90 días para cierres excepcionales.');
        }

        return $dia;
    }

    public function diaTieneZCerrado(int $empresaId, string $fechaYmd): bool
    {
        return $this->diaCubiertoPorZ($empresaId, $fechaYmd);
    }

    /** True si algún Cierre Z cubre ese día calendario (sin depender solo de fecha_fin). */
    public function diaCubiertoPorZ(int $empresaId, string $fechaYmd): bool
    {
        $tz = $this->zonaHoraria();
        $inicio = Carbon::parse($fechaYmd, $tz)->startOfDay();
        $fin = Carbon::parse($fechaYmd, $tz)->endOfDay();

        return CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'Z')
            ->where('fecha_inicio', '<=', $fin)
            ->where('fecha_fin', '>=', $inicio)
            ->exists();
    }

    public function proximoDiaPendiente(int $empresaId): ?string
    {
        $pendientes = $this->jornadasPendientes($empresaId);

        return $pendientes[0]['fecha'] ?? null;
    }

    public function cantidadDiasPendientes(int $empresaId): int
    {
        return count($this->jornadasPendientes($empresaId));
    }

    private function assertPeriodoZNoSolapa(
        int $empresaId,
        Carbon $inicio,
        Carbon $fin,
        ?int $excluirId = null,
    ): void {
        $query = CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'Z')
            ->where('fecha_inicio', '<', $fin)
            ->where('fecha_fin', '>', $inicio);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        if ($query->exists()) {
            throw new RuntimeException(
                'Ese período se solapa con otro Cierre Z. Cierre los días pendientes en orden, uno por día.'
            );
        }
    }

    private function assertDiaSinZCierre(int $empresaId, string $fechaYmd): void
    {
        if ($this->diaCubiertoPorZ($empresaId, $fechaYmd)) {
            throw new RuntimeException('Ese día ya tiene Cierre Z registrado.');
        }
    }

    private function assertCierreEnOrden(int $empresaId, string $fechaYmd): void
    {
        $proximo = $this->proximoDiaPendiente($empresaId);

        if ($proximo !== null && $proximo !== $fechaYmd) {
            $etiqueta = Carbon::parse($proximo, $this->zonaHoraria())
                ->locale('es')
                ->isoFormat('D [de] MMMM [de] YYYY');

            throw new RuntimeException(
                "Primero cierre el día más antiguo pendiente: {$etiqueta}."
            );
        }
    }

    private function assertSinDiasPendientesAnteriores(int $empresaId, Carbon $momento): void
    {
        if ($this->cantidadDiasPendientes($empresaId) > 0) {
            throw new RuntimeException(
                'Hay días anteriores sin Cierre Z. Ciérrelos en la sección «Días sin cerrar» antes del Z de hoy.'
            );
        }
    }

    /** @return array<string, mixed> */
    private function metadatosPeriodoFijo(Carbon $desde, Carbon $hasta): array
    {
        $tz = $this->zonaHoraria();
        $desde = $desde->copy()->timezone($tz);
        $hasta = $hasta->copy()->timezone($tz);

        return [
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'fecha_desde' => $desde->format('d/m/Y H:i'),
            'fecha_hasta' => $hasta->format('d/m/Y H:i'),
            'es_hoy' => $desde->isSameDay($hasta) && $hasta->isToday(),
        ];
    }

    private function inicioTurnoCajeroEnMomento(Usuario $cajero, Carbon $momentoFin): Carbon
    {
        $tz = $this->zonaHoraria();
        $momentoFin = $momentoFin->copy()->timezone($tz);
        $inicioJornada = $this->inicioJornadaAntesDe(
            $cajero->empresa_id,
            $momentoFin->copy()->addSecond(),
        );

        $previoX = CierreCaja::where('empresa_id', $cajero->empresa_id)
            ->where('usuario_id', $cajero->id)
            ->where('tipo_cierre', 'X')
            ->where('fecha_fin', '<=', $momentoFin)
            ->orderByDesc('fecha_fin')
            ->first();

        if ($previoX) {
            $desdeX = $previoX->fecha_fin->copy()->timezone($tz);

            return $desdeX->gt($inicioJornada) ? $desdeX : $inicioJornada;
        }

        return $inicioJornada;
    }

    /**
     * Cajeros con ventas en el período sin Cierre X que las cubra.
     *
     * @return Collection<int, array{cajero: Usuario, ventas: array, totales_x: array, faltan_tickets: int}>
     */
    public function cajerosPendientesX(int $empresaId, Carbon $desde, Carbon $hasta): Collection
    {
        [$desde, $hasta] = $this->normalizarPeriodo($desde, $hasta);

        $usuarioIds = Comprobante::where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->where('emitido_en', '>=', $desde)
            ->where('emitido_en', '<=', $hasta)
            ->distinct()
            ->pluck('usuario_id');

        $pendientes = collect();

        foreach ($usuarioIds as $usuarioId) {
            $cajero = Usuario::find($usuarioId);
            if (! $cajero) {
                continue;
            }

            $ventas = $this->ventasEnPeriodo($empresaId, (int) $usuarioId, $desde, $hasta);
            if ($ventas['cantidad_ventas'] === 0) {
                continue;
            }

            $cierresX = CierreCaja::where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('tipo_cierre', 'X')
                ->where('fecha_fin', '>', $desde)
                ->where('fecha_fin', '<=', $hasta)
                ->get();

            $totalesX = $this->sumarTotalesCierres($this->deduplicarCierres($cierresX));

            $cubierto = $totalesX['cantidad_ventas'] >= $ventas['cantidad_ventas']
                && abs($totalesX['total_ventas'] - $ventas['total_ventas']) < 0.02;

            if (! $cubierto) {
                $pendientes->push([
                    'cajero' => $cajero,
                    'ventas' => $ventas,
                    'totales_x' => $totalesX,
                    'faltan_tickets' => max(0, $ventas['cantidad_ventas'] - $totalesX['cantidad_ventas']),
                ]);
            }
        }

        return $pendientes->sortBy(fn ($f) => $f['cajero']->nombre)->values();
    }

    /** @return array<string, mixed> */
    public function resumenJornadaPendiente(int $empresaId, string $fechaYmd): array
    {
        $dia = $this->validarFechaJornada($fechaYmd);
        $inicioDia = $dia->copy()->startOfDay();
        $finDia = $dia->copy()->endOfDay();
        [$inicioDia, $finDia] = $this->normalizarPeriodo($inicioDia, $finDia);

        $empresa = Empresa::find($empresaId);
        $terminal = $this->terminalSesion->datosActivos();

        $ventasDia = $this->ventasEnPeriodo($empresaId, null, $inicioDia, $finDia);

        $cierresX = CierreCaja::where('empresa_id', $empresaId)
            ->where('tipo_cierre', 'X')
            ->where('fecha_fin', '>=', $inicioDia)
            ->where('fecha_fin', '<=', $finDia)
            ->with(['usuario:id,nombre,usuario', 'supervisor:id,nombre'])
            ->orderBy('fecha_fin')
            ->get();

        $cierresX = $this->deduplicarCierres($cierresX);
        $totalesX = $this->sumarTotalesCierres($cierresX);
        $cajerosPendientes = $this->cajerosPendientesX($empresaId, $inicioDia, $finDia);
        $totalContado = round((float) $cierresX->sum('monto_efectivo_contado'), 2);
        $efectivoSistemaX = $totalesX['total_efectivo'];
        $cerrado = $this->diaCubiertoPorZ($empresaId, $fechaYmd);

        return array_merge(
            $ventasDia,
            $this->metadatosPeriodoFijo($inicioDia, $finDia),
            [
                'fecha' => $fechaYmd,
                'etiqueta' => ucfirst($dia->copy()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
                'cerrado' => $cerrado,
                'ventas_dia_calendario' => $ventasDia,
                'empresa_id' => $empresaId,
                'empresa_nombre' => $empresa?->nombre ?? '',
                'terminal' => $terminal['codigo'],
                'terminal_id' => $terminal['id'],
                'caja' => $terminal['caja'],
                'cierres_x' => $cierresX,
                'cantidad_cierres_x' => $cierresX->count(),
                'totales_x' => $totalesX,
                'totales_comprobantes' => $ventasDia,
                'coincide_x_comprobantes' => abs($totalesX['total_ventas'] - $ventasDia['total_ventas']) < 0.02
                    && $totalesX['cantidad_ventas'] === $ventasDia['cantidad_ventas'],
                'cajeros_pendientes' => $cajerosPendientes,
                'cantidad_cajeros_pendientes' => $cajerosPendientes->count(),
                'efectivo_sistema_arqueo' => $efectivoSistemaX,
                'total_contado' => $totalContado,
                'total_diferencia' => $this->diferenciaArqueo($totalContado, $efectivoSistemaX),
                'puede_cerrar_z' => $cajerosPendientes->isEmpty()
                    && $ventasDia['cantidad_ventas'] > 0
                    && ! $cerrado,
            ],
        );
    }

    /** @return list<array<string, mixed>> */
    public function jornadasPendientes(int $empresaId, int $maxDias = 90): array
    {
        $tz = $this->zonaHoraria();
        $hoy = $this->ahora()->copy()->startOfDay();
        $limite = $hoy->copy()->subDays($maxDias);

        $comprobantes = Comprobante::query()
            ->where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->where('emitido_en', '>=', $limite)
            ->where('emitido_en', '<', $hoy)
            ->orderBy('emitido_en')
            ->get(['emitido_en']);

        $dias = $comprobantes
            ->map(fn (Comprobante $c) => $c->emitido_en->copy()->timezone($tz)->toDateString())
            ->unique()
            ->sort()
            ->values();

        $pendientes = [];

        foreach ($dias as $dia) {
            if ($this->diaCubiertoPorZ($empresaId, $dia)) {
                continue;
            }

            try {
                $resumen = $this->resumenJornadaPendiente($empresaId, $dia);
            } catch (\Throwable) {
                continue;
            }

            if ($resumen['ventas_dia_calendario']['cantidad_ventas'] > 0) {
                $pendientes[] = $resumen;
            }
        }

        usort($pendientes, fn (array $a, array $b) => strcmp($a['fecha'], $b['fecha']));

        return $pendientes;
    }

    /** Cierra un día pasado: X automáticos + Z del día (00:00–23:59). */
    public function cerrarDiaPendiente(
        Usuario $supervisor,
        string $fechaYmd,
        ?string $observaciones = null,
    ): CierreCaja {
        $this->assertCierreEnOrden($supervisor->empresa_id, $fechaYmd);

        return $this->cerrarJornadaExcepcionalDirecta($supervisor, $fechaYmd, $observaciones);
    }

    /** @deprecated Use cerrarDiaPendiente */
    public function cerrarJornadaExcepcionalDirecta(
        Usuario $supervisor,
        string $fechaYmd,
        ?string $observaciones = null,
    ): CierreCaja {
        $empresaId = $supervisor->empresa_id;

        $this->registrarXAutomaticosDelDia($supervisor, $fechaYmd);

        $resumen = $this->resumenJornadaPendiente($empresaId, $fechaYmd);
        if (! $resumen['cajeros_pendientes']->isEmpty()) {
            throw new RuntimeException('No se pudieron registrar todos los Cierre X del día.');
        }

        $nota = trim(($observaciones ?? '').' Cierre directo excepcional.');

        return $this->cerrarZExcepcional($supervisor, $fechaYmd, $nota !== '' ? $nota : 'Cierre directo excepcional.');
    }

    public function cerrarXExcepcional(
        Usuario $cajero,
        Usuario $supervisor,
        string $fechaYmd,
        float $montoEfectivoContado,
        ?string $observaciones = null,
    ): CierreCaja {
        if ($cajero->empresa_id !== $supervisor->empresa_id) {
            throw new RuntimeException('Vendedor no pertenece a la empresa.');
        }

        if ($this->diaCubiertoPorZ($cajero->empresa_id, $fechaYmd)) {
            throw new RuntimeException('Ese día ya tiene Cierre Z registrado.');
        }

        if ($montoEfectivoContado < 0) {
            throw new RuntimeException('El monto contado no puede ser negativo.');
        }

        $dia = $this->validarFechaJornada($fechaYmd);
        $finDia = $dia->copy()->endOfDay();
        $fechaInicio = $this->inicioTurnoCajeroEnMomento($cajero, $finDia);
        $inicioCalendario = $dia->copy()->startOfDay();
        if ($fechaInicio->lt($inicioCalendario)) {
            $fechaInicio = $inicioCalendario;
        }
        [$fechaInicio, $finDia] = $this->normalizarPeriodo($fechaInicio, $finDia);

        $terminal = $this->terminalSesion->datosActivos();
        $resumen = array_merge(
            $this->ventasEnPeriodo($cajero->empresa_id, $cajero->id, $fechaInicio, $finDia),
            $this->metadatosPeriodoFijo($fechaInicio, $finDia),
            [
                'terminal' => $terminal['codigo'],
                'terminal_id' => $terminal['id'],
                'caja' => $terminal['caja'],
            ],
        );

        if ($resumen['cantidad_ventas'] === 0) {
            throw new RuntimeException('No hay ventas de este vendedor en el período del día indicado.');
        }

        $this->assertNoCierreXDuplicado($cajero, $resumen, $fechaInicio);

        $fechaClave = $dia->toDateString();
        $nota = '[Cierre X excepcional día '.$fechaClave.']';
        $obs = trim(($observaciones ?? '').' '.$nota);

        return $this->guardarCierre(
            $cajero,
            'X',
            $fechaInicio,
            $finDia,
            $resumen,
            $montoEfectivoContado,
            $obs !== '' ? $obs : $nota,
            $supervisor,
        );
    }

    public function cerrarZExcepcional(
        Usuario $supervisor,
        string $fechaYmd,
        ?string $observaciones = null,
    ): CierreCaja {
        $this->assertCierreEnOrden($supervisor->empresa_id, $fechaYmd);

        $resumen = $this->resumenJornadaPendiente($supervisor->empresa_id, $fechaYmd);

        if ($resumen['cerrado']) {
            throw new RuntimeException('Ese día ya tiene Cierre Z registrado.');
        }

        if ($resumen['totales_comprobantes']['cantidad_ventas'] === 0) {
            throw new RuntimeException('No hay ventas en ese día.');
        }

        if (! $resumen['puede_cerrar_z']) {
            $nombres = $resumen['cajeros_pendientes']->pluck('cajero.nombre')->join(', ');
            throw new RuntimeException(
                'Faltan Cierre X de: '.$nombres.'. Use «Cerrar este día» para registrarlos automáticamente.'
            );
        }

        if (! $resumen['coincide_x_comprobantes']) {
            $tx = $resumen['totales_x']['total_ventas'];
            $tc = $resumen['totales_comprobantes']['total_ventas'];
            throw new RuntimeException(
                "Los Cierre X (S/ {$tx}) no coinciden con las ventas del día (S/ {$tc})."
            );
        }

        $nota = '[Cierre Z día '.$fechaYmd.']';
        $obs = trim(($observaciones ?? '').' '.$nota);
        $paraGuardar = array_merge($resumen, $resumen['totales_x']);

        return $this->guardarCierre(
            $supervisor,
            'Z',
            $resumen['periodo_desde'],
            $resumen['periodo_hasta'],
            $paraGuardar,
            (float) $resumen['total_contado'],
            $obs !== '' ? $obs : $nota,
            $supervisor,
        );
    }
}
