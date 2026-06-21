@extends('layouts.app')

@section('titulo', 'Cierre de caja')

@section('contenido')
<h1 style="margin-bottom:.5rem">Cierre de caja</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">
    Empresa #{{ $resumen['empresa_id'] }} · Terminal {{ $resumen['terminal'] }} · Caja {{ $resumen['caja'] }} · Turno desde {{ $resumen['fecha_desde'] }}
</p>

<div class="stats" style="margin-bottom:1.5rem">
    <div class="stat"><span>Total turno</span><strong>S/ {{ number_format($resumen['total_ventas'], 2) }}</strong></div>
    <div class="stat"><span>Tickets</span><strong>{{ $resumen['cantidad_ventas'] }}</strong></div>
    <div class="stat"><span>Efectivo sistema</span><strong>S/ {{ number_format($resumen['total_efectivo'], 2) }}</strong></div>
    <div class="stat"><span>Tarjeta</span><strong>S/ {{ number_format($resumen['total_tarjeta'], 2) }}</strong></div>
    <div class="stat"><span>Otros</span><strong>S/ {{ number_format($resumen['total_otros'], 2) }}</strong></div>
</div>

@if($usuario->puedeGestionar() && $cajerosConectados->count())
<div class="card" style="margin-bottom:1.5rem;border-left:4px solid #2563eb">
    <h3 style="margin-bottom:.75rem">Cajeros en conexión</h3>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
        Registre el cierre X del turno y, por separado, cierre la sesión del cajero cuando corresponda.
    </p>
    @error('cierre_x_cajero')
    <p style="color:#b91c1c;font-size:.85rem;margin-bottom:.75rem">{{ $message }}</p>
    @enderror
    @error('cerrar_sesion')
    <p style="color:#b91c1c;font-size:.85rem;margin-bottom:.75rem">{{ $message }}</p>
    @enderror
    @foreach($cajerosConectados as $item)
    @php $c = $item['usuario']; $r = $item['resumen']; @endphp
    <div style="border:1px solid #e2e8f0;border-radius:.5rem;padding:1rem;margin-bottom:.75rem">
        <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;margin-bottom:1rem">
            <div>
                <strong>{{ $c->nombre }}</strong> ({{ $c->usuario }})<br>
                <small style="color:#64748b">Sesión desde {{ $c->sesion_inicio_en?->format('d/m/Y H:i') }}</small>
            </div>
            <div><span style="color:#64748b">Tickets:</span> <strong>{{ $r['cantidad_ventas'] }}</strong></div>
            <div><span style="color:#64748b">Total:</span> <strong>S/ {{ number_format($r['total_ventas'], 2) }}</strong></div>
            <div><span style="color:#64748b">Efectivo sistema:</span> <strong>S/ {{ number_format($r['total_efectivo'], 2) }}</strong></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:1rem">
            <div style="flex:1;min-width:280px;border:1px solid #fde68a;border-radius:.5rem;padding:1rem;background:#fffbeb">
                <h4 style="margin:0 0 .5rem;font-size:.95rem">Cierre X del turno</h4>
                <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem">Cuente el efectivo e imprima el ticket de cierre.</p>
                <form method="post" action="{{ route('cierres.cajero.cierre-x', $c) }}"
                      onsubmit="return confirm('¿Registrar cierre X de {{ $c->nombre }}?')">
                    @csrf
                    <label style="font-size:.8rem;font-weight:600">Efectivo contado S/</label>
                    <input type="number" name="monto_efectivo_contado" step="0.01" min="0" required
                           value="{{ $r['total_efectivo'] }}"
                           style="width:100%;padding:.45rem;margin:.25rem 0 .5rem;border:1px solid #cbd5e1;border-radius:.4rem">
                    <input name="observaciones" placeholder="Observaciones (opcional)"
                           style="width:100%;padding:.45rem;margin-bottom:.75rem;border:1px solid #cbd5e1;border-radius:.4rem">
                    <button type="submit" class="btn btn-warning" style="width:100%">Cierre X e imprimir</button>
                </form>
            </div>
            <div style="flex:1;min-width:220px;border:1px solid #bfdbfe;border-radius:.5rem;padding:1rem;background:#eff6ff">
                <h4 style="margin:0 0 .5rem;font-size:.95rem">Cerrar sesión</h4>
                <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem">
                    Finaliza la sesión activa del cajero en el POS. No registra cierre X.
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
        <h3 style="margin-bottom:.75rem">Cierre X — turno cajero</h3>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">Cuente el efectivo en caja e ingrese el monto real. Se imprimirá ticket de cierre.</p>
        <form method="post" action="{{ route('cierres.x') }}" onsubmit="return confirm('¿Registrar cierre X?')">
            @csrf
            <label style="font-weight:600;font-size:.85rem">Efectivo contado (S/) *</label>
            <input type="number" name="monto_efectivo_contado" step="0.01" min="0" required
                   value="{{ old('monto_efectivo_contado', $resumen['total_efectivo']) }}"
                   style="width:100%;padding:.6rem;margin:.35rem 0 .75rem;border:1px solid #cbd5e1;border-radius:.4rem">
            <input name="observaciones" placeholder="Observaciones (opcional)" style="width:100%;padding:.5rem;margin-bottom:.75rem;border:1px solid #cbd5e1;border-radius:.4rem">
            <button type="submit" class="btn btn-warning" {{ $resumen['cantidad_ventas'] === 0 ? 'disabled' : '' }}>Cierre X e imprimir</button>
        </form>
        @if($resumen['cantidad_ventas'] > 0)
        <p style="font-size:.8rem;color:#64748b;margin-top:.5rem">Sugerido: S/ {{ number_format($resumen['total_efectivo'], 2) }} (según ventas en efectivo)</p>
        @endif
    </div>
    @endif

    @if($usuario->puedeGestionar() && $resumenZ)
    <div class="card" style="flex:1;min-width:300px;margin:0">
        <h3 style="margin-bottom:.75rem">Cierre Z — reporte del día</h3>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:.5rem">Desde: {{ $resumenZ['fecha_desde'] }}</p>
        <div class="stats" style="margin-bottom:1rem;grid-template-columns:repeat(2,1fr)">
            <div class="stat"><span>Ventas totales</span><strong>{{ $resumenZ['cantidad_ventas'] }}</strong></div>
            <div class="stat"><span>Cajeros con cierre X</span><strong>{{ $resumenZ['cantidad_cajeros'] }}</strong></div>
            <div class="stat"><span>Total vendido</span><strong>S/ {{ number_format($resumenZ['total_ventas'], 2) }}</strong></div>
            <div class="stat"><span>Efectivo sistema</span><strong>S/ {{ number_format($resumenZ['total_efectivo'], 2) }}</strong></div>
            <div class="stat"><span>Efectivo contado (X)</span><strong>S/ {{ number_format($resumenZ['total_contado'], 2) }}</strong></div>
            <div class="stat"><span>Diferencia total</span><strong style="color:{{ $resumenZ['total_diferencia'] != 0 ? '#dc2626' : 'inherit' }}">S/ {{ number_format($resumenZ['total_diferencia'], 2) }}</strong></div>
        </div>
        @if($resumenZ['por_cajero']->count())
        <table class="data" style="font-size:.82rem;margin-bottom:1rem">
            <thead>
                <tr><th>Cajero</th><th>Cierres X</th><th>Tickets</th><th>Contado</th><th>Dif.</th></tr>
            </thead>
            <tbody>
                @foreach($resumenZ['por_cajero'] as $fila)
                <tr>
                    <td>{{ $fila['cajero']->nombre }}</td>
                    <td>{{ $fila['cantidad_cierres'] }}</td>
                    <td>{{ $fila['cantidad_ventas'] }}</td>
                    <td>S/ {{ number_format($fila['monto_contado'], 2) }}</td>
                    <td style="color:{{ $fila['diferencia'] != 0 ? '#dc2626' : 'inherit' }}">S/ {{ number_format($fila['diferencia'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">Aún no hay cierres X de cajeros en este período.</p>
        @endif
        @if($resumenZ['cantidad_cajeros_en_sesion'] > 0)
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;padding:.75rem;margin-bottom:1rem;font-size:.85rem;color:#991b1b">
            <strong>Cierre Z bloqueado.</strong> Hay {{ $resumenZ['cantidad_cajeros_en_sesion'] }} cajero(s) con sesión abierta sin Cierre X:
            <ul style="margin:.5rem 0 0 1rem">
                @foreach($resumenZ['cajeros_en_sesion'] as $c)
                <li>{{ $c->nombre }} ({{ $c->usuario }}) — desde {{ $c->sesion_inicio_en?->format('H:i') }}</li>
                @endforeach
            </ul>
            <p style="margin:.5rem 0 0">Cierre la sesión de cada cajero en la sección superior antes de generar el Cierre Z.</p>
        </div>
        @endif
        @error('cierre_z')
        <p style="color:#b91c1c;font-size:.85rem;margin-bottom:.75rem">{{ $message }}</p>
        @enderror
        @php
            $sinActividad = $resumenZ['cantidad_ventas'] === 0 && $resumenZ['cantidad_cierres_x'] === 0;
            $bloqueadoPorCajeros = ! $resumenZ['puede_cerrar_z'];
        @endphp
        <form method="post" action="{{ route('cierres.z') }}" onsubmit="return confirm('¿Generar e imprimir el cierre Z del día?')">
            @csrf
            <input name="observaciones" placeholder="Observaciones (opcional)" style="width:100%;padding:.5rem;margin-bottom:.75rem;border:1px solid #cbd5e1;border-radius:.4rem"
                {{ ($sinActividad || $bloqueadoPorCajeros) ? 'disabled' : '' }}>
            <button type="submit" class="btn btn-danger"
                {{ ($sinActividad || $bloqueadoPorCajeros) ? 'disabled' : '' }}>
                Generar Cierre Z e imprimir
            </button>
        </form>
        <p style="font-size:.8rem;color:#64748b;margin-top:.5rem">Consolida ventas y cierres X de todos los cajeros. Requiere que ningún cajero tenga sesión abierta.</p>
    </div>
    @endif
</div>

<div class="card">
    <h3 style="margin-bottom:1rem">Historial de cierres</h3>
    <table class="data">
        <thead>
            <tr><th>Fecha</th><th>Tipo</th><th>Cajero</th><th>Contado</th><th>Dif.</th><th>Total</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($historial as $c)
            <tr>
                <td>{{ $c->fecha_fin->format('d/m/Y H:i') }}</td>
                <td><strong>{{ $c->tipo_cierre }}</strong></td>
                <td>{{ $c->usuario->nombre }}</td>
                <td>S/ {{ number_format($c->monto_efectivo_contado ?? 0, 2) }}</td>
                <td style="color:{{ ($c->diferencia_efectivo ?? 0) != 0 ? '#dc2626' : 'inherit' }}">
                    S/ {{ number_format($c->diferencia_efectivo ?? 0, 2) }}
                </td>
                <td>S/ {{ number_format($c->total_ventas, 2) }}</td>
                <td><a href="{{ route('cierres.ticket', $c) }}" target="_blank">Ticket</a></td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#64748b">Sin cierres registrados</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
@if(session('ticket_url'))
<script>
window.open(@json(session('ticket_url')), '_blank');
</script>
@endif
@endpush
