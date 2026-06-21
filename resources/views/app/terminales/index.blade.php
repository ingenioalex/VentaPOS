@extends('layouts.app')

@section('titulo', 'Terminales')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Terminales / cajas</h1>
    <div>
        @if(auth()->user()->tieneModulo('usuario_terminales'))
        <a href="{{ route('usuario-terminales.index') }}" class="btn" style="background:#2563eb;color:#fff;margin-right:.35rem">Asignar a usuarios</a>
        @endif
        <a href="{{ route('terminales.crear') }}" class="btn btn-primary">+ Nueva terminal</a>
    </div>
</div>

<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">
    Cada terminal representa un punto de venta (código, nombre, local/sucursal y caja).
    Los cajeros usan su terminal por defecto al iniciar sesión.
</p>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Local / sucursal</th>
                <th>Caja</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($terminales as $t)
            <tr>
                <td style="font-family:monospace;font-weight:700">{{ $t->codigo }}</td>
                <td>{{ $t->nombre }}</td>
                <td>{{ $t->local ?? '—' }}</td>
                <td>{{ $t->caja }}</td>
                <td>{{ $t->activo ? 'Activa' : 'Inactiva' }}</td>
                <td><a href="{{ route('terminales.editar', $t) }}">Editar</a></td>
            </tr>
            @empty
            <tr><td colspan="6" style="color:#64748b">Sin terminales registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $terminales->links() }}
</div>
@endsection
