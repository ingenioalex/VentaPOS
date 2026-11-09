@extends('layouts.app')

@section('titulo', 'Reporte cierres')

@section('contenido')
<h1 style="margin:0">Cierres de caja</h1>
<p class="dyn-lead" style="margin-bottom:1rem">Cierres X y Z del periodo. La diferencia es el valor absoluto, no el faltante neto.</p>

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
        <label>Tipo</label>
        <select name="tipo">
            <option value="todos" @selected($tipo === 'todos')>Todos</option>
            <option value="X" @selected($tipo === 'X')>Cierre X</option>
            <option value="Z" @selected($tipo === 'Z')>Cierre Z</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

<div class="stats" style="margin-bottom:1.5rem">
    <div class="stat"><span>Cierres</span><strong>{{ $resumen->cierres }}</strong></div>
    <div class="stat"><span>Total vendido</span><strong>S/ {{ number_format($resumen->total_ventas, 2) }}</strong></div>
    <div class="stat"><span>Tickets</span><strong>{{ $resumen->tickets }}</strong></div>
    <div class="stat"><span>Efectivo</span><strong>S/ {{ number_format($resumen->efectivo, 2) }}</strong></div>
    <div class="stat"><span>Diferencias (abs.)</span><strong>S/ {{ number_format($resumen->diferencias, 2) }}</strong></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Por cajero</h3>
        <table class="data">
            <thead><tr><th>Cajero</th><th>Cierres</th><th>Monto</th></tr></thead>
            <tbody>
                @forelse($porCajero as $c)
                <tr>
                    <td>{{ $c->cajero }}</td>
                    <td>{{ $c->cierres }}</td>
                    <td>S/ {{ number_format($c->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom:1rem">Detalle de cierres (últimos 100)</h3>
    <table class="data">
        <thead>
            <tr><th>Fecha</th><th>Tipo</th><th>Cajero</th><th>Terminal</th><th>Tickets</th><th>Total</th><th>Dif. efectivo</th></tr>
        </thead>
        <tbody>
            @forelse($listado as $c)
            <tr>
                <td>{{ $c->fecha_fin->format('d/m/Y H:i') }}</td>
                <td>{{ $c->tipo_cierre }}</td>
                <td>{{ $c->usuario?->nombre ?? '—' }}</td>
                <td>{{ $c->terminal ?? '—' }}</td>
                <td>{{ $c->cantidad_ventas }}</td>
                <td>S/ {{ number_format($c->total_ventas, 2) }}</td>
                <td style="color:{{ abs((float)$c->diferencia_efectivo) > 0.01 ? '#dc2626' : 'inherit' }}">
                    S/ {{ number_format($c->diferencia_efectivo ?? 0, 2) }}
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#64748b">Sin cierres en el período</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
