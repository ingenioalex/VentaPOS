@extends('layouts.app')

@section('titulo', 'Clientes')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h1>Clientes</h1>
    <a href="{{ route('clientes.crear') }}" class="btn btn-primary">+ Nuevo</a>
</div>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o documento" style="width:100%;max-width:320px">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr><th>Documento</th><th>Nombre</th><th>Teléfono</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($clientes as $c)
            <tr>
                <td>{{ strtoupper($c->tipo_documento) }} {{ $c->numero_documento }}</td>
                <td>{{ $c->nombre }}</td>
                <td>{{ $c->telefono ?? '—' }}</td>
                <td>{{ $c->activo ? 'Activo' : 'Inactivo' }}</td>
                <td><a href="{{ route('clientes.editar', $c) }}">Editar</a></td>
            </tr>
            @empty
            <tr><td colspan="5" style="color:#64748b">Sin clientes. <a href="{{ route('clientes.crear') }}">Registrar uno</a></td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $clientes->links() }}
</div>
@endsection
