<?php

namespace App\Services;

use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VentasKpiService
{
    public function __construct(private ReporteService $reportes) {}

    /** @return array<string, mixed> */
    public function panel(int $empresaId): array
    {
        $hoy = today()->toDateString();
        $inicioSemana = today()->startOfWeek()->toDateString();
        $inicioMes = today()->startOfMonth()->toDateString();

        $resumenHoy = $this->reportes->resumenVentas($empresaId, $hoy, $hoy);
        $resumenSemana = $this->reportes->resumenVentas($empresaId, $inicioSemana, $hoy);
        $resumenMes = $this->reportes->resumenVentas($empresaId, $inicioMes, $hoy);

        $ayer = today()->subDay()->toDateString();
        $resumenAyer = $this->reportes->resumenVentas($empresaId, $ayer, $ayer);

        $variacion = $this->variacionPct((float) $resumenAyer->total, (float) $resumenHoy->total);

        return [
            'hoy' => $this->formatearResumen($resumenHoy),
            'ayer' => $this->formatearResumen($resumenAyer),
            'semana' => $this->formatearResumen($resumenSemana),
            'mes' => $this->formatearResumen($resumenMes),
            'variacion_vs_ayer_pct' => $variacion,
            'medios_hoy' => $this->reportes->ventasPorMedio($empresaId, $hoy, $hoy),
            'cajeros_hoy' => $this->reportes->ventasPorCajero($empresaId, $hoy, $hoy),
            'top_hoy' => $this->reportes->topProductos($empresaId, $hoy, $hoy, 5),
            'por_dia_semana' => $this->reportes->ventasPorDia($empresaId, $inicioSemana, $hoy),
            'anuladas_mes' => Comprobante::where('empresa_id', $empresaId)
                ->where('estado', 'anulado')
                ->whereDate('anulado_en', '>=', $inicioMes)
                ->count(),
        ];
    }

    /** @return array{tickets: int, total: float, ticket_promedio: float, igv: float} */
    private function formatearResumen(object $r): array
    {
        return [
            'tickets' => (int) $r->tickets,
            'total' => round((float) $r->total, 2),
            'ticket_promedio' => round((float) $r->ticket_promedio, 2),
            'igv' => round((float) $r->igv, 2),
        ];
    }

    private function variacionPct(float $anterior, float $actual): ?float
    {
        if ($anterior <= 0) {
            return $actual > 0 ? 100.0 : null;
        }

        return round((($actual - $anterior) / $anterior) * 100, 1);
    }
}
