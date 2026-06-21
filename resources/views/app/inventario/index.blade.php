@extends('layouts.app')

@section('titulo', 'Inventario')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Inventario</h1>
    <div>
        <a href="{{ route('inventario.exportar') }}" class="btn" style="background:#64748b;color:#fff;margin-right:.35rem">CSV stock</a>
        <a href="{{ route('inventario.exportar-excel') }}" class="btn" style="background:#0f766e;color:#fff;margin-right:.35rem">Excel stock</a>
        <a href="{{ route('inventario.exportar-movimientos', request()->only(['desde','hasta','tipo'])) }}" class="btn" style="background:#64748b;color:#fff;margin-right:.35rem">CSV mov.</a>
        <a href="{{ route('inventario.exportar-movimientos-excel', request()->only(['desde','hasta','tipo'])) }}" class="btn" style="background:#2563eb;color:#fff">Excel mov.</a>
    </div>
</div>

@include('app.inventario._nav')

<div class="stats" style="margin-bottom:1rem">
    <div class="stat"><span>Productos activos</span><strong>{{ $resumen['productos'] }}</strong></div>
    <div class="stat"><span>Unidades en stock</span><strong>{{ number_format($resumen['unidades'], 0) }}</strong></div>
    <div class="stat"><span>Valor a costo</span><strong>S/ {{ number_format($resumen['valor_costo'], 2) }}</strong></div>
    <div class="stat"><span>Valor a venta</span><strong>S/ {{ number_format($resumen['valor_venta'], 2) }}</strong></div>
    <div class="stat" style="border-left-color:{{ $resumen['stock_bajo'] > 0 ? '#d97706' : 'var(--primary)' }}">
        <span>Stock bajo</span>
        <strong style="color:{{ $resumen['stock_bajo'] > 0 ? '#d97706' : 'inherit' }}">{{ $resumen['stock_bajo'] }}</strong>
    </div>
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
    <div>
        <label>Tipo</label>
        <select name="tipo">
            <option value="todos" @selected($tipo === 'todos')>Todos</option>
            <option value="entrada" @selected($tipo === 'entrada')>Entradas</option>
            <option value="salida" @selected($tipo === 'salida')>Salidas</option>
            <option value="ajuste" @selected($tipo === 'ajuste')>Ajustes</option>
            <option value="venta" @selected($tipo === 'venta')>Ventas</option>
        </select>
    </div>
    <div>
        <label>Producto</label>
        <select name="producto_id">
            <option value="">Todos</option>
            @foreach($productos as $p)
            <option value="{{ $p->id }}" @selected($productoId == $p->id)>{{ $p->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1">
        <label>Buscar producto</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o SKU">
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cant.</th>
                <th>Stock ant.</th><th>Stock nuevo</th><th>Usuario</th><th>Referencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $m)
            <tr>
                <td>{{ $m->creado_en?->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="{{ route('inventario.kardex', $m->producto_id) }}">{{ $m->producto?->nombre ?? '—' }}</a>
                </td>
                <td>
                    @php
                        $color = match($m->tipo_movimiento) {
                            'entrada' => '#065f46',
                            'salida' => '#991b1b',
                            'ajuste' => '#92400e',
                            default => '#334155',
                        };
                    @endphp
                    <span style="color:{{ $color }};font-weight:600">{{ ucfirst($m->tipo_movimiento) }}</span>
                </td>
                <td>{{ $m->cantidad }}</td>
                <td>{{ $m->stock_anterior }}</td>
                <td>{{ $m->stock_nuevo }}</td>
                <td>{{ $m->usuario?->nombre ?? '—' }}</td>
                <td>{{ $m->referencia ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="color:#64748b">Sin movimientos en el período.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $movimientos->links() }}
</div>
@endsection
