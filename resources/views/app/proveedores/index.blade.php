@extends('layouts.app')

@section('titulo', 'Proveedores')

@section('contenido')
@include('app.compras._nav')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h1>Proveedores</h1>
    <a href="{{ route('proveedores.crear') }}" class="btn btn-primary">+ Nuevo</a>
</div>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Razón social o documento" style="width:100%;max-width:320px">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr><th>Documento</th><th>Razón social</th><th>Contacto</th><th>Teléfono</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($proveedores as $p)
            <tr>
                <td>{{ $p->etiquetaDocumento() }}</td>
                <td>{{ $p->razon_social }}</td>
                <td>{{ $p->nombre_contacto ?? '—' }}</td>
                <td>{{ $p->telefono ?? '—' }}</td>
                <td>{{ $p->activo ? 'Activo' : 'Inactivo' }}</td>
                <td><a href="{{ route('proveedores.editar', $p) }}">Editar</a></td>
            </tr>
            @empty
            <tr><td colspan="6" style="color:#64748b">Sin proveedores. <a href="{{ route('proveedores.crear') }}">Registrar uno</a></td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $proveedores->links() }}
</div>
@endsection
