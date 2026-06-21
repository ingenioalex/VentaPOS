@extends('layouts.app')

@section('titulo', 'Usuarios y terminales')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Usuarios y terminales</h1>
    <a href="{{ route('terminales.index') }}" class="btn" style="background:#64748b;color:#fff">Gestionar terminales</a>
</div>

<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">
    Asigne una o más terminales a cada cajero o supervisor. Marque cuál es la terminal por defecto al iniciar sesión.
</p>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar usuario</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o usuario" style="width:100%;max-width:320px">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Terminales</th>
                <th>Por defecto</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $u)
            <tr>
                <td>
                    <strong>{{ $u->nombre }}</strong><br>
                    <small style="color:#64748b">{{ $u->usuario }}</small>
                </td>
                <td>{{ $u->etiquetaRol() }}</td>
                <td>{{ $u->terminales_count }}</td>
                <td>
                    @if($u->terminales->first())
                        {{ $u->terminales->first()->etiquetaCompleta() }}
                    @else
                        <span style="color:#d97706">Sin asignar</span>
                    @endif
                </td>
                <td><a href="{{ route('usuario-terminales.editar', $u) }}">Asignar</a></td>
            </tr>
            @empty
            <tr><td colspan="5" style="color:#64748b">No hay cajeros ni supervisores.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $usuarios->links() }}
</div>
@endsection
