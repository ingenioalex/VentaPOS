@extends('layouts.app')

@section('titulo', 'Kardex — '.$producto->nombre)

@section('contenido')
@php
    $stock = (float) $producto->stock_disponible;
    $stockFmt = fmod($stock, 1.0) === 0.0 ? number_format($stock, 0) : rtrim(rtrim(number_format($stock, 4, '.', ''), '0'), '.');
    $badgeClass = fn (string $tipo) => match ($tipo) {
        'entrada' => 'inv-badge--entrada',
        'salida' => 'inv-badge--salida',
        'ajuste' => 'inv-badge--ajuste',
        'venta' => 'inv-badge--venta',
        default => 'inv-badge--default',
    };
    $cantFmt = fn ($n) => fmod((float) $n, 1.0) === 0.0
        ? number_format((float) $n, 0)
        : rtrim(rtrim(number_format((float) $n, 4, '.', ''), '0'), '.');
@endphp
<div class="inv-page">
    @if(session('ok'))
    <p class="inv-alert-ok">{{ session('ok') }}</p>
    @endif

    <header class="inv-header">
        <div>
            <h1>Kardex</h1>
            <p class="inv-subtitle">
                <strong>{{ $producto->nombre }}</strong>
                · SKU {{ $producto->sku }}
                @if($producto->codigo_barra)
                · {{ $producto->codigo_barra }}
                @endif
            </p>
        </div>
        <div class="inv-header-actions">
            @include('app.inventario._acciones-movimiento', ['productoId' => $producto->id, 'simbolos' => true])
            <a href="{{ route('productos.editar', $producto) }}" class="btn-inv-ghost">Editar producto</a>
            <a href="{{ route('inventario.stock') }}" class="btn-inv-ghost">← Stock</a>
        </div>
    </header>

    <div class="inv-kpi-grid">
        <div class="inv-kpi inv-kpi--stock">
            <div class="inv-kpi-label">Stock actual</div>
            <div class="inv-kpi-value">{{ $stockFmt }}</div>
        </div>
        <div class="inv-kpi">
            <div class="inv-kpi-label">Movimientos</div>
            <div class="inv-kpi-value">{{ $movimientos->total() }}</div>
        </div>
        <div class="inv-kpi">
            <div class="inv-kpi-label">Período</div>
            <div class="inv-kpi-value" style="font-size:.95rem">{{ \Carbon\Carbon::parse($desde)->format('d/m/y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/y') }}</div>
        </div>
    </div>

    @include('app.inventario._nav')

    <form class="inv-filtros" method="get">
        <div>
            <label for="desde">Desde</label>
            <input type="date" id="desde" name="desde" value="{{ $desde }}">
        </div>
        <div>
            <label for="hasta">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="{{ $hasta }}">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:.5rem 1rem">Filtrar</button>
    </form>

    <div class="inv-panel">
        <div class="inv-panel-head">Historial de movimientos</div>

        <div class="inv-table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th class="col-num">Cant.</th>
                        <th class="col-num">Stock ant.</th>
                        <th class="col-num">Stock nuevo</th>
                        <th>Usuario</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $m)
                    <tr>
                        <td>{{ $m->creado_en?->format('d/m/Y H:i') }}</td>
                        <td><span class="inv-badge {{ $badgeClass($m->tipo_movimiento) }}">{{ ucfirst($m->tipo_movimiento) }}</span></td>
                        <td class="col-num">{{ $cantFmt($m->cantidad) }}</td>
                        <td class="col-num">{{ $cantFmt($m->stock_anterior) }}</td>
                        <td class="col-num"><strong>{{ $cantFmt($m->stock_nuevo) }}</strong></td>
                        <td>{{ $m->usuario?->nombre ?? '—' }}</td>
                        <td style="color:#64748b;font-size:.82rem">{{ $m->referencia ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="inv-empty">Sin movimientos para este producto en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="inv-cards">
            @forelse($movimientos as $m)
            <article class="inv-card-mov">
                <div class="inv-card-mov-top">
                    <span class="inv-card-mov-fecha">{{ $m->creado_en?->format('d/m/Y H:i') }}</span>
                    <span class="inv-badge {{ $badgeClass($m->tipo_movimiento) }}">{{ ucfirst($m->tipo_movimiento) }}</span>
                </div>
                <dl class="inv-card-mov-grid">
                    <div><dt>Cantidad</dt><dd>{{ $cantFmt($m->cantidad) }}</dd></div>
                    <div><dt>Stock nuevo</dt><dd>{{ $cantFmt($m->stock_nuevo) }}</dd></div>
                    <div><dt>Stock ant.</dt><dd>{{ $cantFmt($m->stock_anterior) }}</dd></div>
                    <div><dt>Usuario</dt><dd>{{ $m->usuario?->nombre ?? '—' }}</dd></div>
                </dl>
                @if($m->referencia)
                <p style="margin:.45rem 0 0;font-size:.78rem;color:#64748b">{{ $m->referencia }}</p>
                @endif
            </article>
            @empty
            <p class="inv-empty">Sin movimientos para este producto en el período.</p>
            @endforelse
        </div>
    </div>

    @if($movimientos->hasPages())
    <div style="margin-top:1rem">{{ $movimientos->links() }}</div>
    @endif
</div>
@endsection
