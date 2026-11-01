@extends('layouts.app')

@section('titulo', 'Inicio')

@section('contenido')
@include('app.inicio._styles')

<div class="inicio-page">
    <div class="inicio-hero">
        <h1>Panel — {{ $usuario->empresa?->nombre ?? 'Sistema' }}</h1>
        <p>Resumen del día · {{ now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</p>
    </div>

    @if($stockBajo > 0 && $usuario->tieneModulo('productos'))
    <div class="alert-panel">
        <span><strong>Alerta de stock:</strong> {{ $stockBajo }} producto(s) con stock bajo o agotado.</span>
        <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="btn btn-warn">Revisar productos</a>
    </div>
    @endif

    <div class="kpi-grid">
        <div class="kpi-card kpi-sales">
            <span class="kpi-icon">💰</span>
            <span class="kpi-label">Ventas hoy</span>
            <strong class="kpi-value">S/ {{ number_format($ventasHoy, 2) }}</strong>
        </div>
        <div class="kpi-card">
            <span class="kpi-icon">🧾</span>
            <span class="kpi-label">Tickets hoy</span>
            <strong class="kpi-value">{{ $totalVentasHoy }}</strong>
        </div>
        <div class="kpi-card {{ $stockBajo > 0 ? 'kpi-warn' : '' }}">
            <span class="kpi-icon">📦</span>
            <span class="kpi-label">Stock bajo</span>
            <strong class="kpi-value">{{ $stockBajo }}</strong>
        </div>
    </div>

    <div class="panel">
        <div class="panel-title">Acciones rápidas</div>
        <div class="acciones-grid">
            @if($usuario->tieneModulo('pos'))
            <a href="{{ route('pos.index') }}" class="accion-card accion-pos">
                <div class="accion-icon">🛒</div>
                <strong>Abrir POS</strong>
                <span>Punto de venta principal</span>
            </a>
            @endif
            @if($usuario->tieneModulo('pos_movil'))
            <a href="{{ route('pos.movil') }}" class="accion-card accion-pos">
                <div class="accion-icon">📱</div>
                <strong>POS Móvil</strong>
                <span>Vender desde celular</span>
            </a>
            @endif
            @if($usuario->tieneModulo('ventas'))
            <a href="{{ route('ventas.index') }}" class="accion-card accion-ventas">
                <div class="accion-icon">📊</div>
                <strong>Ventas del día</strong>
                <span>Comprobantes y KPIs</span>
            </a>
            @endif
            @if($usuario->tieneModulo('cierres'))
            <a href="{{ route('cierres.index') }}" class="accion-card accion-cierres">
                <div class="accion-icon">🏦</div>
                <strong>Cierre de caja</strong>
                <span>Cierre X y Z</span>
            </a>
            @endif
            @if($usuario->tieneModulo('productos'))
            <a href="{{ route('productos.crear') }}" class="accion-card accion-catalogo">
                <div class="accion-icon">➕</div>
                <strong>Nuevo producto</strong>
                <span>Agregar al catálogo</span>
            </a>
            @endif
            @if($usuario->tieneModulo('inventario'))
            <a href="{{ route('inventario.index') }}" class="accion-card accion-catalogo">
                <div class="accion-icon">📋</div>
                <strong>Inventario</strong>
                <span>Stock y movimientos</span>
            </a>
            @endif
            @if($usuario->tieneModulo('reportes'))
            <a href="{{ route('reportes.index') }}" class="accion-card accion-reportes">
                <div class="accion-icon">📈</div>
                <strong>R. Reportes</strong>
                <span>Análisis y exportación</span>
            </a>
            @endif
            <a href="{{ route('guia.index') }}" class="accion-card accion-guia">
                <div class="accion-icon">📖</div>
                <strong>Guía rápida</strong>
                <span>Ayuda del sistema</span>
            </a>
        </div>
    </div>

    @if($stockBajo > 0 && $usuario->tieneModulo('productos') && $productosStockBajo->isNotEmpty())
    <div class="panel">
        <div class="panel-title">Productos con stock crítico</div>
        <div class="tabla-wrap">
            <table class="inicio-table">
                <thead>
                    <tr><th>Producto</th><th>SKU</th><th>Categoría</th><th>Stock</th><th>Mínimo</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($productosStockBajo as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td style="font-family:ui-monospace,monospace;font-size:.82rem">{{ $p->sku }}</td>
                        <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                        <td class="stock-critico">{{ $p->stock_disponible }}</td>
                        <td>{{ $p->stock_minimo > 0 ? $p->stock_minimo : '—' }}</td>
                        <td><a href="{{ route('productos.editar', $p) }}" class="link-accent">Editar</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($stockBajo > $productosStockBajo->count())
        <p style="margin-top:.85rem;font-size:.85rem;color:var(--i-muted)">
            Mostrando {{ $productosStockBajo->count() }} de {{ $stockBajo }}.
            <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="link-accent">Ver todos</a>
        </p>
        @endif
    </div>
    @endif
</div>
@endsection
