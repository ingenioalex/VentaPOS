<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Services\AnulacionService;
use App\Services\ExtrasEmpresaService;
use App\Services\LoteService;
use App\Services\VentasKpiService;
use App\Services\WhatsappResumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    public function __construct(
        private AnulacionService $anulacionService,
        private VentasKpiService $kpi,
        private WhatsappResumenService $whatsapp,
        private ExtrasEmpresaService $extras,
        private LoteService $lotes,
    ) {}

    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $empresa = Auth::user()->empresa;
        $desde = $request->get('desde', today()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $estado = $request->get('estado', 'emitido');

        $ventas = Comprobante::where('empresa_id', $empresaId)
            ->with(['usuario:id,nombre', 'cliente:id,nombre,numero_documento'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->whereDate('emitido_en', '>=', $desde)
            ->whereDate('emitido_en', '<=', $hasta)
            ->orderByDesc('emitido_en')
            ->paginate(25)
            ->withQueryString();

        $totales = Comprobante::where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->whereDate('emitido_en', '>=', $desde)
            ->whereDate('emitido_en', '<=', $hasta)
            ->selectRaw('COUNT(*) as tickets, COALESCE(SUM(total),0) as monto')
            ->first();

        $kpi = $this->kpi->panel($empresaId);
        $whatsappActivo = $empresa && $this->extras->activo($empresa, 'whatsapp_resumen');
        $urlWhatsappHoy = $whatsappActivo && $empresa
            ? $this->whatsapp->urlEnviar($empresa, today())
            : null;
        $lotesActivo = $empresa && $this->extras->activo($empresa, 'lotes_vencimiento');
        $vencimientos = $lotesActivo
            ? $this->lotes->proximosVencer($empresaId, 30, 8)
            : collect();

        return view('app.ventas.index', compact(
            'ventas', 'desde', 'hasta', 'estado', 'totales', 'kpi',
            'whatsappActivo', 'urlWhatsappHoy', 'lotesActivo', 'vencimientos'
        ));
    }

    public function detalle(Comprobante $comprobante)
    {
        $this->autorizarComprobante($comprobante);
        $comprobante->load(['detalles.producto', 'usuario', 'cliente', 'anuladoPor']);

        return view('app.ventas.detalle', compact('comprobante'));
    }

    public function anular(Request $request, Comprobante $comprobante)
    {
        $this->autorizarComprobante($comprobante);

        $datos = $request->validate([
            'supervisor_usuario' => 'required|string|max:60',
            'supervisor_password' => 'required|string',
            'motivo' => 'required|string|max:255',
        ]);

        $supervisor = $this->anulacionService->validarSupervisor(
            Auth::user()->empresa_id,
            $datos['supervisor_usuario'],
            $datos['supervisor_password']
        );

        if (! $supervisor) {
            return back()->withErrors(['supervisor_usuario' => 'Credenciales de supervisor inválidas.']);
        }

        try {
            $this->anulacionService->anular($comprobante, $supervisor, $datos['motivo']);

            return redirect()->route('ventas.detalle', $comprobante)
                ->with('ok', 'Comprobante anulado. Stock restaurado.');
        } catch (\Throwable $e) {
            return back()->withErrors(['motivo' => $e->getMessage()]);
        }
    }

    private function autorizarComprobante(Comprobante $comprobante): void
    {
        if ($comprobante->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }
    }
}