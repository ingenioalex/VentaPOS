<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKMovimiento;
use App\Support\SeccionKEsquema;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMovimientoController extends Controller
{
    public function index()
    {
        SeccionKEsquema::asegurarMovimientos();

        return view('seccionk.admin.fondo', array_merge(
            SeccionKMovimiento::resumenFondo(),
            ['volver' => 'fondo']
        ));
    }

    public function store(Request $request)
    {
        SeccionKEsquema::asegurarMovimientos();

        $data = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(SeccionKMovimiento::tipos()))],
            'persona' => 'nullable|string|max:150',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'comentario' => 'nullable|string|max:1000',
            'prestamo_id' => 'nullable|integer',
            'volver' => 'nullable|in:fondo,detalle,declaracion,dashboard',
            'evento' => 'nullable|integer',
        ]);

        $tipo = $data['tipo'];
        $persona = trim((string) ($data['persona'] ?? ''));

        if (in_array($tipo, [SeccionKMovimiento::TIPO_PRESTAMO, SeccionKMovimiento::TIPO_DEVOLUCION], true) && $persona === '') {
            return back()->withInput()->withErrors([
                'persona' => 'Indica a quién se prestó o quién devolvió el dinero.',
            ]);
        }

        $prestamoId = null;
        if ($tipo === SeccionKMovimiento::TIPO_DEVOLUCION) {
            $prestamo = SeccionKMovimiento::query()
                ->where('tipo', SeccionKMovimiento::TIPO_PRESTAMO)
                ->with('devoluciones')
                ->find($data['prestamo_id'] ?? null);

            if (! $prestamo || ! $prestamo->estaPendiente()) {
                return back()->withInput()->withErrors([
                    'prestamo_id' => 'Elige el préstamo pendiente que se está devolviendo.',
                ]);
            }

            if ((float) $data['monto'] - $prestamo->saldoPendiente() > 0.009) {
                return back()->withInput()->withErrors([
                    'monto' => 'La devolución no puede ser mayor al saldo pendiente (S/ '.number_format($prestamo->saldoPendiente(), 2).').',
                ]);
            }

            $prestamoId = $prestamo->id;
            if ($persona === '') {
                $persona = (string) $prestamo->persona;
            }
        }

        SeccionKMovimiento::create([
            'tipo' => $tipo,
            'persona' => $persona !== '' ? $persona : null,
            'monto' => $data['monto'],
            'fecha' => $data['fecha'],
            'comentario' => $data['comentario'] ?? null,
            'prestamo_id' => $prestamoId,
        ]);

        $mensaje = match ($tipo) {
            SeccionKMovimiento::TIPO_PRESTAMO => 'Préstamo registrado. Se verá en el dashboard hasta que lo devuelvan.',
            SeccionKMovimiento::TIPO_DEVOLUCION => 'Devolución registrada. Si ya no hay saldo, el préstamo deja de mostrarse en el dashboard.',
            default => 'Movimiento registrado en el fondo.',
        };

        return redirect($this->destino($request))->with('success', $mensaje);
    }

    private function destino(Request $request): string
    {
        return match ($request->input('volver')) {
            'detalle' => $request->filled('evento') && SeccionKEvento::find($request->integer('evento'))
                ? route('seccionk.admin.eventos.detalle', $request->integer('evento'))
                : route('seccionk.admin.fondo'),
            'declaracion' => route('seccionk.admin.declaracion'),
            'dashboard' => route('seccionk.admin.dashboard'),
            default => route('seccionk.admin.fondo'),
        };
    }
}
