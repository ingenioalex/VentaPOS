<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlejeController extends Controller
{
    public function index(Request $request)
    {
        $empresa = Auth::user()->empresa;
        abort_unless(app(\App\Services\ExtrasEmpresaService::class)->activo($empresa, 'flejes'), 404);

        $q = trim($request->get('q', ''));

        $productos = Producto::with('categoria:id,nombre')
            ->where('activo', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('codigo_barra', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(30)
            ->withQueryString();

        return view('app.flejes.index', compact('productos', 'q', 'empresa'));
    }

    public function imprimir(Request $request)
    {
        $empresa = Auth::user()->empresa;
        abort_unless(app(\App\Services\ExtrasEmpresaService::class)->activo($empresa, 'flejes'), 404);

        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if ($ids === []) {
            return back()->withErrors(['ids' => 'Seleccione al menos un producto.']);
        }

        $productos = Producto::whereIn('id', $ids)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $cantidad = max(1, min(50, (int) $request->input('cantidad', 1)));

        return view('app.flejes.imprimir', compact('productos', 'empresa', 'cantidad'));
    }
}
