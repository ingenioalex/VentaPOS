@extends('layouts.app')

@section('titulo', 'Productos')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Productos</h1>
    <div>
        @if(auth()->user()->tieneModulo('categorias'))
        <a href="{{ route('categorias.index') }}" class="btn" style="background:#64748b;color:#fff;margin-right:.35rem">Categorías</a>
        @endif
        <a href="{{ route('productos.importar') }}" class="btn" style="background:#2563eb;color:#fff;margin-right:.35rem">Importar CSV</a>
        <a href="{{ route('productos.crear') }}" class="btn btn-primary">+ Nuevo producto</a>
    </div>
</div>

@if($stockBajoCount > 0)
<div class="alert-warning" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
    <span><strong>{{ $stockBajoCount }}</strong> producto(s) con stock bajo o agotado.</span>
    <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="btn btn-warning" style="font-size:.85rem">Ver alertas</a>
</div>
@endif

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar (nombre, barra, SKU)</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar..." style="width:100%;max-width:360px">
    </div>
    <div>
        <label>Alertas</label>
        <select name="stock_bajo">
            <option value="0" @selected(! $soloStockBajo)>Todos</option>
            <option value="1" @selected($soloStockBajo)>Solo stock bajo</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Cód. barra</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>U.M.</th>
                <th>Costo</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Mín.</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $p)
            <tr>
                <td style="font-family:monospace;font-size:.82rem">{{ $p->sku }}</td>
                <td style="font-family:monospace;font-size:.82rem">{{ $p->codigo_barra ?? '—' }}</td>
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                <td>{{ $p->unidad_medida }}</td>
                <td>S/ {{ number_format($p->costo_compra, 2) }}</td>
                <td>S/ {{ number_format($p->precio_venta, 2) }}</td>
                <td style="color:{{ $p->stock_disponible < 0 ? '#dc2626' : ($p->tieneStockBajo() ? '#d97706' : 'inherit') }};font-weight:{{ $p->tieneStockBajo() ? '700' : '400' }}">
                    {{ $p->stock_disponible }}
                    @if($p->tieneStockBajo())
                    <span title="Stock bajo" style="font-size:.72rem">⚠</span>
                    @endif
                </td>
                <td>{{ $p->stock_minimo > 0 ? $p->stock_minimo : '—' }}</td>
                <td>{{ $p->activo ? 'Activo' : 'Inactivo' }}</td>
                <td>
                    <a href="{{ route('productos.editar', $p) }}">Editar</a>
                    @if(auth()->user()->tieneModulo('inventario'))
                    · <a href="{{ route('inventario.kardex', $p) }}">Kardex</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="11" style="padding:1rem;color:#64748b">Sin productos.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $productos->links() }}
</div>
@endsection
