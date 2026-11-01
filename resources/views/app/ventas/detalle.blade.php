@extends('layouts.app')

@section('titulo', 'Detalle venta')

@section('contenido')
@include('app.ventas._styles')

<div class="ventas-page">
    <div class="detalle-hero">
        <div class="detalle-hero-top">
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
                <h1>{{ ucfirst($comprobante->tipo_documento) }} {{ $comprobante->numero_interno }}</h1>
                @include('app.ventas._badge-electronico', ['comprobante' => $comprobante])
            </div>
            <div class="ventas-actions">
                @if($comprobante->estado === 'emitido')
                <a href="{{ route('pos.ticket', $comprobante) }}" target="_blank">Imprimir ticket</a>
                @endif
                <a href="{{ route('ventas.index') }}">← Volver</a>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Fecha</span>
                <span class="meta-value">{{ $comprobante->emitido_en->format('d/m/Y H:i') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Cajero</span>
                <span class="meta-value">{{ $comprobante->usuario->nombre }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Cliente</span>
                <span class="meta-value">{{ $comprobante->cliente?->nombre ?? 'Consumidor final' }}</span>
                @if($comprobante->cliente)
                <small style="display:block;margin-top:.15rem;color:#c7d2fe;font-size:.75rem">
                    {{ strtoupper($comprobante->cliente->tipo_documento) }} {{ $comprobante->cliente->numero_documento }}
                </small>
                @endif
            </div>
            <div class="meta-item">
                <span class="meta-label">Medio de pago</span>
                <span class="meta-value">{{ $comprobante->etiquetaMedioPago() }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Estado</span>
                <span class="meta-value">{{ $comprobante->estado === 'anulado' ? 'Anulado' : 'Emitido' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Total</span>
                <span class="meta-value">S/ {{ number_format($comprobante->total, 2) }}</span>
            </div>
        </div>

        @if($comprobante->estado === 'emitido')
        @php $badgeCpe = $comprobante->badgeElectronico(); @endphp
        <div class="sunat-banner">
            <strong>Comprobante electrónico (SUNAT):</strong>
            <span class="cpe-badge {{ $badgeCpe['clase'] }}" title="{{ $badgeCpe['titulo'] }}">{{ $badgeCpe['texto'] }}</span>
            <span style="margin-left:.5rem">{{ $badgeCpe['titulo'] }}</span>
            @if($comprobante->sunat_mensaje)
            <br><small style="opacity:.85">{{ $comprobante->sunat_mensaje }}</small>
            @endif
            @if($comprobante->sunat_enviado_en)
            <br><small style="opacity:.85">Enviado: {{ $comprobante->sunat_enviado_en->format('d/m/Y H:i') }}</small>
            @endif
        </div>
        @endif

        @if($comprobante->estado === 'anulado')
        <div class="anulacion-box">
            <strong>Anulado:</strong> {{ $comprobante->anulado_en?->format('d/m/Y H:i') }}<br>
            <strong>Por:</strong> {{ $comprobante->anuladoPor?->nombre }}<br>
            <strong>Motivo:</strong> {{ $comprobante->motivo_anulacion }}
        </div>
        @endif
    </div>

    <div class="panel" style="padding:0">
        <div class="tabla-wrap">
            <table class="ventas-table">
                <thead>
                    <tr><th>Producto</th><th>Cant.</th><th>P. unit.</th><th>Desc%</th><th>Subtotal</th></tr>
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
    </div>

    @if($comprobante->estado === 'emitido' && auth()->user()->tieneModulo('sunat') && $comprobante->sunatRequiereAccion())
    <div class="panel panel-section">
        <h3>SUNAT</h3>
        @error('sunat')<p style="color:#991b1b;margin-bottom:.75rem">{{ $message }}</p>@enderror
        <form method="post" action="{{ route('sunat.reenviar', $comprobante) }}">
            @csrf
            <button type="submit" class="btn btn-accent">Reenviar a SUNAT</button>
            @if($comprobante->sunat_xml)
            · <a href="{{ route('ventas.xml', $comprobante) }}" class="link-action">Descargar XML</a>
            @endif
            @if($comprobante->sunat_cdr)
            · <a href="{{ route('ventas.cdr', $comprobante) }}" class="link-action">Descargar CDR</a>
            @endif
        </form>
    </div>
    @elseif($comprobante->sunat_xml && auth()->user()->tieneModulo('sunat'))
    <div class="panel panel-section">
        @if($comprobante->sunat_xml)
        <a href="{{ route('ventas.xml', $comprobante) }}" class="link-action">Descargar XML electrónico</a>
        @endif
        @if($comprobante->sunat_cdr)
        · <a href="{{ route('ventas.cdr', $comprobante) }}" class="link-action">Descargar CDR</a>
        @endif
    </div>
    @endif

    @if($comprobante->estado === 'emitido')
    <div class="panel panel-section" style="max-width:520px">
        <h3>Anular comprobante</h3>
        <p class="hint">Requiere autorización de dueño o supervisor. Restaura el stock automáticamente.</p>
        <form method="post" action="{{ route('ventas.anular', $comprobante) }}" onsubmit="return confirm('¿Anular este comprobante?')">
            @csrf
            <div class="form-field">
                <label>Usuario supervisor</label>
                <input name="supervisor_usuario" required>
            </div>
            <div class="form-field">
                <label>Contraseña supervisor</label>
                <input type="password" name="supervisor_password" required>
            </div>
            <div class="form-field">
                <label>Motivo *</label>
                <input name="motivo" required maxlength="255">
            </div>
            <button type="submit" class="btn btn-danger">Anular venta</button>
        </form>
    </div>
    @endif
</div>
@endsection
