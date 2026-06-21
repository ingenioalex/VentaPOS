@extends('layouts.admin')

@section('titulo', 'Super usuarios')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h1>Super usuarios</h1>
    <a href="{{ route('admin.super-usuarios.crear') }}" class="btn btn-primary">+ Nuevo super usuario</a>
</div>

<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">
    Acceden al panel <code>/admin</code> y gestionan todas las empresas de la plataforma.
</p>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr><th>Usuario</th><th>Nombre</th><th>Email</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($superUsuarios as $u)
            <tr>
                <td><strong>{{ $u->usuario }}</strong></td>
                <td>{{ $u->nombre }}</td>
                <td>{{ $u->email ?? '—' }}</td>
                <td>{{ $u->activo ? 'Activo' : 'Inactivo' }}</td>
                <td><a href="{{ route('admin.super-usuarios.editar', $u) }}">Editar</a></td>
            </tr>
            @empty
            <tr><td colspan="5" style="color:#64748b">Sin super usuarios.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
