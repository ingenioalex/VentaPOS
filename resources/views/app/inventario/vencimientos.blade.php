@extends('layouts.app')

@section('titulo', 'Vencimientos')

@section('contenido')
<h1 style="margin-bottom:1rem">Lotes por vencer</h1>

@include('app.inventario._nav')

<form class="filtros card" method="get">
    <div>
        <label>Próximos días</label>
        <select name="dias">
            @foreach([7, 15, 30, 60, 90] as $d)
            <option value="{{ $d }}" @selected($dias == $d)>{{ $d }} días</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Lote</th>
                <th>Vence</th>
                <th>Cantidad</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $l)
            <tr>
                <td>{{ $l->producto?->nombre }}</td>
                <td>{{ $l->lote ?: '—' }}</td>
                <td>{{ $l->fecha_vencimiento?->format('d/m/Y') }}</td>
                <td>{{ $l->cantidad }}</td>
                <td>
                    @if($l->estaVencido())
                    <span style="color:#dc2626;font-weight:600">Vencido</span>
                    @else
                    <span style="color:#d97706">Por vencer</span>
                    @endif
                </td>
                <td><a href="{{ route('productos.editar', $l->producto_id) }}">Producto</a></td>
            </tr>
            @empty
            <tr><td colspan="6" style="color:#64748b">No hay lotes por vencer en el período</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
