@extends('layouts.app')

@section('titulo', 'Reporte productos')

@section('contenido')
<h1 style="margin-bottom:1rem">Productos más vendidos</h1>

@include('app.reportes._nav')

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
        <label>Top</label>
        <select name="limite">
            @foreach([10, 25, 50] as $n)
            <option value="{{ $n }}" @selected($limite === $n)>{{ $n }} productos</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

<div class="stats" style="margin-bottom:1.5rem">
    <div class="stat"><span>Tickets (período)</span><strong>{{ $resumen->tickets }}</strong></div>
    <div class="stat"><span>Total vendido</span><strong>S/ {{ number_format($resumen->total, 2) }}</strong></div>
    <div class="stat"><span>Ticket promedio</span><strong>S/ {{ number_format($resumen->ticket_promedio, 2) }}</strong></div>
</div>

<div class="card">
    <h3 style="margin-bottom:1rem">Ranking por unidades vendidas</h3>
    <table class="data">
        <thead>
            <tr><th>#</th><th>Producto</th><th>SKU</th><th>Unidades</th><th>Monto</th><th>% del total</th></tr>
        </thead>
        <tbody>
            @php $totalMonto = max(0.01, (float) $topProductos->sum('monto')); @endphp
            @forelse($topProductos as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->nombre }}</td>
                <td style="font-family:monospace;font-size:.85rem">{{ $p->sku }}</td>
                <td>{{ number_format($p->unidades, 2) }}</td>
                <td>S/ {{ number_format($p->monto, 2) }}</td>
                <td>{{ number_format(($p->monto / $totalMonto) * 100, 1) }}%</td>
            </tr>
            @empty
            <tr><td colspan="6" style="color:#64748b">Sin ventas en el período</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
