@extends('layouts.admin')

@section('titulo', 'Contratación — '.$empresa->nombre)

@section('contenido')
<div style="margin-bottom:.5rem">
    <h1 style="margin-bottom:.15rem">{{ $empresa->nombre }}</h1>
    <p style="color:#64748b;font-size:.9rem">Contratación · <code>{{ $empresa->codigo }}</code></p>
</div>

@include('admin.contratacion._nav', ['tab' => 'resumen'])

<div class="stats" style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem">
    <div class="card" style="margin:0;min-width:160px">
        <span style="color:#64748b;font-size:.8rem">Plan</span>
        <strong style="display:block;font-size:1.2rem">{{ $planInfo['nombre'] ?? ucfirst($empresa->plan) }}</strong>
        @if($empresa->plan === 'beta')
        <small style="color:#92400e">{{ $planInfo['nota'] ?? 'Prueba gratuita' }}</small>
        @else
        <small style="color:#64748b">S/ {{ $planInfo['mensual'] ?? 0 }}/mes</small>
        @endif
    </div>
    <div class="card" style="margin:0;min-width:110px">
        <span style="color:#64748b;font-size:.8rem">Módulos</span>
        <strong style="display:block;font-size:1.4rem">{{ count($modulos) }}</strong>
    </div>
    <div class="card" style="margin:0;min-width:110px">
        <span style="color:#64748b;font-size:.8rem">Extras</span>
        <strong style="display:block;font-size:1.4rem">{{ count($extras) }}</strong>
    </div>
    <div class="card" style="margin:0;min-width:120px">
        <span style="color:#64748b;font-size:.8rem">Usuarios</span>
        <strong style="display:block;font-size:1.4rem">{{ $desglose['activos'] }}/{{ $desglose['total'] }}</strong>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;margin-bottom:1.25rem">
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:.75rem">Cambiar plan de la empresa</h3>
        <form method="post" action="{{ route('admin.contratacion.plan', $empresa) }}">
            @csrf
            @method('PUT')
            <label style="font-weight:600;font-size:.85rem;display:block;margin-bottom:.35rem">Plan</label>
            <select name="plan" required style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-bottom:.75rem">
                @foreach($planesDisponibles as $p)
                @php $pi = config('planes.'.$p, []); @endphp
                <option value="{{ $p }}" @selected($empresa->plan === $p)>
                    @if($p === 'beta')
                    {{ $pi['nombre'] ?? 'Beta' }} — {{ $pi['nota'] ?? 'Gratis' }}
                    @else
                    {{ $pi['nombre'] ?? ucfirst($p) }} — S/ {{ $pi['mensual'] ?? 0 }}/mes
                    @endif
                </option>
                @endforeach
            </select>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;margin-bottom:1rem">
                <input type="checkbox" name="aplicar_modulos" value="1">
                Aplicar módulos del plan seleccionado
            </label>
            <button type="submit" class="btn btn-primary">Guardar plan</button>
        </form>
    </div>
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:.75rem">Ir a</h3>
        <div style="display:flex;flex-direction:column;gap:.45rem">
            <a href="{{ route('admin.contratacion.modulos', $empresa) }}" class="btn" style="background:#e0e7ff;color:#3730a3;text-align:left">Módulos (detalle)</a>
            <a href="{{ route('admin.contratacion.extras', $empresa) }}" class="btn" style="background:#ecfdf5;color:#065f46;text-align:left">Extras add-on</a>
            <a href="{{ route('admin.contratacion.cupos', $empresa) }}" class="btn" style="background:#f5f3ff;color:#5b21b6;text-align:left">Cupos de usuarios</a>
            <a href="{{ route('admin.tarifas.index') }}" class="btn" style="background:#f1f5f9;color:#475569;text-align:left">Tarifas globales</a>
        </div>
    </div>
</div>

@include('admin.contratacion._plantillas-plan', ['empresa' => $empresa, 'planesLista' => $planesDisponibles, 'modulosPorPlan' => $modulosPorPlan])
@endsection
