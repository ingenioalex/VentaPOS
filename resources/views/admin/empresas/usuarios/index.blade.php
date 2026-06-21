@extends('layouts.admin')

@section('titulo', 'Usuarios — '.$empresa->nombre)

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
    <div>
        <h1>Usuarios de empresa</h1>
        <p style="color:#64748b;font-size:.9rem;margin-top:.25rem">
            <strong>{{ $empresa->nombre }}</strong> · código <code>{{ $empresa->codigo }}</code>
        </p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
        <a href="{{ route('admin.empresas.editar', $empresa) }}">← Empresa</a>
        <a href="{{ route('admin.empresas.usuarios.crear', $empresa) }}" class="btn btn-primary">+ Nuevo usuario</a>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;padding:.75rem 1rem;font-size:.88rem">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:.5rem;align-items:center">
        <div>
            <strong>Plan {{ $desglose['plan_nombre'] }}:</strong>
            {{ $desglose['activos'] }} / {{ $desglose['total'] }} usuarios
            <span style="color:#64748b">
                (base {{ $desglose['plan_base'] }}
                @if($desglose['bonificados'] > 0)+ {{ $desglose['bonificados'] }} bonif.@endif
                @if($desglose['extras_pago'] > 0)+ {{ $desglose['extras_pago'] }} pago @endif)
            </span>
        </div>
        <a href="{{ route('admin.contratacion.cupos', $empresa) }}" class="btn" style="background:#7c3aed;color:#fff;padding:.35rem .65rem;font-size:.82rem">Gestionar cupos</a>
    </div>
    @if($desglose['activos'] > $desglose['total'])
    <span style="display:block;margin-top:.35rem;color:#991b1b">Sobre el cupo configurado. Ajuste en «Gestionar cupos».</span>
    @endif
</div>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, usuario o email" style="width:100%;max-width:320px">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Rol</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Sesión</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $u)
            <tr>
                <td><strong>{{ $u->usuario }}</strong></td>
                <td>{{ $u->nombre }}</td>
                <td>{{ $u->etiquetaRol() }}</td>
                <td>{{ $u->email ?? '—' }}</td>
                <td>{{ $u->activo ? 'Activo' : 'Inactivo' }}</td>
                <td>
                    @if($u->en_sesion)
                    <span style="color:#2563eb">Activa desde {{ $u->sesion_inicio_en?->format('d/m H:i') }}</span>
                    @else
                    —
                    @endif
                </td>
                <td style="white-space:nowrap">
                    <a href="{{ route('admin.empresas.usuarios.editar', [$empresa, $u]) }}">Editar</a>
                    @if($u->en_sesion)
                    ·
                    <form method="post" action="{{ route('admin.empresas.usuarios.cerrar-sesion', [$empresa, $u]) }}"
                          style="display:inline" onsubmit="return confirm('¿Cerrar sesión de {{ $u->nombre }}?')">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:#d97706;cursor:pointer;padding:0;font:inherit">
                            Cerrar sesión
                        </button>
                    </form>
                    @endif
                    ·
                    <form method="post" action="{{ route('admin.empresas.usuarios.eliminar', [$empresa, $u]) }}"
                          style="display:inline" onsubmit="return confirm('¿Eliminar usuario {{ $u->usuario }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:0;font:inherit">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="color:#64748b">
                    Sin usuarios. <a href="{{ route('admin.empresas.usuarios.crear', $empresa) }}">Crear uno</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $usuarios->links() }}
</div>
@endsection
