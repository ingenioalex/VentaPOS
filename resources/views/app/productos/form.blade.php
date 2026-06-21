@extends('layouts.app')

@section('titulo', $producto->exists ? 'Editar producto' : 'Nuevo producto')

@push('head')
<style>
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 800px) { .form-grid { grid-template-columns: 1fr; } }
    .form-group { margin-bottom: .85rem; }
    .form-group label { font-weight: 600; font-size: .85rem; display: block; margin-bottom: .3rem; }
    .form-group input, .form-group select { width: 100%; padding: .55rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
    .seccion-titulo { font-size: .95rem; font-weight: 700; color: #0f766e; margin: 1rem 0 .5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: .35rem; }
    .calc-box { background: #f0fdfa; padding: .75rem; border-radius: .5rem; font-size: .9rem; }
</style>
@endpush

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h1>{{ $producto->exists ? 'Editar producto' : 'Registro de producto' }}</h1>
    @if($producto->exists && auth()->user()->tieneModulo('inventario'))
    <a href="{{ route('inventario.kardex', $producto) }}" class="btn" style="background:#0f766e;color:#fff">Ver kardex</a>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:start">
    <div class="card" style="margin:0">
        <form method="post" action="{{ $producto->exists ? route('productos.actualizar', $producto) : route('productos.guardar') }}" id="form-producto">
            @csrf
            @if($producto->exists) @method('PUT') @endif
            @if(!empty($retorno))
            <input type="hidden" name="retorno" value="{{ $retorno }}">
            @endif

            <div class="seccion-titulo">Identificación</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>SKU</label>
                    <input name="sku" value="{{ old('sku', $producto->sku ?? ($siguienteSku ?? '')) }}" readonly style="background:#f8fafc">
                </div>
                <div class="form-group">
                    <label>Código de barra</label>
                    <input name="codigo_barra" maxlength="13" value="{{ old('codigo_barra', $producto->codigo_barra ?? ($prefillBarra ?? '')) }}" placeholder="EAN-13">
                </div>
            </div>
            <div class="form-group">
                <label>Descripción larga (nombre) *</label>
                <input name="nombre" required value="{{ old('nombre', $producto->nombre ?? ($prefillNombre ?? '')) }}" style="text-transform:uppercase">
            </div>
            <div class="form-group">
                <label>Descripción corta</label>
                <input name="campo2" maxlength="25" value="{{ old('campo2', $producto->campo2) }}" placeholder="Máx 25 caracteres">
            </div>

            <div class="seccion-titulo">Clasificación</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id">
                        <option value="">— Seleccione —</option>
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected(old('categoria_id', $producto->categoria_id) == $cat->id)>
                            @if(auth()->user()->empresa?->tieneExtra('categorias_jerarquicas') && $cat->parent)
                            {{ $cat->parent->nombre }} › {{ $cat->nombre }}
                            @else
                            {{ $cat->nombre }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Unidad medida</label>
                    <select name="unidad_medida">
                        @foreach(['UND','KG','PACK','CJA','LT'] as $um)
                        <option value="{{ $um }}" @selected(old('unidad_medida', $producto->unidad_medida ?? 'UND') === $um)>{{ $um }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Marca</label>
                    <input name="campo1" value="{{ old('campo1', $producto->campo1) }}" placeholder="Marca del producto">
                </div>
                <div class="form-group">
                    <label>Tipo producto</label>
                    <select name="campo3">
                        @foreach(['Mercadería','Insumo','Servicio'] as $tp)
                        <option value="{{ $tp }}" @selected(old('campo3', $producto->campo3 ?? 'Mercadería') === $tp)>{{ $tp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="seccion-titulo">Precios y stock</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Costo compra</label>
                    <input type="number" step="0.01" name="costo_compra" id="costo" value="{{ old('costo_compra', $producto->costo_compra ?? 0) }}">
                </div>
                <div class="form-group">
                    <label>Precio venta *</label>
                    <input type="number" step="0.01" name="precio_venta" id="precio" required value="{{ old('precio_venta', $producto->precio_venta ?? 0) }}">
                </div>
                <div class="form-group">
                    <label>Stock disponible</label>
                    <input type="number" step="0.01" name="stock_disponible" value="{{ old('stock_disponible', $producto->stock_disponible ?? 0) }}">
                    <small style="color:#64748b">Puede quedar negativo si hay físico pendiente de ingreso</small>
                </div>
                <div class="form-group">
                    <label>Stock mínimo</label>
                    <input type="number" step="0.01" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo ?? 0) }}">
                </div>
            </div>
            <div class="calc-box" style="margin-bottom:1rem">
                <div>IGV (18%): S/ <span id="lbl-igv">0.00</span></div>
                <div>Valor venta (sin IGV): S/ <span id="lbl-base">0.00</span></div>
                <div>Ganancia: S/ <span id="lbl-ganancia">0.00</span></div>
            </div>
            <label style="display:flex;align-items:center;gap:.4rem;margin-bottom:1rem">
                <input type="checkbox" name="afecto_igv" value="1" id="afecto_igv" @checked(old('afecto_igv', $producto->afecto_igv ?? true))>
                Afecto a IGV (18%)
            </label>
            @if($producto->exists)
            <label style="display:flex;align-items:center;gap:.4rem;margin-bottom:1rem">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $producto->activo))> Producto activo
            </label>
            @endif
            @if($lotesActivo ?? false)
            <label style="display:flex;align-items:center;gap:.4rem;margin-bottom:1rem">
                <input type="checkbox" name="control_lote" value="1" @checked(old('control_lote', $producto->control_lote ?? false))>
                Control por lote y vencimiento
            </label>
            @endif

            <button type="submit" class="btn btn-primary">Guardar producto</button>
            <a href="{{ ($retorno ?? '') === 'compras' ? route('compras.crear') : route('productos.index') }}" style="margin-left:.5rem">Cancelar</a>
        </form>
    </div>

    @if($producto->exists && ($lotesActivo ?? false) && isset($lotes) && $lotes->isNotEmpty())
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Lotes registrados</h3>
        <table class="data">
            <thead><tr><th>Lote</th><th>Vence</th><th>Cantidad</th><th>Ref.</th></tr></thead>
            <tbody>
                @foreach($lotes as $l)
                <tr>
                    <td>{{ $l->lote ?: '—' }}</td>
                    <td>{{ $l->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ number_format($l->cantidad, 2) }}</td>
                    <td style="font-size:.85rem;color:#64748b">{{ $l->referencia ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p style="font-size:.8rem;color:#64748b;margin-top:.75rem">Los lotes se crean al registrar entradas de inventario.</p>
    </div>
    @endif
    @if($producto->exists && isset($historial) && $historial->count())
    <div class="card" style="margin:0">
        <h3 style="margin-bottom:1rem">Historial de precios</h3>
        <table class="data">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Precio</th><th>Costo</th></tr></thead>
            <tbody>
                @foreach($historial as $h)
                <tr>
                    <td>{{ $h->creado_en->format('d/m/Y H:i') }}</td>
                    <td>{{ $h->usuario?->nombre ?? '—' }}</td>
                    <td>S/ {{ number_format($h->precio_anterior, 2) }} → {{ number_format($h->precio_nuevo, 2) }}</td>
                    <td>S/ {{ number_format($h->costo_anterior, 2) }} → {{ number_format($h->costo_nuevo, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const TASA = 0.18;
function calcular() {
    const precio = +document.getElementById('precio').value || 0;
    const costo = +document.getElementById('costo').value || 0;
    const afecto = document.getElementById('afecto_igv').checked;
    const base = afecto ? precio / (1 + TASA) : precio;
    const igv = afecto ? precio - base : 0;
    document.getElementById('lbl-igv').textContent = igv.toFixed(2);
    document.getElementById('lbl-base').textContent = base.toFixed(2);
    document.getElementById('lbl-ganancia').textContent = (base - costo).toFixed(2);
}
['precio','costo'].forEach(id => document.getElementById(id).addEventListener('input', calcular));
document.getElementById('afecto_igv').addEventListener('change', calcular);
calcular();
</script>
@endpush
