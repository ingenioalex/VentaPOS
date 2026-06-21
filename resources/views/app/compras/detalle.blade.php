@extends('layouts.app')

@section('titulo', 'Detalle compra')

@section('contenido')
@include('app.compras._nav')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h1>{{ $compra->etiquetaDocumento() }}</h1>
    <a href="{{ route('compras.index') }}">← Volver</a>
</div>

<div class="card">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;font-size:.9rem">
        <div><strong>Proveedor:</strong> {{ $compra->proveedor->razon_social }}<br>
            <small style="color:#64748b">{{ $compra->proveedor->etiquetaDocumento() }}</small>
        </div>
        <div><strong>Fecha emisión:</strong> {{ $compra->fecha_emision->format('d/m/Y') }}</div>
        <div><strong>Fecha recepción:</strong> {{ $compra->fecha_recepcion?->format('d/m/Y') ?? '—' }}</div>
        <div><strong>Registró:</strong> {{ $compra->usuario->nombre }}</div>
        <div><strong>Estado:</strong>
            @if($compra->estado === 'anulado')
                <span style="color:#dc2626">Anulado</span>
            @elseif($compra->estado === 'borrador')
                <span style="color:#92400e">Borrador</span>
            @else
                <span style="color:#065f46">Recibido</span>
            @endif
        </div>
    </div>

    @if($compra->observaciones)
    <p style="margin-top:1rem;font-size:.9rem"><strong>Observaciones:</strong> {{ $compra->observaciones }}</p>
    @endif

    @if($compra->estado === 'anulado')
    <div style="margin-top:1rem;padding:.75rem;background:#fee2e2;border-radius:.5rem;font-size:.9rem">
        <strong>Anulado:</strong> {{ $compra->anulado_en?->format('d/m/Y H:i') }}<br>
        <strong>Por:</strong> {{ $compra->anuladoPor?->nombre }}<br>
        <strong>Motivo:</strong> {{ $compra->motivo_anulacion }}
    </div>
    @endif
</div>

<div class="card">
    <table class="data">
        <thead>
            <tr><th>Producto</th><th>Cant.</th><th>Costo unit.</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($compra->detalles as $d)
            <tr>
                <td>{{ $d->descripcion }}</td>
                <td>{{ $d->cantidad }}</td>
                <td>S/ {{ number_format($d->costo_unitario, 4) }}</td>
                <td>S/ {{ number_format($d->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3" style="text-align:right"><strong>Subtotal</strong></td><td>S/ {{ number_format($compra->subtotal, 2) }}</td></tr>
            <tr><td colspan="3" style="text-align:right">IGV</td><td>S/ {{ number_format($compra->valor_igv, 2) }}</td></tr>
            <tr><td colspan="3" style="text-align:right"><strong>TOTAL</strong></td><td><strong>S/ {{ number_format($compra->total, 2) }}</strong></td></tr>
        </tfoot>
    </table>
</div>

@if($compra->estado === 'borrador')
<div class="card" style="max-width:480px">
    <h3 style="margin-bottom:.75rem">Recibir mercadería</h3>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">Ingresará el stock al inventario y actualizará el costo de compra de cada producto.</p>
    @error('recibir')<p style="color:#991b1b;margin-bottom:.75rem">{{ $message }}</p>@enderror
    <form method="post" action="{{ route('compras.recibir', $compra) }}" onsubmit="return confirm('¿Recibir esta compra e ingresar stock?')">
        @csrf
        <button type="submit" class="btn btn-primary">Recibir compra</button>
    </form>
</div>
@endif

@if($compra->estado !== 'anulado')
<div class="card" style="max-width:480px">
    <h3 style="margin-bottom:.75rem">Anular compra</h3>
    @if($compra->estado === 'recibido')
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">Revertirá las entradas de stock registradas por esta compra.</p>
    @endif
    <form method="post" action="{{ route('compras.anular', $compra) }}" onsubmit="return confirm('¿Anular esta compra?')">
        @csrf
        <div style="margin-bottom:.75rem">
            <label style="font-weight:600;font-size:.85rem">Motivo *</label>
            <input name="motivo" required maxlength="255" style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.4rem">
            @error('motivo')<small style="color:#991b1b">{{ $message }}</small>@enderror
        </div>
        <button type="submit" class="btn btn-danger">Anular</button>
    </form>
</div>
@endif
@endsection
