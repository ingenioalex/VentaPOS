@extends('layouts.admin')

@section('titulo', 'Tarifas y precios')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
    <div>
        <h1 style="margin-bottom:.25rem">Tarifas comerciales</h1>
        <p style="color:#64748b;font-size:.9rem;max-width:640px">
            Precios de planes, módulos y extras. Los cambios se aplican en landing, alta de empresas,
            módulos contratados y extras.
        </p>
    </div>
    @if($tienePersonalizacion)
    <form method="post" action="{{ route('admin.tarifas.restaurar') }}"
          onsubmit="return confirm('¿Restaurar precios del archivo config (valores de fábrica)?')">
        @csrf
        <button type="submit" class="btn" style="background:#64748b;color:#fff">Restaurar defaults</button>
    </form>
    @endif
</div>

@if($tienePersonalizacion)
<div class="alert-ok" style="background:#eff6ff;color:#1e40af">
    Hay tarifas personalizadas guardadas en <code>storage/app/tarifas.json</code>.
</div>
@endif

<form method="post" action="{{ route('admin.tarifas.actualizar') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <h3 style="margin-bottom:1rem">Arranque (todos los planes)</h3>
        <div style="max-width:220px">
            <label style="font-weight:600;font-size:.85rem">Monto único (S/)</label>
            <input type="number" name="arranque_monto" step="0.01" min="0" required
                   value="{{ old('arranque_monto', $arranque['monto'] ?? 99) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
            <small style="color:#64748b">{{ $arranque['etiqueta'] ?? 'Instalación + 1er mes' }}</small>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:1rem">Planes comerciales (cuota mensual desde mes 2)</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem">
            @foreach($planes as $clave => $plan)
            <div>
                <label style="font-weight:600;font-size:.85rem">{{ $plan['nombre'] ?? ucfirst($clave) }} (S/ / mes)</label>
                <input type="number" name="plan_{{ $clave }}_mensual" step="0.01" min="0" required
                       value="{{ old('plan_'.$clave.'_mensual', $plan['mensual'] ?? 0) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:.5rem">Precio referencia por módulo (add-on)</h3>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">Usado en admin al contratar módulos por empresa.</p>
        @foreach($porGrupo as $grupo => $items)
        <h4 style="font-size:.9rem;color:#475569;margin:1rem 0 .5rem">{{ $grupo }}</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.65rem;margin-bottom:.5rem">
            @foreach($items as $item)
            <label style="display:block;font-size:.85rem">
                <span style="font-weight:600">{{ $item['nombre'] }}</span>
                <div style="display:flex;align-items:center;gap:.35rem;margin-top:.25rem">
                    <span style="color:#64748b">S/</span>
                    <input type="number" name="modulo_{{ $item['clave'] }}" step="0.01" min="0" required
                           value="{{ old('modulo_'.$item['clave'], $item['precio_mensual'] ?? 0) }}"
                           style="flex:1;padding:.45rem;border:1px solid #cbd5e1;border-radius:.4rem">
                    <span style="color:#94a3b8;font-size:.78rem">/mes</span>
                </div>
            </label>
            @endforeach
        </div>
        @endforeach
    </div>

    <div class="card">
        <h3 style="margin-bottom:1rem">Add-on usuario adicional</h3>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:.75rem">
            Precio por cada cupo extra de pago asignado en «Cupos de usuarios» por empresa.
        </p>
        <div style="max-width:220px">
            <label style="font-weight:600;font-size:.85rem">{{ config('planes.usuario_extra.nombre', 'Usuario adicional') }} (S/ / mes)</label>
            <input type="number" name="usuario_extra_mensual" step="0.01" min="0" required
                   value="{{ old('usuario_extra_mensual', config('planes.usuario_extra.precio_mensual', 5)) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:1rem">Extras (add-ons módulos)</h3>
        <div style="max-width:220px;margin-bottom:1rem">
            <label style="font-weight:600;font-size:.85rem">Precio default (S/ / mes)</label>
            <input type="number" name="extras_default" step="0.01" min="0" required
                   value="{{ old('extras_default', $extraDefault) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:.65rem">
            @foreach($extras as $clave => $info)
            <label style="display:block;font-size:.85rem">
                <span style="font-weight:600">{{ $info['nombre'] }}</span>
                <div style="display:flex;align-items:center;gap:.35rem;margin-top:.25rem">
                    <span style="color:#64748b">S/</span>
                    <input type="number" name="extra_{{ $clave }}" step="0.01" min="0" required
                           value="{{ old('extra_'.$clave, $info['precio_mensual'] ?? $extraDefault) }}"
                           style="flex:1;padding:.45rem;border:1px solid #cbd5e1;border-radius:.4rem">
                    <span style="color:#94a3b8;font-size:.78rem">/mes</span>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar tarifas</button>
    <a href="{{ route('admin.empresas.index') }}" style="margin-left:.5rem">Volver a empresas</a>
</form>
@endsection
