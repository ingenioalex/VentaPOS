@extends('layouts.app')



@section('titulo', 'Usuarios')



@section('contenido')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">

    <h1 style="margin:0">Usuarios</h1>

    @if($puedeCrear)

    <a href="{{ route('usuarios.crear') }}" class="btn btn-primary">+ Nuevo usuario</a>

    @else

    <span class="btn" style="background:#cbd5e1;color:#64748b;cursor:not-allowed" title="Cupo completo">+ Nuevo usuario</span>

    @endif

</div>



@if($empresa && $desglose)

<div class="card" style="margin-bottom:1rem;padding:.85rem 1rem">

    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between">

        <div>

            <strong style="font-size:.9rem">Cupos (plan {{ $desglose['plan_nombre'] }})</strong>

            <p style="margin:.25rem 0 0;font-size:.85rem;color:#64748b">

                {{ $desglose['activos'] }} de {{ $desglose['total'] }} activos

                · Base {{ $desglose['plan_base'] }}

                @if($desglose['bonificados'] > 0)+ {{ $desglose['bonificados'] }} bonif.@endif

                @if($desglose['extras_pago'] > 0)+ {{ $desglose['extras_pago'] }} add-on@endif

            </p>

        </div>

        <div style="min-width:160px;flex:1;max-width:280px">

            <div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden">

                <div style="background:{{ $desglose['activos'] >= $desglose['total'] ? '#dc2626' : '#0f766e' }};height:100%;width:{{ $desglose['total'] > 0 ? min(100, round($desglose['activos'] / $desglose['total'] * 100)) : 0 }}%"></div>

            </div>

        </div>

    </div>

    @unless($puedeCrear)

    <p style="margin:.65rem 0 0;font-size:.85rem;color:#92400e;background:#fef3c7;padding:.5rem .65rem;border-radius:.4rem">

        Cupo completo. Solicite a soporte VentaPOS usuarios adicionales.

    </p>

    @endunless

</div>

@endif



<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">

    Administre supervisores, cajeros y digitadores. El administrador principal no aparece en esta lista.

</p>



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

                    <span style="color:#2563eb">En sesión</span>

                    @else

                    —

                    @endif

                </td>

                <td>

                    <a href="{{ route('usuarios.editar', $u) }}">Editar</a>

                    @if(auth()->user()->tieneModulo('usuario_terminales') && in_array($u->rol, ['cajero', 'supervisor']))

                    · <a href="{{ route('usuario-terminales.editar', $u) }}">Terminales</a>

                    @endif

                </td>

            </tr>

            @empty

            <tr>

                <td colspan="7" style="color:#64748b">

                    Sin usuarios adicionales.

                    @if($puedeCrear)

                    <a href="{{ route('usuarios.crear') }}">Crear uno</a>

                    @endif

                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

    {{ $usuarios->links() }}

</div>

@endsection

