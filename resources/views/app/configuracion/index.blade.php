@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
<h1 style="margin-bottom:.5rem">Configuración de empresa</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
    Datos del negocio y series de comprobantes. Se muestran en tickets y en el POS.
</p>

<div class="card" style="max-width:720px">
    <form method="post" action="{{ route('configuracion.actualizar') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h3 style="margin-bottom:1rem">Datos del negocio</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1.5rem">
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre comercial *</label>
                <input name="nombre" required value="{{ old('nombre', $empresa->nombre) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">RUC</label>
                <input name="ruc" maxlength="11" value="{{ old('ruc', $empresa->ruc) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Tipo de negocio *</label>
                <select name="tipo_negocio" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    @foreach(['bodega','ferreteria','farmacia','otro'] as $t)
                    <option value="{{ $t }}" @selected(old('tipo_negocio', $empresa->tipo_negocio) === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Teléfono</label>
                <input name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div style="grid-column:1/-1">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Dirección</label>
                <input name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div style="grid-column:1/-1">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Logo (ticket)</label>
                @if($empresa->logoUrl())
                <div style="margin-bottom:.5rem">
                    <img src="{{ $empresa->logoUrl() }}" alt="Logo" style="max-height:64px;max-width:200px;border:1px solid #e2e8f0;border-radius:.35rem;padding:.25rem">
                </div>
                @endif
                <input type="file" name="logo" accept="image/*"
                       style="width:100%;padding:.45rem;border:1px solid #cbd5e1;border-radius:.5rem;background:#fff">
                <small style="color:#64748b">PNG o JPG, máx. 2 MB. Se muestra en el ticket de venta.</small>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Código empresa</label>
                <input value="{{ $empresa->codigo }}" readonly
                       style="width:100%;padding:.6rem;border:1px solid #e2e8f0;border-radius:.5rem;background:#f8fafc">
                <small style="color:#64748b">Solo el super admin puede cambiarlo.</small>
            </div>
        </div>

        <h3 style="margin-bottom:1rem">POS y comprobantes</h3>
        <p style="color:#64748b;font-size:.85rem;margin-bottom:1rem">
            Las terminales y cajas se gestionan en
            @if(auth()->user()->tieneModulo('terminales'))
            <a href="{{ route('terminales.index') }}">Terminales</a>.
            @else
            el módulo Terminales.
            @endif
        </p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem">
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Serie boleta *</label>
                <input name="serie_boleta" required value="{{ old('serie_boleta', $config['serie_boleta'] ?? 'B001') }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Serie factura *</label>
                <input name="serie_factura" required value="{{ old('serie_factura', $config['serie_factura'] ?? 'F001') }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar configuración</button>
        @if(auth()->user()->tieneModulo('sunat'))
        <a href="{{ route('sunat.index') }}" class="btn" style="margin-left:.5rem;background:#0f766e;color:#fff">Facturación SUNAT</a>
        @endif
    </form>
</div>

<div class="card" style="max-width:720px;margin-top:1rem">
    <h3 style="margin-bottom:.75rem">Extras contratados</h3>
    <p style="color:#64748b;font-size:.85rem;margin-bottom:1rem">
        Los add-ons (WhatsApp, lotes, flejes, etc.) los activa el administrador de VentaPOS.
        Cada extra tiene un costo de S/ {{ config('extras.precio_mensual_default') }}/mes.
        Para contratar uno, contacte a soporte.
    </p>
    <ul style="list-style:none;padding:0;margin:0 0 1rem">
        @foreach($extrasDisponibles as $clave => $info)
        <li style="padding:.5rem 0;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;gap:1rem;align-items:center">
            <span>
                <strong>{{ $info['nombre'] }}</strong>
                <br><small style="color:#64748b">{{ $info['descripcion'] }}</small>
            </span>
            @if($extrasActivos[$clave] ?? false)
            <span style="background:#d1fae5;color:#065f46;font-size:.75rem;font-weight:700;padding:.25rem .55rem;border-radius:999px">Activo</span>
            @else
            <span style="background:#f1f5f9;color:#64748b;font-size:.75rem;padding:.25rem .55rem;border-radius:999px">No contratado</span>
            @endif
        </li>
        @endforeach
    </ul>

    @if($extrasActivos['whatsapp_resumen'] ?? false)
    <form method="post" action="{{ route('configuracion.actualizar') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="whatsapp_form" value="1">
        <input type="hidden" name="nombre" value="{{ $empresa->nombre }}">
        <input type="hidden" name="tipo_negocio" value="{{ $empresa->tipo_negocio }}">
        <input type="hidden" name="serie_boleta" value="{{ $config['serie_boleta'] ?? 'B001' }}">
        <input type="hidden" name="serie_factura" value="{{ $config['serie_factura'] ?? 'F001' }}">
        <label style="font-weight:600;font-size:.85rem">WhatsApp resumen — número destino (solo dígitos)</label>
        <div style="display:flex;gap:.5rem;align-items:center;margin-top:.35rem;flex-wrap:wrap">
            <input name="whatsapp_telefono" value="{{ old('whatsapp_telefono', $whatsappCfg['telefono'] ?? preg_replace('/\D/', '', $empresa->telefono ?? '')) }}"
                   placeholder="987654321" style="padding:.55rem;border:1px solid #cbd5e1;border-radius:.4rem;max-width:200px">
            <button type="submit" class="btn btn-primary" style="padding:.45rem .85rem">Guardar teléfono</button>
        </div>
        <small style="color:#64748b;display:block;margin-top:.35rem">El resumen diario se envía vía enlace wa.me (cron 20:00).</small>
    </form>
    @endif
</div>
@endsection
