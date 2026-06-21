@extends('layouts.app')

@section('titulo', 'Stock actual')

@section('contenido')
<h1 style="margin-bottom:1rem">Stock actual</h1>

@include('app.inventario._nav')

<div class="stats" style="margin-bottom:1rem">
    <div class="stat"><span>Unidades</span><strong>{{ number_format($totales['unidades'], 0) }}</strong></div>
    <div class="stat"><span>Valor a costo</span><strong>S/ {{ number_format($totales['valor_costo'], 2) }}</strong></div>
    <div class="stat"><span>Valor a venta</span><strong>S/ {{ number_format($totales['valor_venta'], 2) }}</strong></div>
</div>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, SKU o barra">
    </div>
    <div>
        <label>Alertas</label>
        <select name="stock_bajo">
            <option value="0" @selected(! $soloBajo)>Todos</option>
            <option value="1" @selected($soloBajo)>Solo stock bajo</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>SKU</th><th>Producto</th><th>Categoría</th><th>Stock</th><th>Mín.</th>
                <th>Costo</th><th>Valor costo</th><th>Precio</th><th>Valor venta</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i)
            <tr>
                <td style="font-family:monospace;font-size:.82rem">{{ $i->sku }}</td>
                <td>{{ $i->nombre }}</td>
                <td>{{ $i->categoria ?? '—' }}</td>
                <td style="color:{{ $i->bajo ? '#d97706' : 'inherit' }};font-weight:{{ $i->bajo ? '700' : '400' }}">{{ $i->stock }}</td>
                <td>{{ $i->stock_minimo > 0 ? $i->stock_minimo : '—' }}</td>
                <td>S/ {{ number_format($i->costo, 2) }}</td>
                <td>S/ {{ number_format($i->valor_costo, 2) }}</td>
                <td>S/ {{ number_format($i->precio, 2) }}</td>
                <td>S/ {{ number_format($i->valor_venta, 2) }}</td>
                <td><a href="{{ route('inventario.kardex', $i->id) }}">Kardex</a></td>
            </tr>
            @empty
            <tr><td colspan="10" style="color:#64748b">Sin productos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
