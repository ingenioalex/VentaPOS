@extends('layouts.app')

@section('titulo', 'Reporte de inventario')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Reportes</h1>
    <div>
        <a href="{{ route('reportes.exportar-stock') }}" class="btn" style="background:#64748b;color:#fff">Stock CSV</a>
        <a href="{{ route('reportes.exportar-stock-excel') }}" class="btn" style="background:#0f766e;color:#fff;margin-left:.35rem">Stock Excel</a>
        <a href="{{ route('inventario.exportar-movimientos', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn" style="background:#64748b;color:#fff;margin-left:.35rem">Mov. CSV</a>
        <a href="{{ route('inventario.exportar-movimientos-excel', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn" style="background:#2563eb;color:#fff;margin-left:.35rem">Mov. Excel</a>
    </div>
</div>

@include('app.reportes._nav')

<div class="stats" style="margin-bottom:1rem">
    <div class="stat"><span>Productos activos</span><strong>{{ $resumen['productos'] }}</strong></div>
    <div class="stat"><span>Unidades en stock</span><strong>{{ number_format($resumen['unidades'], 0) }}</strong></div>
    <div class="stat"><span>Valor a costo</span><strong>S/ {{ number_format($resumen['valor_costo'], 2) }}</strong></div>
    <div class="stat"><span>Valor a venta</span><strong>S/ {{ number_format($resumen['valor_venta'], 2) }}</strong></div>
    <div class="stat" style="border-left-color:{{ $resumen['stock_bajo'] > 0 ? '#d97706' : 'var(--primary)' }}">
        <span>Stock bajo</span>
        <strong style="color:{{ $resumen['stock_bajo'] > 0 ? '#d97706' : 'inherit' }}">{{ $resumen['stock_bajo'] }}</strong>
    </div>
    <div class="stat"><span>Sin stock</span><strong>{{ $resumen['sin_stock'] }}</strong></div>
</div>

<div class="stats" style="margin-bottom:1.5rem">
    <div class="stat"><span>Entradas (mes)</span><strong>{{ number_format($resumen['entradas_mes'], 0) }}</strong></div>
    <div class="stat"><span>Salidas (mes)</span><strong>{{ number_format($resumen['salidas_mes'], 0) }}</strong></div>
    <div class="stat"><span>Ventas stock (mes)</span><strong>{{ number_format($resumen['ventas_mes'], 0) }}</strong></div>
    <div class="stat"><span>Ajustes (mes)</span><strong>{{ $resumen['ajustes_mes'] }}</strong></div>
</div>

<form class="filtros card" method="get">
    <div>
        <label>Desde</label>
        <input type="date" name="desde" value="{{ $desde }}">
    </div>
    <div>
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta }}">
    </div>
    <div style="flex:1">
        <label>Buscar producto</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, SKU o barra">
    </div>
    <div>
        <label>Alertas</label>
        <select name="stock_bajo">
            <option value="0" @selected(! request()->boolean('stock_bajo'))>Todos</option>
            <option value="1" @selected(request()->boolean('stock_bajo'))>Solo stock bajo</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div style="display:grid;grid-template-columns:280px 1fr;gap:1rem;align-items:start">
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Movimientos por tipo</h3>
        <table class="data">
            <thead><tr><th>Tipo</th><th>Movs.</th><th>Unid.</th></tr></thead>
            <tbody>
                @forelse($porTipo as $t)
                <tr>
                    <td>{{ ucfirst($t->tipo_movimiento) }}</td>
                    <td>{{ $t->movimientos }}</td>
                    <td>{{ number_format($t->unidades, 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin movimientos</td></tr>
                @endforelse
            </tbody>
        </table>
        <p style="margin-top:.75rem;font-size:.85rem;color:#64748b">
            Total valorizado: S/ {{ number_format($totalesStock['valor_costo'], 2) }} (costo) ·
            S/ {{ number_format($totalesStock['valor_venta'], 2) }} (venta)
        </p>
    </div>

    <div class="card" style="margin:0;overflow:auto">
        <h3 style="margin-bottom:1rem">Stock valorizado</h3>
        <table class="data">
            <thead>
                <tr>
                    <th>SKU</th><th>Producto</th><th>Stock</th><th>Valor costo</th><th>Valor venta</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stock as $i)
                <tr>
                    <td style="font-family:monospace;font-size:.82rem">{{ $i->sku }}</td>
                    <td>
                        <a href="{{ route('inventario.kardex', $i->id) }}">{{ $i->nombre }}</a>
                        @if($i->bajo)<span style="color:#d97706;font-size:.75rem;margin-left:.25rem">bajo</span>@endif
                    </td>
                    <td>{{ $i->stock }}</td>
                    <td>S/ {{ number_format($i->valor_costo, 2) }}</td>
                    <td>S/ {{ number_format($i->valor_venta, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="color:#64748b">Sin productos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
