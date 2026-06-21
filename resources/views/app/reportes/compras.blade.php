@extends('layouts.app')

@section('titulo', 'Reporte compras')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Reporte de compras</h1>
    <div>
        <a href="{{ route('reportes.exportar-compras', request()->only(['desde','hasta','estado'])) }}" class="btn" style="background:#64748b;color:#fff">CSV</a>
        <a href="{{ route('reportes.exportar-compras-excel', request()->only(['desde','hasta','estado'])) }}" class="btn btn-primary" style="margin-left:.35rem">Excel</a>
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
    <div>
        <label>Estado</label>
        <select name="estado">
            <option value="recibido" @selected($estado === 'recibido')>Recibidas</option>
            <option value="borrador" @selected($estado === 'borrador')>Borrador</option>
            <option value="anulado" @selected($estado === 'anulado')>Anuladas</option>
            <option value="todos" @selected($estado === 'todos')>Todas</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

<div class="stats" style="margin-bottom:1.5rem">
    <div class="stat"><span>Documentos</span><strong>{{ $resumen->documentos }}</strong></div>
    <div class="stat"><span>Total comprado</span><strong>S/ {{ number_format($resumen->total, 2) }}</strong></div>
    <div class="stat"><span>Subtotal</span><strong>S/ {{ number_format($resumen->subtotal, 2) }}</strong></div>
    <div class="stat"><span>IGV</span><strong>S/ {{ number_format($resumen->igv, 2) }}</strong></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Por proveedor</h3>
        <table class="data">
            <thead><tr><th>Proveedor</th><th>Docs</th><th>Monto</th></tr></thead>
            <tbody>
                @forelse($porProveedor as $p)
                <tr>
                    <td>{{ $p->proveedor }}</td>
                    <td>{{ $p->documentos }}</td>
                    <td>S/ {{ number_format($p->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Por tipo de documento</h3>
        <table class="data">
            <thead><tr><th>Tipo</th><th>Docs</th><th>Monto</th></tr></thead>
            <tbody>
                @forelse($porTipo as $t)
                <tr>
                    <td>{{ ucfirst($t->tipo_documento) }}</td>
                    <td>{{ $t->documentos }}</td>
                    <td>S/ {{ number_format($t->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#64748b">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom:1rem">Top productos comprados</h3>
    <table class="data">
        <thead><tr><th>Producto</th><th>SKU</th><th>Unidades</th><th>Monto</th></tr></thead>
        <tbody>
            @forelse($topProductos as $p)
            <tr>
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->sku ?? '—' }}</td>
                <td>{{ $p->unidades }}</td>
                <td>S/ {{ number_format($p->monto, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="color:#64748b">Sin datos en el período</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
