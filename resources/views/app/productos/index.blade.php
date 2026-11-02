@extends('layouts.app')

@section('titulo', 'Productos')

@section('contenido')
@include('app.productos._styles')

<div class="productos-page">
    <div class="prod-header">
        <div>
            <h1>Productos</h1>
            <p class="prod-subtitle">Catálogo, precios, stock y categorías de tu negocio</p>
        </div>
        <div class="prod-actions">
            @if(auth()->user()->tieneModulo('categorias'))
            <a href="{{ route('categorias.index') }}" class="btn btn-cat">Categorías</a>
            @endif
            <a href="{{ route('productos.importar') }}" class="btn btn-import">Importar CSV</a>
            <a href="{{ route('productos.crear') }}" class="btn btn-new">+ Nuevo producto</a>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <span class="kpi-label">Total en catálogo</span>
            <strong class="kpi-value">{{ number_format($resumen['total']) }}</strong>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Productos activos</span>
            <strong class="kpi-value">{{ number_format($resumen['activos']) }}</strong>
        </div>
        <div class="kpi-card {{ $resumen['stock_bajo'] > 0 ? 'kpi-warn' : '' }}">
            <span class="kpi-label">Stock bajo / agotado</span>
            <strong class="kpi-value">{{ number_format($resumen['stock_bajo']) }}</strong>
        </div>
    </div>

    @if($stockBajoCount > 0)
    <div class="alert-stock">
        <span><strong>{{ $stockBajoCount }}</strong> producto(s) con stock bajo o agotado requieren atención.</span>
        <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="btn btn-warning" style="font-size:.85rem">Ver alertas</a>
    </div>
    @endif

    <div class="panel">
        <div class="panel-title">Filtros y búsqueda</div>
        <form class="filtros-productos" method="get">
            <div class="campo-buscar">
                <label>Buscar (nombre, barra, SKU)</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Escriba para buscar...">
            </div>
            <div>
                <label>Alertas</label>
                <select name="stock_bajo">
                    <option value="0" @selected(! $soloStockBajo)>Todos</option>
                    <option value="1" @selected($soloStockBajo)>Solo stock bajo</option>
                </select>
            </div>
            @if($jerarquicas ?? false)
            <div>
                <label>Categoría / subcategoría</label>
                <select name="categoria_id">
                    <option value="">Todas</option>
                    @foreach($categoriasFiltro->whereNull('parent_id') as $padre)
                        @php $hijos = $categoriasFiltro->where('parent_id', $padre->id); @endphp
                        @if($hijos->isNotEmpty())
                    <optgroup label="{{ $padre->nombre }}">
                        <option value="{{ $padre->id }}" @selected(($categoriaId ?? null) === $padre->id)>{{ $padre->nombre }} (principal)</option>
                        @foreach($hijos as $hijo)
                        <option value="{{ $hijo->id }}" @selected(($categoriaId ?? null) === $hijo->id)>↳ {{ $hijo->nombre }}</option>
                        @endforeach
                    </optgroup>
                        @else
                    <option value="{{ $padre->id }}" @selected(($categoriaId ?? null) === $padre->id)>{{ $padre->nombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label>Ordenar por</label>
                <select name="orden">
                    <option value="categoria" @selected(($orden ?? 'categoria') === 'categoria')>Categoría (predeterminado)</option>
                    <option value="nombre_asc" @selected(($orden ?? '') === 'nombre_asc')>Nombre A → Z</option>
                    <option value="nombre_desc" @selected(($orden ?? '') === 'nombre_desc')>Nombre Z → A</option>
                    <option value="sku_asc" @selected(($orden ?? '') === 'sku_asc')>SKU menor → mayor</option>
                    <option value="sku_desc" @selected(($orden ?? '') === 'sku_desc')>SKU mayor → menor</option>
                    <option value="barra_asc" @selected(($orden ?? '') === 'barra_asc')>Cód. barra A → Z</option>
                    <option value="barra_desc" @selected(($orden ?? '') === 'barra_desc')>Cód. barra Z → A</option>
                    <option value="categoria_asc" @selected(($orden ?? '') === 'categoria_asc')>Categoría A → Z</option>
                    <option value="categoria_desc" @selected(($orden ?? '') === 'categoria_desc')>Categoría Z → A</option>
                    @if($jerarquicas ?? false)
                    <option value="subcategoria_asc" @selected(($orden ?? '') === 'subcategoria_asc')>Subcategoría A → Z</option>
                    <option value="subcategoria_desc" @selected(($orden ?? '') === 'subcategoria_desc')>Subcategoría Z → A</option>
                    @endif
                    <option value="precio_asc" @selected(($orden ?? '') === 'precio_asc')>Precio menor → mayor</option>
                    <option value="precio_desc" @selected(($orden ?? '') === 'precio_desc')>Precio mayor → menor</option>
                    <option value="costo_asc" @selected(($orden ?? '') === 'costo_asc')>Costo menor → mayor</option>
                    <option value="costo_desc" @selected(($orden ?? '') === 'costo_desc')>Costo mayor → menor</option>
                    <option value="stock_asc" @selected(($orden ?? '') === 'stock_asc')>Stock menor → mayor</option>
                    <option value="stock_desc" @selected(($orden ?? '') === 'stock_desc')>Stock mayor → menor</option>
                    <option value="um_asc" @selected(($orden ?? '') === 'um_asc')>U.M. A → Z</option>
                    <option value="um_desc" @selected(($orden ?? '') === 'um_desc')>U.M. Z → A</option>
                    <option value="estado_asc" @selected(($orden ?? '') === 'estado_asc')>Estado inactivo → activo</option>
                    <option value="estado_desc" @selected(($orden ?? '') === 'estado_desc')>Estado activo → inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-search">Buscar</button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-title">Listado · {{ number_format($productos->total()) }} resultado(s)</div>
        <div class="tabla-wrap">
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Cód. barra</th>
                        <th class="col-nombre">Nombre</th>
                        @if($jerarquicas ?? false)
                        <th>Categoría</th>
                        <th>Subcategoría</th>
                        @else
                        <th>Categoría</th>
                        @endif
                        <th class="col-center">U.M.</th>
                        <th class="col-num">Costo</th>
                        <th class="col-num">Precio</th>
                        <th class="col-num">Stock</th>
                        <th class="col-num">Mín.</th>
                        <th class="col-center">Estado</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $p)
                    <tr>
                        <td class="mono">{{ $p->sku }}</td>
                        <td class="mono">{{ $p->codigo_barra ?? '—' }}</td>
                        <td class="prod-nombre">{{ $p->nombre }}</td>
                        @if($jerarquicas ?? false)
                        <td>
                            @if($p->categoria?->parent)
                            <span class="txt-cat">{{ $p->categoria->parent->nombre }}</span>
                            @elseif($p->categoria)
                            <span class="txt-cat">{{ $p->categoria->nombre }}</span>
                            @else
                            —
                            @endif
                        </td>
                        <td>
                            @if($p->categoria?->parent_id)
                            <span class="txt-sub">{{ $p->categoria->nombre }}</span>
                            @else
                            <span style="color:#94a3b8">—</span>
                            @endif
                        </td>
                        @else
                        <td>
                            @if($p->categoria)
                            <span class="txt-cat">{{ $p->categoria->nombre }}</span>
                            @else
                            —
                            @endif
                        </td>
                        @endif
                        <td class="col-center txt-um">{{ $p->unidad_medida }}</td>
                        <td class="col-num precio-costo">S/ {{ number_format($p->costo_compra, 2) }}</td>
                        <td class="col-num precio-venta">S/ {{ number_format($p->precio_venta, 2) }}</td>
                        <td class="col-num {{ $p->stock_disponible < 0 ? 'stock-neg' : ($p->tieneStockBajo() ? 'stock-bajo' : 'stock-ok') }}">
                            {{ $p->stock_disponible }}
                            @if($p->tieneStockBajo())
                            <span title="Stock bajo" style="font-size:.72rem">⚠</span>
                            @endif
                        </td>
                        <td class="col-num" style="color:#64748b">{{ $p->stock_minimo > 0 ? $p->stock_minimo : '—' }}</td>
                        <td class="col-center">
                            <span class="txt-estado {{ $p->activo ? 'activo' : 'inactivo' }}">
                                {{ $p->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="col-acciones">
                            <a href="{{ route('productos.editar', $p) }}" class="link-action">Editar</a>
                            @if(auth()->user()->tieneModulo('inventario'))
                            · <a href="{{ route('inventario.kardex', $p) }}" class="link-action link-kardex">Kardex</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row"><td colspan="{{ ($jerarquicas ?? false) ? 12 : 11 }}">Sin productos que coincidan con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $productos->links() }}</div>
    </div>
</div>
@endsection
