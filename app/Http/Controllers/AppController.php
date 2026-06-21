<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    public function inicio()
    {
        $usuario = Auth::user();

        if (! $usuario->tieneModulo('inicio')) {
            return redirect($usuario->rutaInicio());
        }
        $empresaId = $usuario->empresa_id;

        $ventasHoy = Comprobante::where('empresa_id', $empresaId)
            ->whereDate('emitido_en', today())
            ->sum('total');

        $totalVentasHoy = Comprobante::where('empresa_id', $empresaId)
            ->whereDate('emitido_en', today())
            ->count();

        $stockBajo = Producto::stockBajo()->count();

        $productosStockBajo = Producto::with('categoria')
            ->stockBajo()
            ->orderBy('stock_disponible')
            ->limit(10)
            ->get(['id', 'nombre', 'sku', 'stock_disponible', 'stock_minimo', 'categoria_id']);

        return view('app.inicio', compact('usuario', 'ventasHoy', 'totalVentasHoy', 'stockBajo', 'productosStockBajo'));
    }
}
