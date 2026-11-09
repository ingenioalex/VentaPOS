@extends('layouts.app')

@section('titulo', 'Reporte de inventario')

@section('contenido')
@include('app.reportes._inventario-styles')
@php
    $nombreTipo = [
        'entrada' => 'Entrada',
        'salida' => 'Salida',
        'venta' => 'Venta',
        'ajuste' => 'Ajuste',
        'compra' => 'Compra',
        'devolucion' => 'Devolución',
    ];
@endphp
<div class="rep-inv">
    <div class="rep-head">
        <div>
            <h1>Inventario</h1>
            <p class="rep-lead">Arriba está el stock de hoy. El filtro de fechas solo cambia los movimientos del periodo y no el valor del almacén.</p>
        </div>
        <div class="rep-exports">
            <a href="{{ route('reportes.exportar-stock') }}" class="rep-chip slate">Stock CSV</a>
            <a href="{{ route('reportes.exportar-stock-excel') }}" class="rep-chip teal">Stock Excel</a>
            <a href="{{ route('inventario.exportar-movimientos', ['desde' => $desde, 'hasta' => $hasta]) }}" class="rep-chip slate">Mov. CSV</a>
            <a href="{{ route('inventario.exportar-movimientos-excel', ['desde' => $desde, 'hasta' => $hasta]) }}" class="rep-chip blue">Mov. Excel</a>
        </div>
    </div>

    @include('app.reportes._nav')

    <div class="rep-section">
        <h2>Stock de hoy</h2>
        <p>Lo que hay ahora en almacén, sin importar las fechas.</p>
    </div>
    <div class="rep-kpis">
        <article class="rep-kpi tone-indigo">
            <span>Productos activos</span>
            <strong>{{ number_format($resumen['productos']) }}</strong>
            <small>Con venta habilitada</small>
        </article>
        <article class="rep-kpi tone-sky">
            <span>Unidades</span>
            <strong>{{ number_format($resumen['unidades'], 0) }}</strong>
            <small>Suma de todo el stock</small>
        </article>
        <article class="rep-kpi tone-teal">
            <span>Valor a costo</span>
            <strong>S/ {{ number_format($resumen['valor_costo'], 2) }}</strong>
            <small>Lo que costó comprarlo</small>
        </article>
        <article class="rep-kpi tone-green">
            <span>Valor a venta</span>
            <strong>S/ {{ number_format($resumen['valor_venta'], 2) }}</strong>
            <small>Si se vende al precio actual</small>
        </article>
        <article class="rep-kpi tone-amber {{ $resumen['stock_bajo'] > 0 ? 'alerta' : '' }}">
            <span>Stock bajo</span>
            <strong>{{ number_format($resumen['stock_bajo']) }}</strong>
            <small>En el mínimo o por debajo</small>
        </article>
        <article class="rep-kpi tone-red {{ $resumen['sin_stock'] > 0 ? 'alerta' : '' }}">
            <span>Sin stock</span>
            <strong>{{ number_format($resumen['sin_stock']) }}</strong>
            <small>En cero o en negativo</small>
        </article>
    </div>

    <div class="rep-section">
        <h2>Movimientos de este mes</h2>
        <p>Desde el día 1 hasta hoy. No usa el filtro de abajo.</p>
    </div>
    <div class="rep-kpis">
        <article class="rep-kpi tone-green">
            <span>Entradas</span>
            <strong>{{ number_format($resumen['entradas_mes'], 0) }}</strong>
            <small>Unidades que ingresaron</small>
        </article>
        <article class="rep-kpi tone-orange">
            <span>Salidas</span>
            <strong>{{ number_format($resumen['salidas_mes'], 0) }}</strong>
            <small>Unidades que salieron</small>
        </article>
        <article class="rep-kpi tone-blue">
            <span>Ventas</span>
            <strong>{{ number_format($resumen['ventas_mes'], 0) }}</strong>
            <small>Unidades vendidas</small>
        </article>
        <article class="rep-kpi tone-violet">
            <span>Ajustes</span>
            <strong>{{ number_format($resumen['ajustes_mes']) }}</strong>
            <small>Correcciones registradas</small>
        </article>
    </div>

    <form class="rep-filtros" method="get">
        <div>
            <label for="desde">Desde</label>
            <input id="desde" type="date" name="desde" value="{{ $desde }}">
        </div>
        <div>
            <label for="hasta">Hasta</label>
            <input id="hasta" type="date" name="hasta" value="{{ $hasta }}">
        </div>
        <div>
            <label for="q">Buscar producto</label>
            <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Nombre, SKU o código">
        </div>
        <div>
            <label for="stock_bajo">Qué productos ver</label>
            <select id="stock_bajo" name="stock_bajo">
                <option value="0" @selected(! request()->boolean('stock_bajo'))>Todos</option>
                <option value="1" @selected(request()->boolean('stock_bajo'))>Solo stock bajo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <div class="rep-layout">
        <section class="rep-panel">
            <h3>Movimientos del periodo</h3>
            <p class="hint">{{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}</p>
            <div class="rep-tipos">
                @forelse($porTipo as $t)
                <div class="rep-tipo">
                    <b>{{ $nombreTipo[$t->tipo_movimiento] ?? ucfirst($t->tipo_movimiento) }}</b>
                    <span>{{ number_format($t->movimientos) }} movimientos</span>
                    <strong>{{ number_format($t->unidades, 0) }}</strong>
                </div>
                @empty
                <p class="vacio">No hay movimientos en estas fechas.</p>
                @endforelse
            </div>
            <p class="rep-total">
                Lista de abajo: costo S/ {{ number_format($totalesStock['valor_costo'], 2) }}
                · venta S/ {{ number_format($totalesStock['valor_venta'], 2) }}
            </p>
        </section>

        <section class="rep-panel">
            <h3>Productos y su valor</h3>
            <p class="hint">Toca el nombre para ver el kardex. El valor es stock × precio.</p>
            <div class="rep-table-wrap">
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Valor costo</th>
                            <th>Valor venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stock as $i)
                        <tr>
                            <td class="sku" data-label="SKU">{{ $i->sku }}</td>
                            <td data-label="Producto">
                                <a class="prod-link" href="{{ route('inventario.kardex', $i->id) }}">{{ $i->nombre }}</a>
                                @if($i->stock <= 0)
                                    <span class="badge cero">Sin stock</span>
                                @elseif($i->bajo)
                                    <span class="badge bajo">Bajo</span>
                                @endif
                            </td>
                            <td class="num" data-label="Stock">{{ number_format($i->stock, 0) }}</td>
                            <td class="num" data-label="Valor costo">S/ {{ number_format($i->valor_costo, 2) }}</td>
                            <td class="num" data-label="Valor venta">S/ {{ number_format($i->valor_venta, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="vacio">Ningún producto coincide con el filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
