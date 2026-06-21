@extends('layouts.admin')

@section('titulo', 'Extras — '.$empresa->nombre)

@section('contenido')
<div style="margin-bottom:.5rem">
    <h1 style="margin-bottom:.15rem">Extras (add-ons)</h1>
    <p style="color:#64748b;font-size:.9rem">{{ $empresa->nombre }} · {{ count($contratados) }} activos · S/ {{ number_format($totalMensual, 0) }}/mes</p>
</div>

@include('admin.contratacion._nav', ['tab' => 'extras'])

<div style="margin-bottom:1rem;display:flex;flex-wrap:wrap;gap:.5rem;align-items:center">
    <form method="post" action="{{ route('admin.contratacion.extras.aplicar-tipo', $empresa) }}" style="display:inline">
        @csrf
        <button type="submit" class="btn" style="background:#64748b;color:#fff;font-size:.85rem">Aplicar sugeridos por tipo</button>
    </form>
    <span style="font-size:.82rem;color:#64748b">KPI en Ventas incluido con módulo Ventas (sin extra).</span>
</div>

<form method="post" action="{{ route('admin.contratacion.extras.actualizar', $empresa) }}">
    @csrf
    @method('PUT')

    @foreach($porGrupo as $grupo => $items)
    <div class="card">
        <h3 style="margin-bottom:1rem">{{ $grupo }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem">
            @foreach($items as $item)
            @php $moduloOk = empty($item['requiere']) || in_array($item['requiere'], $modulosContratados, true); @endphp
            <label style="display:flex;align-items:flex-start;gap:.6rem;padding:.65rem;border:1px solid #e2e8f0;border-radius:.5rem;cursor:{{ $moduloOk ? 'pointer' : 'not-allowed' }};background:{{ in_array($item['clave'], $contratados, true) ? '#ecfdf5' : '#fff' }};opacity:{{ $moduloOk ? '1' : '.65' }}">
                <input type="checkbox" name="extras[]" value="{{ $item['clave'] }}"
                       @checked(in_array($item['clave'], old('extras', $contratados), true))
                       @disabled(! $moduloOk) style="margin-top:.2rem">
                <span>
                    <strong style="display:block">{{ $item['nombre'] }}</strong>
                    <small style="color:#64748b">S/ {{ number_format($item['precio_mensual'] ?? config('extras.precio_mensual_default'), 0) }} / mes</small>
                    <small style="display:block;color:#94a3b8;margin-top:.2rem">{{ $item['descripcion'] }}</small>
                    @if(!empty($item['requiere']))
                    @php $nomReq = config('permisos.modulos_contratables.'.$item['requiere'].'.nombre', $item['requiere']); @endphp
                    <small style="display:block;color:{{ $moduloOk ? '#94a3b8' : '#dc2626' }};margin-top:.25rem">
                        Requiere {{ $nomReq }}{{ $moduloOk ? '' : ' (no contratado)' }}
                    </small>
                    @endif
                </span>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach

    <div style="margin-top:1rem">
        <button type="submit" class="btn btn-primary">Guardar extras</button>
        <a href="{{ route('admin.contratacion.show', $empresa) }}" style="margin-left:.5rem">Volver al resumen</a>
    </div>
</form>
@endsection
