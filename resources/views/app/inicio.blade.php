@extends('layouts.app')

@section('titulo', 'Inicio')

@section('contenido')
<h1 style="margin-bottom:1.5rem">Panel — {{ $usuario->empresa?->nombre ?? 'Sistema' }}</h1>

@if($stockBajo > 0 && $usuario->tieneModulo('productos'))
<div class="alert-warning" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
    <span><strong>Alerta:</strong> {{ $stockBajo }} producto(s) con stock bajo o agotado.</span>
    <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="btn btn-warning" style="font-size:.85rem">Revisar inventario</a>
</div>
@endif

<div class="stats">
    <div class="stat"><span>Ventas hoy</span><strong>S/ {{ number_format($ventasHoy, 2) }}</strong></div>
    <div class="stat"><span>Tickets hoy</span><strong>{{ $totalVentasHoy }}</strong></div>
    <div class="stat" style="border-left-color:{{ $stockBajo > 0 ? '#d97706' : 'var(--primary)' }}">
        <span>Stock bajo</span>
        <strong style="color:{{ $stockBajo > 0 ? '#d97706' : 'inherit' }}">{{ $stockBajo }}</strong>
    </div>
</div>

@if($stockBajo > 0 && $usuario->tieneModulo('productos') && $productosStockBajo->isNotEmpty())
<div class="card" style="margin-top:1rem">
    <h3 style="margin-bottom:.75rem">Productos con stock crítico</h3>
    <table class="data">
        <thead>
            <tr><th>Producto</th><th>SKU</th><th>Categoría</th><th>Stock</th><th>Mínimo</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($productosStockBajo as $p)
            <tr>
                <td>{{ $p->nombre }}</td>
                <td style="font-family:monospace;font-size:.82rem">{{ $p->sku }}</td>
                <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                <td style="color:#d97706;font-weight:700">{{ $p->stock_disponible }}</td>
                <td>{{ $p->stock_minimo > 0 ? $p->stock_minimo : '—' }}</td>
                <td><a href="{{ route('productos.editar', $p) }}">Editar</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($stockBajo > $productosStockBajo->count())
    <p style="margin-top:.75rem;font-size:.85rem;color:#64748b">
        Mostrando {{ $productosStockBajo->count() }} de {{ $stockBajo }}.
        <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}">Ver todos</a>
    </p>
    @endif
</div>
@endif
<div class="card" style="margin-top:1.5rem">
    <h3 style="margin-bottom:1rem">Acciones rápidas</h3>
    @if($usuario->tieneModulo('pos'))
    <a href="{{ route('pos.index') }}" class="btn btn-primary">Abrir POS</a>
    @endif
    @if($usuario->tieneModulo('pos_movil'))
    <a href="{{ route('pos.movil') }}" class="btn" style="margin-left:.5rem;background:#0d9488;color:#fff">POS Móvil</a>
    @endif
    @if($usuario->tieneModulo('ventas'))
    <a href="{{ route('ventas.index') }}" class="btn" style="margin-left:.5rem;background:#475569;color:#fff">Ventas del día</a>
    @endif
    @if($usuario->tieneModulo('cierres'))
    <a href="{{ route('cierres.index') }}" class="btn btn-warning" style="margin-left:.5rem">Cierre de caja</a>
    @endif
    @if($usuario->tieneModulo('productos'))
    <a href="{{ route('productos.crear') }}" class="btn" style="margin-left:.5rem;background:#64748b;color:#fff">Nuevo producto</a>
    @endif
    @if($usuario->tieneModulo('inventario'))
    <a href="{{ route('inventario.index') }}" class="btn" style="margin-left:.5rem;background:#0f766e;color:#fff">Inventario</a>
    @endif
    @if($usuario->tieneModulo('reportes'))
    <a href="{{ route('reportes.index') }}" class="btn" style="margin-left:.5rem;background:#2563eb;color:#fff">Reportes</a>
    @endif
    <a href="{{ route('guia.index') }}" class="btn" style="margin-left:.5rem;background:#e2e8f0;color:#334155">Guía rápida</a>
</div>
@endsection
