@extends('layouts.app')

@section('titulo', 'Compras')

@section('contenido')
@include('app.compras._nav')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1 style="margin:0">Compras a proveedor</h1>
    @if(auth()->user()->puedeGestionar() && auth()->user()->tieneModulo('reportes'))
    <div>
        <a href="{{ route('reportes.compras') }}" class="btn" style="background:#e2e8f0;color:#334155">Reporte</a>
        <a href="{{ route('reportes.exportar-compras', request()->only(['desde','hasta','estado','tipo','proveedor_id'])) }}" class="btn" style="background:#64748b;color:#fff;margin-left:.35rem">CSV</a>
        <a href="{{ route('reportes.exportar-compras-excel', request()->only(['desde','hasta','estado','tipo','proveedor_id'])) }}" class="btn btn-primary" style="margin-left:.35rem">Excel</a>
    </div>
    @endif
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
        <label>Estado</label>
        <select name="estado">
            <option value="todos" @selected($estado === 'todos')>Todos</option>
            <option value="borrador" @selected($estado === 'borrador')>Borrador</option>
            <option value="recibido" @selected($estado === 'recibido')>Recibidas</option>
            <option value="anulado" @selected($estado === 'anulado')>Anuladas</option>
        </select>
    </div>
    <div>
        <label>Tipo doc.</label>
        <select name="tipo">
            <option value="todos" @selected($tipo === 'todos')>Todos</option>
            <option value="orden" @selected($tipo === 'orden')>Orden compra</option>
            <option value="guia" @selected($tipo === 'guia')>Guía</option>
            <option value="factura" @selected($tipo === 'factura')>Factura</option>
        </select>
    </div>
    <div>
        <label>Proveedor</label>
        <select name="proveedor_id">
            <option value="">Todos</option>
            @foreach($proveedores as $p)
            <option value="{{ $p->id }}" @selected($proveedorId == $p->id)>{{ $p->razon_social }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div class="stats" style="margin-bottom:1rem">
    <div class="stat"><span>Recibidas (período)</span><strong>{{ $totales->documentos }}</strong></div>
    <div class="stat"><span>Total comprado</span><strong>S/ {{ number_format($totales->monto, 2) }}</strong></div>
</div>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Documento</th><th>Fecha</th><th>Proveedor</th><th>Registró</th>
                <th>Total</th><th>Estado</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $c)
            <tr>
                <td>{{ $c->etiquetaDocumento() }}</td>
                <td>{{ $c->fecha_emision->format('d/m/Y') }}</td>
                <td>{{ $c->proveedor->razon_social }}</td>
                <td>{{ $c->usuario->nombre }}</td>
                <td>S/ {{ number_format($c->total, 2) }}</td>
                <td>
                    @if($c->estado === 'anulado')
                        <span style="color:#dc2626">Anulado</span>
                    @elseif($c->estado === 'borrador')
                        <span style="color:#92400e">Borrador</span>
                    @else
                        <span style="color:#065f46">Recibido</span>
                    @endif
                </td>
                <td><a href="{{ route('compras.detalle', $c) }}">Ver</a></td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#64748b">Sin compras en el período. <a href="{{ route('compras.crear') }}">Registrar una</a></td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $compras->links() }}
</div>
@endsection
