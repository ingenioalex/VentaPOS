<?php

namespace App\Services;

use App\Models\Comprobante;
use App\Models\Empresa;
use Carbon\Carbon;

class WhatsappResumenService
{
    public function __construct(
        private ExtrasEmpresaService $extras,
        private ReporteService $reportes,
    ) {}

    public function habilitado(Empresa $empresa): bool
    {
        return $this->extras->activo($empresa, 'whatsapp_resumen');
    }

    /** @return array<string, mixed> */
    public function config(Empresa $empresa): array
    {
        $cfg = ($empresa->configuracion ?? [])['whatsapp_resumen'] ?? [];

        return array_merge([
            'telefono' => preg_replace('/\D/', '', (string) ($empresa->telefono ?? '')),
            'hora_envio' => '20:00',
        ], is_array($cfg) ? $cfg : []);
    }

    public function generarTexto(Empresa $empresa, ?Carbon $fecha = null): string
    {
        $fecha = ($fecha ?? today())->copy();
        $desde = $fecha->toDateString();
        $resumen = $this->reportes->resumenVentas($empresa->id, $desde, $desde);
        $medios = $this->reportes->ventasPorMedio($empresa->id, $desde, $desde);
        $top = $this->reportes->topProductos($empresa->id, $desde, $desde, 5);

        $lineas = [
            "📊 *Resumen VentaPOS*",
            "*{$empresa->nombre}*",
            "Fecha: {$fecha->format('d/m/Y')}",
            '',
            "Tickets: {$resumen->tickets}",
            'Total: S/ '.number_format((float) $resumen->total, 2),
            'Ticket prom.: S/ '.number_format((float) $resumen->ticket_promedio, 2),
        ];

        if ($medios->isNotEmpty()) {
            $lineas[] = '';
            $lineas[] = '*Por medio de pago:*';
            foreach ($medios as $m) {
                $lineas[] = '• '.ucfirst($m->medio_pago).': S/ '.number_format((float) $m->monto, 2);
            }
        }

        if ($top->isNotEmpty()) {
            $lineas[] = '';
            $lineas[] = '*Top productos:*';
            foreach ($top as $i => $p) {
                $lineas[] = ($i + 1).". {$p->nombre} ({$p->unidades} u.)";
            }
        }

        $anuladas = Comprobante::where('empresa_id', $empresa->id)
            ->where('estado', 'anulado')
            ->whereDate('anulado_en', $desde)
            ->count();

        if ($anuladas > 0) {
            $lineas[] = '';
            $lineas[] = "Anulaciones: {$anuladas}";
        }

        return implode("\n", $lineas);
    }

    public function urlEnviar(Empresa $empresa, ?Carbon $fecha = null): string
    {
        $cfg = $this->config($empresa);
        $telefono = preg_replace('/\D/', '', (string) ($cfg['telefono'] ?? ''));
        if ($telefono === '') {
            return '#';
        }

        if (strlen($telefono) === 9) {
            $telefono = '51'.$telefono;
        }

        $texto = rawurlencode($this->generarTexto($empresa, $fecha));

        return "https://wa.me/{$telefono}?text={$texto}";
    }
}
