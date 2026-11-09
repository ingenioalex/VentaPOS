@extends('layouts.app')

@section('titulo', 'Ventas')

@section('contenido')
@include('app.ventas._styles')

<div class="ventas-page">
    <div class="ventas-header">
        <div>
            <h1>Ventas</h1>
            <p class="ventas-subtitle">Panel de operación, KPIs y comprobantes emitidos</p>
        </div>
        @if($whatsappActivo && $urlWhatsappHoy && $urlWhatsappHoy !== '#')
        <div class="ventas-actions">
            <a href="{{ $urlWhatsappHoy }}" target="_blank" rel="noopener" class="btn btn-whatsapp">
                Enviar resumen WhatsApp (hoy)
            </a>
        </div>
        @endif
    </div>

    <div class="panel">
        <div class="panel-title">Dashboard — KPI del día</div>
        <div class="kpi-grid">
            <div class="kpi-card kpi-highlight">
                <span class="kpi-label">Ventas hoy</span>
                <strong class="kpi-value">S/ {{ number_format($kpi['hoy']['total'], 2) }}</strong>
                @if($kpi['variacion_vs_ayer_pct'] !== null)
                <span class="kpi-delta {{ $kpi['variacion_vs_ayer_pct'] >= 0 ? 'up' : 'down' }}">
                    {{ $kpi['variacion_vs_ayer_pct'] >= 0 ? '▲' : '▼' }} {{ abs($kpi['variacion_vs_ayer_pct']) }}% vs ayer
                </span>
                @endif
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Tickets hoy</span>
                <strong class="kpi-value">{{ $kpi['hoy']['tickets'] }}</strong>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Ticket promedio</span>
                <strong class="kpi-value">S/ {{ number_format($kpi['hoy']['ticket_promedio'], 2) }}</strong>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Semana</span>
                <strong class="kpi-value">S/ {{ number_format($kpi['semana']['total'], 2) }}</strong>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Mes</span>
                <strong class="kpi-value">S/ {{ number_format($kpi['mes']['total'], 2) }}</strong>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Anuladas (mes)</span>
                <strong class="kpi-value">{{ $kpi['anuladas_mes'] }}</strong>
            </div>
        </div>

        <div class="insights-grid">
            <div class="insight-block">
                <strong>Medios de pago (hoy)</strong>
                <ul class="insight-list">
                    @forelse($kpi['medios_hoy'] as $m)
                    <li><span>{{ ucfirst($m->medio_pago) }}</span><span>S/ {{ number_format($m->monto, 2) }}</span></li>
                    @empty
                    <li><span>Sin ventas hoy</span><span>—</span></li>
                    @endforelse
                </ul>
            </div>
            <div class="insight-block">
                <strong>Top productos (hoy)</strong>
                <ul class="insight-list">
                    @forelse($kpi['top_hoy'] as $p)
                    <li><span>{{ $p->nombre }}</span><span>{{ number_format((float) $p->unidades, 2) }} {{ abs((float) $p->unidades - 1) < 0.001 ? 'UN' : 'UND' }}</span></li>
                    @empty
                    <li><span>—</span><span>—</span></li>
                    @endforelse
                </ul>
            </div>
            <div class="insight-block">
                <strong>Por cajero (hoy)</strong>
                <ul class="insight-list">
                    @forelse($kpi['cajeros_hoy'] as $c)
                    <li><span>{{ $c->cajero }}</span><span>S/ {{ number_format($c->monto, 2) }}</span></li>
                    @empty
                    <li><span>—</span><span>—</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    @if($lotesActivo && $vencimientos->isNotEmpty())
    <div class="alert-warning">
        <strong>Vencimientos próximos (30 días):</strong>
        @foreach($vencimientos as $l)
        <span style="margin-left:.5rem">{{ $l->producto?->nombre }} ({{ $l->fecha_vencimiento->format('d/m') }})</span>
        @endforeach
        · <a href="{{ route('inventario.vencimientos') }}">Ver todos</a>
    </div>
    @endif

    <div class="panel">
        <form class="filtros-ventas" method="get">
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
            <button type="submit" class="btn btn-accent">Filtrar</button>
        </form>
    </div>

    <div class="periodo-stats">
        <div class="periodo-stat">
            <span>Tickets (período)</span>
            <strong>{{ $totales->tickets }}</strong>
        </div>
        <div class="periodo-stat">
            <span>Total vendido</span>
            <strong>S/ {{ number_format($totales->monto, 2) }}</strong>
        </div>
    </div>

    <div class="cpe-leyenda panel" style="padding:.75rem 1.15rem">
        <strong style="font-size:.78rem;margin-right:.35rem;color:#64748b">LEYENDA CPE</strong>
        <span><span class="cpe-badge cpe-sim">☑ E-B·SIM</span> Simulación</span>
        <span><span class="cpe-badge cpe-pro">☑ E-F·PRO</span> Producción</span>
        <span><span class="cpe-badge cpe-no">☐ INT</span> Ticket interno</span>
    </div>

    <div class="panel" style="padding:0">
        <div class="tabla-wrap">
            <table class="ventas-table">
                <thead>
                    <tr>
                        <th>Número</th><th>CPE</th><th>Fecha</th><th>Cajero</th><th>Cliente</th>
                        <th>Medio</th><th>Total</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $v)
                    <tr>
                        <td><strong>{{ $v->numero_interno }}</strong></td>
                        <td>@include('app.ventas._badge-electronico', ['comprobante' => $v])</td>
                        <td>{{ $v->emitido_en->format('d/m/Y H:i') }}</td>
                        <td>{{ $v->usuario->nombre }}</td>
                        <td>{{ $v->cliente?->nombre ?? '—' }}</td>
                        <td>{{ $v->etiquetaMedioPago() }}</td>
                        <td><strong>S/ {{ number_format($v->total, 2) }}</strong></td>
                        <td>
                            <span class="estado-pill {{ $v->estado === 'anulado' ? 'anulado' : 'emitido' }}">
                                {{ $v->estado === 'anulado' ? 'Anulado' : 'Emitido' }}
                            </span>
                        </td>
                        <td style="white-space:nowrap">
                            <a href="{{ route('ventas.detalle', $v) }}" class="link-action">Ver</a>
                            @if($v->estado === 'emitido')
                            · <a href="{{ route('pos.ticket', $v) }}" target="_blank" class="link-action">Ticket</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="color:#64748b;text-align:center;padding:2rem">Sin ventas en el período</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:.85rem 1rem">{{ $ventas->links() }}</div>
    </div>
</div>
@endsection
