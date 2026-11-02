@extends('layouts.admin')

@section('titulo', 'Prospecto — '.$prospecto->nombre_negocio)

@section('contenido')
<div style="margin-bottom:1rem">
    <a href="{{ route('admin.prospectos.index') }}" style="font-size:.9rem;color:#64748b">← Volver a prospectos</a>
    <h1 style="margin:.35rem 0 .15rem">{{ $prospecto->nombre_negocio }}</h1>
    <p style="color:#64748b;font-size:.9rem">
        Recibido {{ $prospecto->creado_en?->format('d/m/Y H:i') }} · {{ $prospecto->etiquetaOrigen() }}
    </p>
</div>

<div class="card" style="margin-bottom:1rem">
    <h3 style="margin-bottom:.75rem">Datos de contacto</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;font-size:.92rem">
        <div>
            <small style="color:#64748b;display:block">Contacto</small>
            <strong>{{ $prospecto->nombre_contacto ?: '—' }}</strong>
        </div>
        <div>
            <small style="color:#64748b;display:block">WhatsApp / teléfono</small>
            <a href="{{ $prospecto->whatsappUrl() }}" target="_blank" rel="noopener"><strong>{{ $prospecto->telefono }}</strong></a>
        </div>
        <div>
            <small style="color:#64748b;display:block">Rubro</small>
            <strong>{{ ucfirst($prospecto->tipo_negocio ?? '—') }}</strong>
        </div>
        <div>
            <small style="color:#64748b;display:block">Estado</small>
            <form method="post" action="{{ route('admin.prospectos.estado', $prospecto) }}" style="display:inline-flex;gap:.35rem;align-items:center">
                @csrf
                @method('PATCH')
                <select name="estado" onchange="this.form.submit()" style="padding:.35rem;border:1px solid #cbd5e1;border-radius:.4rem">
                    @foreach(\App\Models\Prospecto::estados() as $e)
                    <option value="{{ $e }}" @selected($prospecto->estado === $e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:1rem">
    <h3 style="margin-bottom:.75rem">Mensaje completo</h3>
    @if($prospecto->mensaje)
    <div style="white-space:pre-wrap;line-height:1.55;font-size:.95rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;padding:1rem">{{ $prospecto->mensaje }}</div>
    @else
    <p style="color:#64748b">Sin mensaje adicional.</p>
    @endif
</div>

<div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <a href="{{ route('admin.guia.index', ['nombre' => $prospecto->nombre_contacto ?: $prospecto->nombre_negocio]) }}"
       class="btn" style="background:#0f766e;color:#fff">Guía de respuesta (plantillas)</a>
    <a href="{{ $prospecto->whatsappUrl() }}?text={{ rawurlencode('Hola, soy de VentasÁgil. Vi tu solicitud para '.$prospecto->nombre_negocio.'.') }}"
       class="btn btn-primary" target="_blank" rel="noopener">Responder por WhatsApp</a>
    @if($prospecto->estado !== 'descartado')
    <form method="post" action="{{ route('admin.prospectos.estado', $prospecto) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="estado" value="descartado">
        <button type="submit" class="btn" style="background:#e2e8f0;color:#475569" onclick="return confirm('¿Marcar como descartado (spam)?')">Marcar spam / descartar</button>
    </form>
    @endif
</div>
@endsection
