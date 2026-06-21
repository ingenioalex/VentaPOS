@extends('layouts.app')

@section('titulo', 'Reportes')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Reportes</h1>
    <div>
        <a href="{{ route('reportes.exportar', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn" style="background:#64748b;color:#fff">CSV</a>
        <a href="{{ route('reportes.exportar-excel', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn btn-primary" style="margin-left:.35rem">Excel</a>
    </div>
</div>

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
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

<div class="stats" style="margin-bottom:1.5rem">
    <div class="stat"><span>Tickets</span><strong>{{ $resumen->tickets }}</strong></div>
    <div class="stat"><span>Total vendido</span><strong>S/ {{ number_format($resumen->total, 2) }}</strong></div>
    <div class="stat"><span>Ticket promedio</span><strong>S/ {{ number_format($resumen->ticket_promedio, 2) }}</strong></div>
    <div class="stat"><span>IGV</span><strong>S/ {{ number_format($resumen->igv, 2) }}</strong></div>
    <div class="stat"><span>Descuentos</span><strong>S/ {{ number_format($resumen->descuentos, 2) }}</strong></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Por medio de pago</h3>
        <table class="data">
            <thead><tr><th>Medio</th><th>Tickets</th><th>Monto</th></tr></thead>
            <tbody>
                @forelse($porMedio as $m)
                <tr>
                    <td>{{ ucfirst($m->medio_pago) }}</td>
                    <td>{{ $m->tickets }}</td>
                    <td>S/ {{ number_format($m->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Top 10 productos</h3>
        <table class="data">
            <thead><tr><th>Producto</th><th>Unid.</th><th>Monto</th></tr></thead>
            <tbody>
                @forelse($topProductos as $p)
                <tr>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ number_format($p->unidades, 0) }}</td>
                    <td>S/ {{ number_format($p->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Ventas por cajero</h3>
        <table class="data">
            <thead><tr><th>Cajero</th><th>Tickets</th><th>Monto</th></tr></thead>
            <tbody>
                @forelse($porCajero as $c)
                <tr>
                    <td>{{ $c->cajero }}</td>
                    <td>{{ $c->tickets }}</td>
                    <td>S/ {{ number_format($c->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Ventas por día</h3>
        <table class="data">
            <thead><tr><th>Fecha</th><th>Tickets</th><th>Monto</th></tr></thead>
            <tbody>
                @forelse($porDia as $d)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($d->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $d->tickets }}</td>
                    <td>S/ {{ number_format($d->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
