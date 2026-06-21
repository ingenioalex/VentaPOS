@extends('layouts.app')

@section('titulo', 'Ventas')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
    <h1 style="margin:0">Ventas</h1>
    @if($whatsappActivo && $urlWhatsappHoy && $urlWhatsappHoy !== '#')
    <a href="{{ $urlWhatsappHoy }}" target="_blank" rel="noopener" class="btn" style="background:#25d366;color:#fff">
        Enviar resumen WhatsApp (hoy)
    </a>
    @endif
</div>

<div class="card" style="margin-bottom:1rem">
    <h2 style="font-size:1rem;margin-bottom:.75rem;color:#0f766e">Dashboard — KPI del día</h2>
    <div class="stats" style="margin-bottom:1rem">
        <div class="stat">
            <span>Ventas hoy</span>
            <strong>S/ {{ number_format($kpi['hoy']['total'], 2) }}</strong>
            @if($kpi['variacion_vs_ayer_pct'] !== null)
            <small style="color:{{ $kpi['variacion_vs_ayer_pct'] >= 0 ? '#065f46' : '#991b1b' }}">
                {{ $kpi['variacion_vs_ayer_pct'] >= 0 ? '▲' : '▼' }} {{ abs($kpi['variacion_vs_ayer_pct']) }}% vs ayer
            </small>
            @endif
        </div>
        <div class="stat"><span>Tickets hoy</span><strong>{{ $kpi['hoy']['tickets'] }}</strong></div>
        <div class="stat"><span>Ticket promedio</span><strong>S/ {{ number_format($kpi['hoy']['ticket_promedio'], 2) }}</strong></div>
        <div class="stat"><span>Semana</span><strong>S/ {{ number_format($kpi['semana']['total'], 2) }}</strong></div>
        <div class="stat"><span>Mes</span><strong>S/ {{ number_format($kpi['mes']['total'], 2) }}</strong></div>
        <div class="stat"><span>Anuladas (mes)</span><strong>{{ $kpi['anuladas_mes'] }}</strong></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;font-size:.88rem">
        <div>
            <strong>Medios de pago (hoy)</strong>
            <ul style="margin:.35rem 0 0 1rem;color:#475569">
                @forelse($kpi['medios_hoy'] as $m)
                <li>{{ ucfirst($m->medio_pago) }}: S/ {{ number_format($m->monto, 2) }}</li>
                @empty
                <li>Sin ventas hoy</li>
                @endforelse
            </ul>
        </div>
        <div>
            <strong>Top productos (hoy)</strong>
            <ul style="margin:.35rem 0 0 1rem;color:#475569">
                @forelse($kpi['top_hoy'] as $p)
                <li>{{ $p->nombre }} ({{ $p->unidades }} u.)</li>
                @empty
                <li>—</li>
                @endforelse
            </ul>
        </div>
        <div>
            <strong>Por cajero (hoy)</strong>
            <ul style="margin:.35rem 0 0 1rem;color:#475569">
                @forelse($kpi['cajeros_hoy'] as $c)
                <li>{{ $c->cajero }}: S/ {{ number_format($c->monto, 2) }}</li>
                @empty
                <li>—</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@if($lotesActivo && $vencimientos->isNotEmpty())
<div class="alert-warning" style="margin-bottom:1rem">
    <strong>Vencimientos próximos (30 días):</strong>
    @foreach($vencimientos as $l)
    <span style="margin-left:.5rem">{{ $l->producto?->nombre }} ({{ $l->fecha_vencimiento->format('d/m') }})</span>
    @endforeach
    · <a href="{{ route('inventario.vencimientos') }}">Ver todos</a>
</div>
@endif

<form class="filtros card" method="get">
    <div>
        <label>Desde</label>
        <input type="date" name="desde" value="{{ $desde }}">
    </div>
    <div>
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta }}">
    </div>
    <div>
        <label>Estado</label>
        <select name="estado">
            <option value="emitido" @selected($estado === 'emitido')>Emitidas</option>
            <option value="anulado" @selected($estado === 'anulado')>Anuladas</option>
            <option value="todos" @selected($estado === 'todos')>Todas</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div class="stats" style="margin-bottom:1rem">
    <div class="stat"><span>Tickets (período)</span><strong>{{ $totales->tickets }}</strong></div>
    <div class="stat"><span>Total vendido</span><strong>S/ {{ number_format($totales->monto, 2) }}</strong></div>
</div>

<div class="cpe-leyenda card" style="padding:.65rem 1rem;margin-bottom:1rem">
    <strong style="font-size:.85rem;margin-right:.25rem">Leyenda CPE:</strong>
    <span><span class="cpe-badge cpe-sim">☑ E-B·SIM</span> Simulación</span>
    <span><span class="cpe-badge cpe-pro">☑ E-F·PRO</span> Producción</span>
    <span><span class="cpe-badge cpe-no">☐ INT</span> Ticket interno</span>
</div>

<div class="card" style="overflow:auto">
    <table class="data">
        <thead>
            <tr>
                <th>Número</th><th>CPE</th><th>Fecha</th><th>Cajero</th><th>Cliente</th>
                <th>Medio</th><th>Total</th><th>Estado</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $v)
            <tr>
                <td>{{ $v->numero_interno }}</td>
                <td>@include('app.ventas._badge-electronico', ['comprobante' => $v])</td>
                <td>{{ $v->emitido_en->format('d/m/Y H:i') }}</td>
                <td>{{ $v->usuario->nombre }}</td>
                <td>{{ $v->cliente?->nombre ?? '—' }}</td>
                <td>{{ $v->etiquetaMedioPago() }}</td>
                <td>S/ {{ number_format($v->total, 2) }}</td>
                <td>
                    @if($v->estado === 'anulado')
                        <span style="color:#dc2626">Anulado</span>
                    @else
                        <span style="color:#065f46">Emitido</span>
                    @endif
                </td>
                <td style="white-space:nowrap">
                    <a href="{{ route('ventas.detalle', $v) }}">Ver</a>
                    @if($v->estado === 'emitido')
                    · <a href="{{ route('pos.ticket', $v) }}" target="_blank">Ticket</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="color:#64748b">Sin ventas en el período</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $ventas->links() }}
</div>
@endsection
