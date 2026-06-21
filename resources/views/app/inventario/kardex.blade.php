@extends('layouts.app')

@section('titulo', 'Kardex — '.$producto->nombre)

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <div>
        <h1 style="margin-bottom:.25rem">Kardex</h1>
        <p style="color:#64748b">{{ $producto->nombre }} · SKU {{ $producto->sku }} · Stock actual: <strong>{{ $producto->stock_disponible }}</strong></p>
    </div>
    <a href="{{ route('productos.editar', $producto) }}" class="btn" style="background:#64748b;color:#fff">Editar producto</a>
</div>

@include('app.inventario._nav')

<form class="filtros card" method="get">
    <div>
        <label>Desde</label>
        <input type="date" name="desde" value="{{ $desde }}">
    </div>
    <div>
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta }}">
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr><th>Fecha</th><th>Tipo</th><th>Cant.</th><th>Stock ant.</th><th>Stock nuevo</th><th>Usuario</th><th>Referencia</th></tr>
        </thead>
        <tbody>
            @forelse($movimientos as $m)
            <tr>
                <td>{{ $m->creado_en?->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst($m->tipo_movimiento) }}</td>
                <td>{{ $m->cantidad }}</td>
                <td>{{ $m->stock_anterior }}</td>
                <td>{{ $m->stock_nuevo }}</td>
                <td>{{ $m->usuario?->nombre ?? '—' }}</td>
                <td>{{ $m->referencia ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#64748b">Sin movimientos para este producto.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $movimientos->links() }}
</div>
@endsection
