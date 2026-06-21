@extends('layouts.admin')

@section('titulo', 'Contratación')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div>
        <h1 style="margin-bottom:.25rem">Contratación</h1>
        <p style="color:#64748b;font-size:.9rem">Planes, módulos, extras y cupos por empresa</p>
    </div>
    <a href="{{ route('admin.tarifas.index') }}" class="btn" style="background:#64748b;color:#fff">Tarifas globales</a>
</div>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar empresa</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o código" style="width:100%;max-width:320px">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Plan</th>
                <th>Módulos</th>
                <th>Extras</th>
                <th>Usuarios</th>
                <th>Est. mensual</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($empresas as $e)
            @php
                $mods = app(\App\Services\PermisoService::class)->modulosContratados($e);
                $ext = app(\App\Services\ExtrasEmpresaService::class)->contratados($e);
                $totMod = app(\App\Services\PermisoService::class)->totalMensualContrato($mods);
                $totExt = app(\App\Services\ExtrasEmpresaService::class)->totalMensual($ext);
                $cupos = app(\App\Services\PlanLimiteService::class)->desglose($e);
                $planNombre = config('planes.'.$e->plan.'.nombre', ucfirst($e->plan));
            @endphp
            <tr>
                <td>
                    <strong>{{ $e->nombre }}</strong>
                    <br><code style="font-size:.8rem">{{ $e->codigo }}</code>
                </td>
                <td>{{ $planNombre }}</td>
                <td>{{ count($mods) }}</td>
                <td>{{ count($ext) }}</td>
                <td>{{ $cupos['activos'] }}/{{ $cupos['total'] }}</td>
                <td>S/ {{ number_format($totMod + $totExt + $cupos['costo_extras_mensual'], 0) }}</td>
                <td>
                    <a href="{{ route('admin.contratacion.show', $e) }}" class="btn btn-primary" style="padding:.35rem .65rem;font-size:.82rem">Gestionar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#64748b">Sin empresas</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $empresas->links() }}
</div>
@endsection
