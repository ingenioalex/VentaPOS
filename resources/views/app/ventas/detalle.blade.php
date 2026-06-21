@extends('layouts.app')

@section('titulo', 'Detalle venta')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
        <h1 style="margin:0">{{ ucfirst($comprobante->tipo_documento) }} {{ $comprobante->numero_interno }}</h1>
        @include('app.ventas._badge-electronico', ['comprobante' => $comprobante])
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
        @if($comprobante->estado === 'emitido')
        <a href="{{ route('pos.ticket', $comprobante) }}" target="_blank">Imprimir ticket</a>
        @endif
        <a href="{{ route('ventas.index') }}">← Volver</a>
    </div>
</div>

<div class="card">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;font-size:.9rem">
        <div><strong>Fecha:</strong> {{ $comprobante->emitido_en->format('d/m/Y H:i') }}</div>
        <div><strong>Cajero:</strong> {{ $comprobante->usuario->nombre }}</div>
        <div><strong>Cliente:</strong> {{ $comprobante->cliente?->nombre ?? 'Consumidor final' }}
            @if($comprobante->cliente)
            <br><small style="color:#64748b">{{ strtoupper($comprobante->cliente->tipo_documento) }} {{ $comprobante->cliente->numero_documento }}</small>
            @endif
        </div>
        <div><strong>Medio pago:</strong> {{ $comprobante->etiquetaMedioPago() }}</div>
        <div><strong>Estado:</strong>
            @if($comprobante->estado === 'anulado')
                <span style="color:#dc2626">Anulado</span>
            @else
                <span style="color:#065f46">Emitido</span>
            @endif
        </div>
        @if($comprobante->estado === 'emitido')
        @php $badgeCpe = $comprobante->badgeElectronico(); @endphp
        <div style="grid-column:1/-1;padding:.65rem .85rem;border-radius:.5rem;font-size:.88rem;
            @if($comprobante->esComprobanteElectronico()) background:#f0fdf4;border:1px solid #bbf7d0;
            @else background:#f8fafc;border:1px solid #e2e8f0; @endif">
            <strong>Comprobante electrónico (SUNAT):</strong>
            <span class="cpe-badge {{ $badgeCpe['clase'] }}" title="{{ $badgeCpe['titulo'] }}">{{ $badgeCpe['texto'] }}</span>
            <span style="margin-left:.5rem;color:#475569">{{ $badgeCpe['titulo'] }}</span>
            @if($comprobante->sunat_mensaje)
            <br><small style="color:#64748b">{{ $comprobante->sunat_mensaje }}</small>
            @endif
            @if($comprobante->sunat_enviado_en)
            <br><small style="color:#64748b">Enviado: {{ $comprobante->sunat_enviado_en->format('d/m/Y H:i') }}</small>
            @endif
        </div>
        @endif
    </div>

    @if($comprobante->estado === 'anulado')
    <div style="margin-top:1rem;padding:.75rem;background:#fee2e2;border-radius:.5rem;font-size:.9rem">
        <strong>Anulado:</strong> {{ $comprobante->anulado_en?->format('d/m/Y H:i') }}<br>
        <strong>Por:</strong> {{ $comprobante->anuladoPor?->nombre }}<br>
        <strong>Motivo:</strong> {{ $comprobante->motivo_anulacion }}
    </div>
    @endif
</div>

<div class="card">
    <table class="data">
        <thead>
            <tr><th>Producto</th><th>Cant.</th><th>P.Unit</th><th>Desc%</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($comprobante->detalles as $d)
            <tr>
                <td>{{ $d->descripcion_producto }}</td>
                <td>{{ $d->cantidad }}</td>
                <td>S/ {{ number_format($d->precio_unitario, 2) }}</td>
                <td>{{ $d->descuento_pct ?? 0 }}%</td>
                <td>S/ {{ number_format($d->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="4" style="text-align:right"><strong>Subtotal</strong></td><td>S/ {{ number_format($comprobante->subtotal, 2) }}</td></tr>
            <tr><td colspan="4" style="text-align:right">IGV</td><td>S/ {{ number_format($comprobante->valor_igv, 2) }}</td></tr>
            <tr><td colspan="4" style="text-align:right">Descuento</td><td>S/ {{ number_format($comprobante->descuento, 2) }}</td></tr>
            <tr><td colspan="4" style="text-align:right"><strong>TOTAL</strong></td><td><strong>S/ {{ number_format($comprobante->total, 2) }}</strong></td></tr>
        </tfoot>
    </table>
</div>

@if($comprobante->estado === 'emitido' && auth()->user()->tieneModulo('sunat') && $comprobante->sunatRequiereAccion())
<div class="card" style="max-width:480px">
    <h3 style="margin-bottom:.75rem">SUNAT</h3>
    @error('sunat')<p style="color:#991b1b;margin-bottom:.75rem">{{ $message }}</p>@enderror
    <form method="post" action="{{ route('sunat.reenviar', $comprobante) }}">
        @csrf
        <button type="submit" class="btn btn-primary">Reenviar a SUNAT</button>
        @if($comprobante->sunat_xml)
        · <a href="{{ route('sunat.xml', $comprobante) }}">Descargar XML</a>
        @endif
        @if($comprobante->sunat_cdr)
        · <a href="{{ route('sunat.cdr', $comprobante) }}">Descargar CDR</a>
        @endif
    </form>
</div>
@elseif($comprobante->sunat_xml && auth()->user()->tieneModulo('sunat'))
<div class="card" style="max-width:480px">
    @if($comprobante->sunat_xml)
    <a href="{{ route('sunat.xml', $comprobante) }}">Descargar XML electrónico</a>
    @endif
    @if($comprobante->sunat_cdr)
    · <a href="{{ route('sunat.cdr', $comprobante) }}">Descargar CDR</a>
    @endif
</div>
@endif

@if($comprobante->estado === 'emitido')
<div class="card" style="max-width:480px">
    <h3 style="margin-bottom:.75rem">Anular comprobante</h3>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">Requiere autorización de dueño o supervisor. Restaura el stock automáticamente.</p>
    <form method="post" action="{{ route('ventas.anular', $comprobante) }}" onsubmit="return confirm('¿Anular este comprobante?')">
        @csrf
        <div style="margin-bottom:.75rem">
            <label style="font-weight:600;font-size:.85rem">Usuario supervisor</label>
            <input name="supervisor_usuario" required style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.4rem">
        </div>
        <div style="margin-bottom:.75rem">
            <label style="font-weight:600;font-size:.85rem">Contraseña supervisor</label>
            <input type="password" name="supervisor_password" required style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.4rem">
        </div>
        <div style="margin-bottom:.75rem">
            <label style="font-weight:600;font-size:.85rem">Motivo *</label>
            <input name="motivo" required maxlength="255" style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.4rem">
        </div>
        <button type="submit" class="btn btn-danger">Anular venta</button>
    </form>
</div>
@endif
@endsection
