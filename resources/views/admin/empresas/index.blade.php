@extends('layouts.admin')

@section('titulo', 'Empresas')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div>
        <h1 style="margin-bottom:.25rem">Empresas</h1>
        <p style="color:#64748b;font-size:.9rem">Datos operativos, usuarios y onboarding</p>
    </div>
    <a href="{{ route('admin.empresas.crear') }}" class="btn btn-primary">+ Nueva empresa</a>
</div>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, código o RUC" style="width:100%;max-width:320px">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Código</th>
                <th>Tipo</th>
                <th>Estado wizard</th>
                <th>Usuarios</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empresas as $e)
            @php
                $onboardingOk = (bool) (($e->configuracion ?? [])['onboarding_completado'] ?? false);
                $planNombre = config('planes.'.$e->plan.'.nombre', ucfirst($e->plan));
            @endphp
            <tr>
                <td>
                    <strong>{{ $e->nombre }}</strong>
                    <br><small style="color:#64748b">Plan {{ $planNombre }}</small>
                </td>
                <td><code>{{ $e->codigo }}</code></td>
                <td>{{ ucfirst($e->tipo_negocio) }}</td>
                <td style="color:{{ $onboardingOk ? '#065f46' : '#d97706' }}">
                    {{ $onboardingOk ? 'Listo' : 'Paso '.(($e->configuracion ?? [])['onboarding_paso'] ?? 1) }}
                </td>
                <td>
                    <a href="{{ route('admin.empresas.usuarios.index', $e) }}">{{ $e->usuarios_count }} usuarios</a>
                </td>
                <td>{{ $e->activo ? 'Activa' : 'Inactiva' }}</td>
                <td style="white-space:nowrap">
                    <a href="{{ route('admin.empresas.editar', $e) }}">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#64748b">Sin empresas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $empresas->links() }}
</div>
@endsection
