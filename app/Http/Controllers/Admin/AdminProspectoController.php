<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospecto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProspectoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $estado = $request->get('estado');
        $origen = $request->get('origen');

        $prospectos = Prospecto::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre_negocio', 'like', "%{$q}%")
                        ->orWhere('nombre_contacto', 'like', "%{$q}%")
                        ->orWhere('telefono', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->when($origen, fn ($query) => $query->where('origen', $origen))
            ->orderByDesc('creado_en')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'nuevos' => Prospecto::where('estado', 'nuevo')->count(),
            'activos' => Prospecto::whereNotIn('estado', ['cliente', 'descartado'])->count(),
            'beta' => Prospecto::where('origen', 'beta')->count(),
        ];

        return view('admin.prospectos.index', compact(
            'prospectos', 'q', 'estado', 'origen', 'stats'
        ));
    }

    public function actualizarEstado(Request $request, Prospecto $prospecto)
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(Prospecto::estados())],
        ]);

        $prospecto->update(['estado' => $datos['estado']]);

        return back()->with('ok', 'Estado de «'.$prospecto->nombre_negocio.'» actualizado.');
    }
}
