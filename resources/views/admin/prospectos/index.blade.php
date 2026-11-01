@extends('layouts.admin')

@section('titulo', 'Prospectos')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
    <div>
        <h1 style="margin-bottom:.25rem">Prospectos</h1>
        <p style="color:#64748b;font-size:.9rem">Solicitudes desde la landing y formulario beta (público en ventasagil.com). Revise spam con estado «descartado».</p>
    </div>
</div>

<div class="stats" style="margin-bottom:1rem">
    <div class="card" style="flex:1;min-width:140px">
        <small style="color:#64748b">Nuevos sin revisar</small>
        <strong style="display:block;font-size:1.5rem;color:#d97706">{{ $stats['nuevos'] }}</strong>
    </div>
    <div class="card" style="flex:1;min-width:140px">
        <small style="color:#64748b">Pipeline activo</small>
        <strong style="display:block;font-size:1.5rem;color:#0f766e">{{ $stats['activos'] }}</strong>
    </div>
    <div class="card" style="flex:1;min-width:140px">
        <small style="color:#64748b">Desde formulario beta</small>
        <strong style="display:block;font-size:1.5rem;color:#7c3aed">{{ $stats['beta'] }}</strong>
    </div>
</div>

<form class="filtros card" method="get" id="filtros-prospectos">
    <div>
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Negocio, contacto, teléfono…" style="width:100%;min-width:200px">
    </div>
    <div>
        <label>Estado</label>
        <select name="estado">
            <option value="">Todos</option>
            @foreach(\App\Models\Prospecto::estados() as $e)
            <option value="{{ $e }}" @selected($estado === $e)>{{ ucfirst($e) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Origen</label>
        <select name="origen">
            <option value="">Todos</option>
            @foreach(\App\Models\Prospecto::origenes() as $o)
            <option value="{{ $o }}" @selected($origen === $o)>{{ ucfirst($o) }}</option>
            @endforeach
        </select>
    </div>
    <div style="display:flex;flex-direction:column;justify-content:flex-end;min-width:180px">
        <label style="margin-bottom:.35rem">Descartados</label>
        <label class="switch-prospectos" title="Ocultar prospectos marcados como descartado">
            <input type="hidden" name="ocultar_descartados" value="0">
            <input type="checkbox" name="ocultar_descartados" value="1" @checked($ocultarDescartados) onchange="this.form.submit()">
            <span class="switch-prospectos__track"></span>
            <span class="switch-prospectos__label">{{ $ocultarDescartados ? 'Ocultos' : 'Visibles' }}</span>
        </label>
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
    @if($q || $estado || $origen || ! $ocultarDescartados)
    <a href="{{ route('admin.prospectos.index') }}" class="btn" style="background:#e2e8f0;color:#334155">Limpiar</a>
    @endif
</form>

<style>
.switch-prospectos {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    cursor: pointer;
    user-select: none;
}
.switch-prospectos input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.switch-prospectos__track {
    position: relative;
    width: 42px;
    height: 24px;
    background: #cbd5e1;
    border-radius: 999px;
    transition: background .2s;
}
.switch-prospectos__track::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
    box-shadow: 0 1px 2px rgba(0,0,0,.15);
}
.switch-prospectos input:checked + .switch-prospectos__track {
    background: #0f766e;
}
.switch-prospectos input:checked + .switch-prospectos__track::after {
    transform: translateX(18px);
}
.switch-prospectos__label {
    font-size: .85rem;
    color: #475569;
    font-weight: 600;
}
</style>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Negocio</th>
                <th>Contacto</th>
                <th>Rubro</th>
                <th>Origen</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($prospectos as $p)
            @php
                $colorEstado = match($p->estado) {
                    'nuevo' => '#d97706',
                    'contactado' => '#2563eb',
                    'demo', 'beta' => '#7c3aed',
                    'cliente' => '#059669',
                    'descartado' => '#94a3b8',
                    default => '#64748b',
                };
            @endphp
            <tr>
                <td style="white-space:nowrap;font-size:.85rem;color:#64748b">
                    {{ $p->creado_en?->format('d/m/Y H:i') }}
                </td>
                <td>
                    <strong>{{ $p->nombre_negocio }}</strong>
                    @if($p->mensaje)
                    <br><small style="color:#64748b" title="{{ $p->mensaje }}">{{ Str::limit($p->mensaje, 60) }}</small>
                    @endif
                </td>
                <td>
                    {{ $p->nombre_contacto ?: '—' }}
                    <br><a href="{{ $p->whatsappUrl() }}" target="_blank" rel="noopener" style="font-size:.85rem">{{ $p->telefono }}</a>
                </td>
                <td>{{ ucfirst($p->tipo_negocio ?? '—') }}</td>
                <td><span style="font-size:.85rem">{{ $p->etiquetaOrigen() }}</span></td>
                <td>
                    <form method="post" action="{{ route('admin.prospectos.estado', $p) }}" style="display:flex;gap:.35rem;align-items:center">
                        @csrf
                        @method('PATCH')
                        <select name="estado" onchange="this.form.submit()" style="padding:.35rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.82rem;color:{{ $colorEstado }};font-weight:600">
                            @foreach(\App\Models\Prospecto::estados() as $e)
                            <option value="{{ $e }}" @selected($p->estado === $e)>{{ ucfirst($e) }}</option>
                            @endforeach
                        </select>
                    </form>
                </td>
                <td style="white-space:nowrap">
                    <a href="{{ route('admin.prospectos.show', $p) }}">Ver</a>
                    ·
                    <a href="{{ $p->whatsappUrl() }}?text={{ rawurlencode('Hola, soy de VentasÁgil. Vi tu solicitud para '.$p->nombre_negocio.'.') }}" target="_blank" rel="noopener">WhatsApp</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#64748b">No hay prospectos con estos filtros.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $prospectos->links() }}
</div>
@endsection
