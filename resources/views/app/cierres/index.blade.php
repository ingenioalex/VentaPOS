@extends('layouts.app')

@section('titulo', 'Cierre de caja')

@push('head')
<style>
.cierres-page .page-intro { color:#64748b; font-size:.9rem; margin-bottom:1.25rem; max-width:52rem; line-height:1.5; }
.cierres-page .glosario {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:.75rem;
    margin-bottom:1.25rem;
}
.cierres-page .glosario-item {
    background:#f8fafc; border:1px solid #e2e8f0; border-radius:.6rem; padding:.85rem 1rem; font-size:.82rem;
}
.cierres-page .glosario-item strong { display:block; color:#0f172a; margin-bottom:.25rem; font-size:.88rem; }
.cierres-page .kpi-grid {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:.75rem; margin-bottom:1.25rem;
}
.cierres-page .resumen-dual {
    display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;
}
@media (max-width: 768px) { .cierres-page .resumen-dual { grid-template-columns:1fr; } }
.cierres-page .resumen-dual__ventas,
.cierres-page .resumen-dual__arqueo {
    background:#fff; border:1px solid #e2e8f0; border-radius:.65rem; padding:1rem 1.1rem;
}
.cierres-page .resumen-dual__arqueo { background:#fffbeb; border-color:#fde68a; }
.cierres-page .resumen-dual__titulo { margin:0 0 .25rem; font-size:.95rem; color:#0f172a; }
.cierres-page .resumen-dual__sub { margin:0 0 .65rem; font-size:.78rem; color:#64748b; }
.cierres-page .resumen-dual__meta { margin:0 0 .65rem; font-size:.8rem; color:#64748b; }
.cierres-page .resumen-medios, .cierres-page .resumen-arqueo { width:100%; border-collapse:collapse; font-size:.88rem; }
.cierres-page .resumen-medios td, .cierres-page .resumen-arqueo td { padding:.4rem 0; border-bottom:1px solid #f1f5f9; }
.cierres-page .resumen-medios__total td { border-top:2px solid #e2e8f0; border-bottom:none; font-weight:700; padding-top:.55rem; }
.cierres-page .resumen-arqueo__dif td { border-bottom:none; font-weight:700; padding-top:.45rem; }
.cierres-page .resumen-arqueo__dif--alerta td { color:#dc2626; }
.cierres-page .medio-dot { display:inline-block; width:.55rem; height:.55rem; border-radius:50%; margin-right:.35rem; vertical-align:middle; }
.cierres-page .medio-dot--ef { background:#0f766e; }
.cierres-page .medio-dot--tar { background:#2563eb; }
.cierres-page .medio-dot--otr { background:#7c3aed; }
.cierres-page .resumen-dual--compacto { margin-bottom:.85rem; }
.cierres-page .resumen-dual--compacto .resumen-dual__ventas,
.cierres-page .resumen-dual--compacto .resumen-dual__arqueo { padding:.75rem .9rem; }
.cierres-page .kpi-grid {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:.75rem; margin-bottom:1.25rem;
}
.cierres-page .kpi {
    background:#fff; border:1px solid #e2e8f0; border-radius:.65rem; padding:1rem;
    box-shadow:0 1px 2px rgba(15,23,42,.04);
}
.cierres-page .kpi-label { font-size:.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.03em; }
.cierres-page .kpi-hint { font-size:.72rem; color:#94a3b8; margin-top:.15rem; line-height:1.35; }
.cierres-page .kpi-value { font-size:1.45rem; font-weight:700; color:#0f172a; margin-top:.35rem; }
.cierres-page .kpi--money .kpi-value { color:#0f766e; }
.cierres-page .kpi--warn .kpi-value { color:#b45309; }
.cierres-page .kpi--danger .kpi-value { color:#dc2626; }
.cierres-page .section-head { display:flex; flex-wrap:wrap; gap:.5rem 1rem; align-items:baseline; margin-bottom:.75rem; }
.cierres-page .section-head h3 { margin:0; }
.cierres-page .section-head p { margin:0; font-size:.85rem; color:#64748b; }
.cierres-page .badge-tipo {
    display:inline-block; font-size:.72rem; font-weight:700; padding:.2rem .5rem; border-radius:.35rem;
}
.cierres-page .badge-x { background:#fef3c7; color:#92400e; }
.cierres-page .badge-z { background:#fee2e2; color:#991b1b; }
.cierres-page .cajero-card {
    border:1px solid #e2e8f0; border-radius:.65rem; padding:1rem; margin-bottom:.75rem; background:#fafafa;
}
.cierres-page .cajero-metrics { display:flex; flex-wrap:wrap; gap:.75rem 1.25rem; margin-bottom:1rem; font-size:.88rem; }
.cierres-page .cajero-metrics dt { color:#64748b; font-size:.75rem; margin-bottom:.1rem; }
.cierres-page .cajero-metrics dd { margin:0; font-weight:600; }
.cierres-page .tabla-leyenda { font-size:.78rem; color:#64748b; margin:.75rem 0 0; line-height:1.45; }
.cierres-page .tabla-wrap { overflow:auto; }
.cierres-page table.data th { font-size:.78rem; white-space:nowrap; background:#f8fafc; }
.cierres-page table.data td { font-size:.84rem; vertical-align:top; }
.cierres-page .col-money { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
.cierres-page .col-periodo { font-size:.78rem; color:#64748b; min-width:9rem; }
.cierres-page .pill-supervisor { font-size:.72rem; color:#475569; background:#e2e8f0; padding:.1rem .4rem; border-radius:.25rem; }
.cierres-page .dia-bloque { margin-bottom:1.5rem; }
.cierres-page .dia-bloque--hoy { border:2px solid #0f766e; border-radius:.65rem; padding:1rem; background:#f0fdfa; }
.cierres-page .dia-cabecera {
    display:flex; flex-wrap:wrap; gap:.5rem 1.25rem; align-items:center; justify-content:space-between;
    margin-bottom:.65rem; padding-bottom:.5rem; border-bottom:2px solid #e2e8f0;
}
.cierres-page .dia-cabecera h4 { margin:0; font-size:1rem; color:#0f172a; }
.cierres-page .dia-cabecera .dia-resumen { font-size:.8rem; color:#64748b; }
.cierres-page .dia-cabecera .dia-resumen strong { color:#0f172a; }
.cierres-page .fila-reciente { background:#ecfdf5 !important; }
.cierres-page .fila-diferencia { background:#fff7ed !important; }
.cierres-page .fila-reciente.fila-diferencia { background:#fef3c7 !important; outline:2px solid #f59e0b; }
.cierres-page .btn-reimprimir {
    display:inline-block; padding:.35rem .7rem; font-size:.78rem; font-weight:600;
    background:#0f766e; color:#fff; border-radius:.4rem; text-decoration:none; white-space:nowrap;
}
.cierres-page .btn-reimprimir:hover { background:#0d9488; color:#fff; }
.cierres-page .col-accion { text-align:right; width:7rem; }
.cierres-page table.data th.col-ventas,
.cierres-page table.data td.col-ventas { border-left:2px solid #e2e8f0; }
.cierres-page table.data th.col-arqueo,
.cierres-page table.data td.col-arqueo { border-left:2px solid #cbd5e1; }
.cierres-page table.data .grupo-th { font-size:.7rem; text-transform:uppercase; letter-spacing:.04em; color:#64748b; text-align:center; padding:.35rem; }
.cierres-page table.data tfoot td { border-top:2px solid #e2e8f0; }
</style>
@endpush

@section('contenido')
@php
    $rt = $resumenTurno;
    $esGestor = $usuario->puedeGestionar();
@endphp
<div class="cierres-page">
<h1 style="margin-bottom:.35rem">Cierre de caja</h1>
<p class="page-intro">
    Terminal {{ $resumen['terminal'] ?? '01' }} · Caja {{ $resumen['caja'] ?? '01' }}
    @if(!empty($resumen['terminal_local'])) · {{ $resumen['terminal_local'] }} @endif
    @if($esGestor && $resumenZ)
    · Jornada activa desde <strong>{{ $resumenZ['fecha_desde'] }}</strong>
    @elseif($rt)
    · Turno del <strong>{{ $rt['fecha_desde'] }}</strong> al <strong>{{ $rt['fecha_hasta'] }}</strong>
    @endif
</p>

<div class="glosario">
    <div class="glosario-item">
        <strong>Cierre X</strong>
        Cierra el turno de un vendedor (ventas + arqueo de efectivo).
    </div>
    <div class="glosario-item">
        <strong>Cierre Z</strong>
        Cierra un día completo. Cada Z cubre un solo día y no se solapa con otros.
    </div>
    <div class="glosario-item">
        <strong>Días sin cerrar</strong>
        Si olvidó cerrar ayer, use «Cerrar este día» en orden (el más antiguo primero).
    </div>
</div>

@if(session('ok'))
<p style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.9rem">{{ session('ok') }}</p>
@endif
@error('cierre_z')
<p style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.9rem">{{ $message }}</p>
@enderror

@if($rt)
@include('app.cierres._resumen-dual', [
    'titulo' => $rt['titulo'],
    'subtitulo' => $rt['subtitulo'],
    'periodoDesde' => $rt['fecha_desde'],
    'periodoHasta' => $rt['fecha_hasta'],
    'tickets' => $rt['cantidad_ventas'],
    'efectivo' => $rt['total_efectivo'],
    'tarjeta' => $rt['total_tarjeta'],
    'otros' => $rt['total_otros'],
    'efectivoSistema' => $rt['total_efectivo'],
    'efectivoContado' => null,
    'diferencia' => null,
])
@endif

@if($usuario->puedeGestionar() && $cajerosConectados->count())
<div class="card" style="border-left:4px solid #2563eb">
    <div class="section-head">
        <h3>Cajeros en conexión</h3>
        <p>{{ $cajerosConectados->count() }} con sesión abierta en el POS</p>
    </div>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
        Registre el <strong>Cierre X</strong> para cada cajero (arqueo + cierre de sesión). Use <strong>Cerrar sesión</strong> solo si debe sacarlo del POS sin registrar arqueo.
    </p>
    @error('cierre_x_cajero')
    <p style="color:#b91c1c;font-size:.85rem;margin-bottom:.75rem">{{ $message }}</p>
    @enderror
    @error('cerrar_sesion')
    <p style="color:#b91c1c;font-size:.85rem;margin-bottom:.75rem">{{ $message }}</p>
    @enderror
    @foreach($cajerosConectados as $item)
    @php $c = $item['usuario']; $r = $item['resumen']; @endphp
    <div class="cajero-card">
        <div style="margin-bottom:.5rem">
            <strong>{{ $c->nombre }}</strong> <span style="color:#64748b">({{ $c->usuario }})</span><br>
            <small style="color:#64748b">Sesión desde {{ $c->sesion_inicio_en?->format('d/m/Y H:i') }}</small>
        </div>
        @include('app.cierres._resumen-dual', [
            'titulo' => 'Ventas del turno',
            'periodoDesde' => $r['fecha_desde'],
            'periodoHasta' => $r['fecha_hasta'],
            'tickets' => $r['cantidad_ventas'],
            'efectivo' => $r['total_efectivo'],
            'tarjeta' => $r['total_tarjeta'],
            'otros' => $r['total_otros'],
            'efectivoSistema' => $r['total_efectivo'],
            'efectivoContado' => null,
            'diferencia' => null,
            'compacto' => true,
        ])
        <div style="display:flex;flex-wrap:wrap;gap:1rem">
            <div style="flex:1;min-width:280px;border:1px solid #fde68a;border-radius:.5rem;padding:1rem;background:#fffbeb">
                <h4 style="margin:0 0 .5rem;font-size:.95rem">Cierre X del turno</h4>
                <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem">Cuente solo el efectivo físico en caja del cajero.</p>
                <form method="post" action="{{ route('cierres.cajero.cierre-x', $c) }}"
                      onsubmit="return confirm('¿Registrar cierre X de {{ $c->nombre }}? Se cerrará su sesión en el POS.')">
                    @csrf
                    <label style="font-size:.8rem;font-weight:600">Efectivo contado en arqueo (S/)</label>
                    <input type="number" name="monto_efectivo_contado" step="0.01" min="0" required
                           value="{{ $r['total_efectivo'] }}"
                           style="width:100%;padding:.45rem;margin:.25rem 0 .5rem;border:1px solid #cbd5e1;border-radius:.4rem">
                    <input name="observaciones" placeholder="Observaciones (opcional)"
                           style="width:100%;padding:.45rem;margin-bottom:.75rem;border:1px solid #cbd5e1;border-radius:.4rem">
                    <button type="submit" class="btn btn-warning" style="width:100%">Registrar cierre X</button>
                </form>
            </div>
            <div style="flex:1;min-width:220px;border:1px solid #bfdbfe;border-radius:.5rem;padding:1rem;background:#eff6ff">
                <h4 style="margin:0 0 .5rem;font-size:.95rem">Solo cerrar sesión</h4>
                <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem">
                    Expulsa al cajero del POS <em>sin</em> registrar arqueo ni Cierre X.
                </p>
                <form method="post" action="{{ route('cierres.cajero.cerrar-sesion', $c) }}"
                      onsubmit="return confirm('¿Cerrar sesión de {{ $c->nombre }}? El cajero deberá volver a iniciar sesión.')">
                    @csrf
                    <button type="submit" class="btn" style="width:100%;background:#2563eb;color:#fff">Cerrar sesión del cajero</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem">
    @if($usuario->rol === 'cajero')
    <div class="card" style="flex:1;min-width:300px;margin:0">
        <h3 style="margin-bottom:.75rem">Registrar mi Cierre X</h3>
        <form method="post" action="{{ route('cierres.x') }}" onsubmit="return confirm('¿Registrar cierre X? Se cerrará su sesión.')">
            @csrf
            <label style="font-weight:600;font-size:.85rem">Efectivo contado en arqueo (S/) *</label>
            <input type="number" name="monto_efectivo_contado" step="0.01" min="0" required
                   value="{{ old('monto_efectivo_contado', $resumen['total_efectivo']) }}"
                   style="width:100%;padding:.6rem;margin:.35rem 0 .75rem;border:1px solid #cbd5e1;border-radius:.4rem">
            <input name="observaciones" placeholder="Observaciones (opcional)" style="width:100%;padding:.5rem;margin-bottom:.75rem;border:1px solid #cbd5e1;border-radius:.4rem">
            <button type="submit" class="btn btn-warning" {{ $resumen['cantidad_ventas'] === 0 ? 'disabled' : '' }}>Registrar cierre X</button>
        </form>
    </div>
    @endif

    @if($usuario->puedeGestionar() && $resumenZ)
    <div class="card" style="flex:1;min-width:320px;margin:0;border-top:3px solid #dc2626">
        <div class="section-head">
            <h3>Cierre Z de hoy</h3>
            <p>Ventas de hoy · {{ $resumenZ['fecha_desde'] }} — {{ $resumenZ['fecha_hasta'] }}</p>
        </div>

        @if(($resumenZ['dias_pendientes'] ?? 0) > 0)
        <div style="background:#fff7ed;border:1px solid #fdba74;border-radius:.5rem;padding:.75rem;margin-bottom:1rem;font-size:.85rem;color:#92400e">
            <strong>Hay {{ $resumenZ['dias_pendientes'] }} día(s) anterior(es) sin cerrar.</strong>
            Debe cerrarlos primero en «Días sin cerrar» (abajo) antes de generar el Z de hoy.
            <a href="#jornadas-pendientes" style="display:inline-block;margin-top:.35rem;color:#b45309;font-weight:600">Ir a días sin cerrar ↓</a>
        </div>
        @endif

        @include('app.cierres._resumen-dual', [
            'titulo' => 'Ventas de hoy',
            'subtitulo' => $resumenZ['fecha_desde'].' — '.$resumenZ['fecha_hasta'],
            'tickets' => $resumenZ['cantidad_ventas'],
            'efectivo' => $resumenZ['total_efectivo'],
            'tarjeta' => $resumenZ['total_tarjeta'],
            'otros' => $resumenZ['total_otros'],
            'efectivoSistema' => $resumenZ['efectivo_sistema_arqueo'] ?? $resumenZ['totales_x']['total_efectivo'],
            'efectivoContado' => $resumenZ['total_contado'],
            'diferencia' => $resumenZ['total_diferencia'],
        ])

        @if($resumenZ['cierres_x']->count())
        <h4 style="font-size:.9rem;margin-bottom:.5rem">Cierres X de la jornada (orden cronológico)</h4>
        <p style="font-size:.78rem;color:#64748b;margin:-.25rem 0 .65rem">
            Las columnas de ventas suman tickets y montos por medio de pago. El arqueo compara solo el efectivo físico contado vs. las ventas en efectivo del sistema.
        </p>
        <div class="tabla-wrap">
        <table class="data" style="font-size:.82rem;margin-bottom:1rem">
            <thead>
                <tr>
                    <th colspan="4"></th>
                    <th colspan="4" class="grupo-th col-ventas">Ventas del turno</th>
                    <th colspan="3" class="grupo-th col-arqueo">Arqueo (solo efectivo)</th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Registro</th>
                    <th>Período del turno</th>
                    <th>Cajero</th>
                    <th class="col-money col-ventas">Tk</th>
                    <th class="col-money">Ef. ventas</th>
                    <th class="col-money">Tarjeta</th>
                    <th class="col-money">Otros</th>
                    <th class="col-money col-arqueo">Ef. sistema</th>
                    <th class="col-money">Ef. contado</th>
                    <th class="col-money">Dif.</th>
                </tr>
            </thead>
            <tbody>
                @php $tz = config('app.timezone', 'America/Lima'); $n = 0; @endphp
                @foreach($resumenZ['cierres_x'] as $cx)
                @php
                    $n++;
                    $ini = $cx->fecha_inicio->timezone($tz);
                    $fin = $cx->fecha_fin->timezone($tz);
                    $efSist = (float) $cx->total_efectivo;
                    $efCont = (float) ($cx->monto_efectivo_contado ?? 0);
                    $difCalc = round($efCont - $efSist, 2);
                    $tieneDif = abs($difCalc) > 0.001;
                @endphp
                <tr>
                    <td>{{ $n }}</td>
                    <td>{{ $fin->format('d/m/Y H:i') }}</td>
                    <td class="col-periodo">{{ $ini->format('d/m H:i') }} — {{ $fin->format('d/m H:i') }}</td>
                    <td>{{ $cx->usuario->nombre }}</td>
                    <td class="col-money col-ventas">{{ $cx->cantidad_ventas }}</td>
                    <td class="col-money">S/ {{ number_format($efSist, 2) }}</td>
                    <td class="col-money">S/ {{ number_format($cx->total_tarjeta, 2) }}</td>
                    <td class="col-money">S/ {{ number_format($cx->total_otros, 2) }}</td>
                    <td class="col-money col-arqueo">S/ {{ number_format($efSist, 2) }}</td>
                    <td class="col-money">S/ {{ number_format($efCont, 2) }}</td>
                    <td class="col-money" style="color:{{ $tieneDif ? '#dc2626' : 'inherit' }}">S/ {{ number_format($difCalc, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $tx = $resumenZ['totales_x'];
                    $efSistTot = (float) $tx['total_efectivo'];
                    $efContTot = (float) $resumenZ['total_contado'];
                    $difTot = round($efContTot - $efSistTot, 2);
                @endphp
                <tr style="font-weight:700;background:#f8fafc">
                    <td colspan="4">Total ventas ({{ $resumenZ['cantidad_cierres_x'] }} X)</td>
                    <td class="col-money col-ventas">{{ $tx['cantidad_ventas'] }}</td>
                    <td class="col-money">S/ {{ number_format($tx['total_efectivo'], 2) }}</td>
                    <td class="col-money">S/ {{ number_format($tx['total_tarjeta'], 2) }}</td>
                    <td class="col-money">S/ {{ number_format($tx['total_otros'], 2) }}</td>
                    <td class="col-money col-arqueo" colspan="3" style="text-align:left;font-weight:500;font-size:.78rem;color:#64748b">
                        Total ventas S/ {{ number_format($tx['total_ventas'], 2) }}
                    </td>
                </tr>
                <tr style="font-weight:700;background:#fffbeb">
                    <td colspan="8" style="text-align:right;font-size:.8rem;color:#92400e">Arqueo acumulado de los X</td>
                    <td class="col-money col-arqueo">S/ {{ number_format($efSistTot, 2) }}</td>
                    <td class="col-money">S/ {{ number_format($efContTot, 2) }}</td>
                    <td class="col-money" style="color:{{ abs($difTot) > 0.001 ? '#dc2626' : 'inherit' }}">S/ {{ number_format($difTot, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        </div>
        @else
        <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">Aún no hay cierres X en este período.</p>
        @endif

        @if($resumenZ['cantidad_cajeros_en_sesion'] > 0)
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:.5rem;padding:.75rem;margin-bottom:1rem;font-size:.85rem;color:#1e40af">
            Hay {{ $resumenZ['cantidad_cajeros_en_sesion'] }} cajero(s) con sesión abierta en el POS.
            Al pulsar <strong>Cerrar hoy</strong> se registrarán sus Cierre X automáticamente.
        </div>
        @endif
        @error('cierre_z')
        <p style="color:#b91c1c;font-size:.85rem;margin-bottom:.75rem">{{ $message }}</p>
        @enderror
        @php
            $sinActividad = $resumenZ['cantidad_ventas'] === 0;
            $bloqueadoPorDias = ($resumenZ['dias_pendientes'] ?? 0) > 0;
        @endphp
        <form method="post" action="{{ route('cierres.z') }}" onsubmit="return confirm('¿Cerrar el día de hoy?\n\nSe completarán los Cierre X pendientes y luego el Cierre Z.')">
            @csrf
            <input name="observaciones" placeholder="Observaciones (opcional)" style="width:100%;padding:.5rem;margin-bottom:.75rem;border:1px solid #cbd5e1;border-radius:.4rem"
                {{ ($sinActividad || $bloqueadoPorDias) ? 'disabled' : '' }}>
            <button type="submit" class="btn btn-danger"
                {{ ($sinActividad || $bloqueadoPorDias) ? 'disabled' : '' }}>
                Cerrar hoy (Cierre Z)
            </button>
        </form>
        <p class="tabla-leyenda">Cierra el día completo. Los Cierre X de cada vendedor se generan solos; usted solo confirma el Z.</p>
    </div>
    @endif
</div>

<div class="card" id="historial-cierres">
    <div class="section-head">
        <h3>Historial — últimos 5 días</h3>
        <p>Cierres X y Z ya registrados</p>
    </div>

    @forelse($historialPorDia as $bloque)
    <div class="dia-bloque {{ $bloque['es_hoy'] ? 'dia-bloque--hoy' : '' }}" id="dia-{{ $bloque['clave'] }}">
        <div class="dia-cabecera">
            <h4>
                {{ $bloque['etiqueta'] }}
                @if($bloque['es_hoy'])
                <span style="font-size:.72rem;background:#0f766e;color:#fff;padding:.15rem .45rem;border-radius:.25rem;margin-left:.35rem;vertical-align:middle">Hoy</span>
                @endif
            </h4>
            <div class="dia-resumen">
                <strong>{{ $bloque['totales']['registros'] }}</strong> cierre(s) ·
                <strong>{{ $bloque['totales']['tickets'] }}</strong> tickets ·
                Ef. <strong>S/ {{ number_format($bloque['totales']['efectivo'], 2) }}</strong> ·
                Tarjeta <strong>S/ {{ number_format($bloque['totales']['tarjeta'], 2) }}</strong> ·
                Otros <strong>S/ {{ number_format($bloque['totales']['otros'], 2) }}</strong>
                @if(abs($bloque['totales']['diferencia']) > 0.001)
                · dif. arqueo <strong style="color:#dc2626">S/ {{ number_format($bloque['totales']['diferencia'], 2) }}</strong>
                @endif
            </div>
        </div>
        <div class="tabla-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th colspan="5"></th>
                    <th colspan="4" class="grupo-th col-ventas">Ventas</th>
                    <th colspan="3" class="grupo-th col-arqueo">Arqueo efectivo</th>
                    <th></th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Registro</th>
                    <th>Tipo</th>
                    <th>Período</th>
                    <th>Cajero / responsable</th>
                    <th class="col-money col-ventas">Tk</th>
                    <th class="col-money">Ef. ventas</th>
                    <th class="col-money">Tarjeta</th>
                    <th class="col-money">Otros</th>
                    <th class="col-money col-arqueo">Ef. sistema</th>
                    <th class="col-money">Ef. contado</th>
                    <th class="col-money">Dif.</th>
                    <th class="col-accion"></th>
                </tr>
            </thead>
            <tbody>
                @php $num = 0; @endphp
                @foreach($bloque['cierres'] as $c)
                @php
                    $num++;
                    $efSist = (float) $c->total_efectivo;
                    $efCont = (float) ($c->monto_efectivo_contado ?? 0);
                    $difCalc = round($efCont - $efSist, 2);
                    $tieneDif = abs($difCalc) > 0.001;
                    $esReciente = (int) session('ultimo_cierre_id') === (int) $c->id;
                    $claseFila = trim(($esReciente ? 'fila-reciente ' : '').($tieneDif ? 'fila-diferencia' : ''));
                    $tz = config('app.timezone', 'America/Lima');
                    $fechaCierre = $c->fecha_fin->timezone($tz);
                    $fechaInicio = $c->fecha_inicio->timezone($tz);
                @endphp
                <tr @if($claseFila !== '') class="{{ $claseFila }}" @endif id="cierre-{{ $c->id }}">
                    <td>{{ $num }}</td>
                    <td>
                        <strong>{{ $fechaCierre->format('d/m/Y') }}</strong><br>
                        <span style="color:#64748b;font-size:.78rem">{{ $fechaCierre->format('H:i:s') }}</span>
                    </td>
                    <td>
                        <span class="badge-tipo badge-{{ strtolower($c->tipo_cierre) }}">{{ $c->tipo_cierre }}</span>
                    </td>
                    <td class="col-periodo">{{ $fechaInicio->format('d/m/Y H:i') }}<br>{{ $fechaCierre->format('d/m/Y H:i') }}</td>
                    <td>
                        {{ $c->usuario->nombre }}
                        @if($c->supervisor && $c->supervisor_usuario_id !== $c->usuario_id)
                        <br><span class="pill-supervisor">Supervisado por {{ $c->supervisor->nombre }}</span>
                        @endif
                        @if($esReciente)
                        <br><span class="pill-supervisor" style="background:#bbf7d0;color:#166534">Último registrado</span>
                        @endif
                    </td>
                    <td class="col-money col-ventas">{{ $c->cantidad_ventas }}</td>
                    <td class="col-money">S/ {{ number_format($efSist, 2) }}</td>
                    <td class="col-money">S/ {{ number_format($c->total_tarjeta, 2) }}</td>
                    <td class="col-money">S/ {{ number_format($c->total_otros, 2) }}</td>
                    <td class="col-money col-arqueo">S/ {{ number_format($efSist, 2) }}</td>
                    <td class="col-money">S/ {{ number_format($efCont, 2) }}</td>
                    <td class="col-money" style="color:{{ $tieneDif ? '#dc2626' : 'inherit' }};font-weight:{{ $tieneDif ? '700' : 'inherit' }}">
                        S/ {{ number_format($difCalc, 2) }}
                    </td>
                    <td class="col-accion">
                        <a href="{{ route('cierres.ticket', ['cierre' => $c, 'imprimir' => 1]) }}"
                           target="_blank" rel="noopener" class="btn-reimprimir">Reimprimir</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @empty
    <p style="color:#64748b;padding:1rem 0">Sin cierres registrados.</p>
    @endforelse

    <p class="tabla-leyenda">
        <strong>Ef. contado</strong> = billetes y monedas que el cajero contó en caja (no suma con tarjeta/Yape).
        <strong>Diferencia</strong> = efectivo contado − efectivo según ventas. Las ventas totales = Ef. ventas + Tarjeta + Otros.
    </p>
</div>

@if($esGestor)
@php $jpPag = $jornadasPendientesPag; @endphp
<div class="card" id="jornadas-pendientes" style="margin-top:1.25rem;border:2px solid #f59e0b;background:#fffbeb">
    <div class="section-head">
        <h3 style="color:#92400e">Días sin cerrar</h3>
        <p>Cierre el más antiguo primero · 5 por página</p>
    </div>
    <p style="font-size:.85rem;color:#78350f;margin:0 0 .75rem;line-height:1.45">
        Cada botón registra los Cierre X (efectivo según sistema) y el Cierre Z de ese día.
        Los Z no se solapan: un día, un cierre.
    </p>
    @if(session('ok'))
    <p style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:.75rem 1rem;border-radius:.5rem;margin:0 0 .75rem;font-size:.9rem">{{ session('ok') }}</p>
    @endif
    @error('jornada')
    <p style="color:#b91c1c;font-size:.85rem;margin:0 0 .75rem">{{ $message }}</p>
    @enderror
    @error('jornada_directa')
    <p style="color:#b91c1c;font-size:.85rem;margin:0 0 .75rem">{{ $message }}</p>
    @enderror
    @error('cierre_z')
    <p style="color:#b91c1c;font-size:.85rem;margin:0 0 .75rem">{{ $message }}</p>
    @enderror
    @if($jpPag && $jpPag['total'] > 0)
    <ul style="margin:0 0 .85rem;padding:0;list-style:none">
        @foreach($jpPag['items'] as $jp)
        @php $esSiguiente = ($jpPag['proximo'] ?? null) === $jp['fecha']; @endphp
        <li style="display:flex;flex-wrap:wrap;gap:.5rem 1rem;align-items:center;justify-content:space-between;padding:.65rem .5rem;border-top:1px solid #fde68a;{{ $esSiguiente ? 'background:#fef3c7;border-radius:.4rem' : '' }}">
            <span>
                <strong>{{ $jp['etiqueta'] }}</strong>
                @if($esSiguiente)
                <span style="font-size:.72rem;background:#d97706;color:#fff;padding:.12rem .4rem;border-radius:.25rem;margin-left:.25rem">Siguiente</span>
                @endif
                <br>
                <span style="font-size:.82rem;color:#78350f">
                    {{ $jp['ventas_dia_calendario']['cantidad_ventas'] }} ventas
                    · S/ {{ number_format($jp['ventas_dia_calendario']['total_ventas'], 2) }}
                </span>
            </span>
            <form method="post" action="{{ route('cierres.jornada.directo', $jp['fecha']) }}"
                onsubmit="return confirm('¿Cerrar el día {{ $jp['etiqueta'] }}?\n\nSe registrarán los X y el Z de ese día.')">
                @csrf
                <input type="hidden" name="jp" value="{{ $jpPag['page'] }}">
                <button type="submit" class="btn btn-danger" style="font-size:.82rem;padding:.45rem .9rem;white-space:nowrap"
                    @if(! $esSiguiente) disabled title="Primero cierre el día marcado como Siguiente" @endif>
                    Cerrar este día
                </button>
            </form>
        </li>
        @endforeach
    </ul>
    @if($jpPag['last_page'] > 1)
    <nav style="display:flex;flex-wrap:wrap;gap:.35rem;align-items:center;margin-bottom:.85rem;font-size:.82rem" aria-label="Paginación días sin cerrar">
        @if($jpPag['page'] > 1)
        <a href="{{ route('cierres.index', ['jp' => $jpPag['page'] - 1]) }}#jornadas-pendientes"
           style="padding:.35rem .65rem;border:1px solid #fdba74;border-radius:.35rem;text-decoration:none;color:#92400e;background:#fff">
            ← Anterior
        </a>
        @endif
        <span style="color:#78350f;padding:0 .25rem">
            Página {{ $jpPag['page'] }} de {{ $jpPag['last_page'] }} ({{ $jpPag['total'] }} día(s))
        </span>
        @if($jpPag['page'] < $jpPag['last_page'])
        <a href="{{ route('cierres.index', ['jp' => $jpPag['page'] + 1]) }}#jornadas-pendientes"
           style="padding:.35rem .65rem;border:1px solid #fdba74;border-radius:.35rem;text-decoration:none;color:#92400e;background:#fff">
            Siguiente →
        </a>
        @endif
    </nav>
    @endif
    @else
    <p style="font-size:.85rem;color:#065f46;margin:0 0 .75rem">
        ✓ No hay días anteriores pendientes. Puede cerrar hoy con el Cierre Z de arriba.
    </p>
    @endif
</div>
@endif
</div>
@endsection

@push('scripts')
@if($errors->has('jornada_directa') || $errors->has('jornada') || $errors->has('cierre_z') || session('ok'))
<script>
(function () {
    const bloque = document.getElementById('jornadas-pendientes');
    if (bloque) bloque.scrollIntoView({ behavior: 'smooth', block: 'start' });
})();
</script>
@endif
@if(session('ultimo_cierre_id'))
<script>
(function () {
    const fila = document.getElementById('cierre-{{ session('ultimo_cierre_id') }}');
    if (fila) {
        fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
})();
</script>
@endif
@endpush
