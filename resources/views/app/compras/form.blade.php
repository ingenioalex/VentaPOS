@extends('layouts.app')

@section('titulo', 'Nueva compra')

@push('head')
<style>
    .buscar-producto-wrap { position: relative; margin-bottom: .75rem; }
    .buscar-producto-wrap input { width: 100%; padding: .65rem .75rem; border: 1px solid #cbd5e1; border-radius: .5rem; font-size: 1rem; }
    .buscar-producto-wrap input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
    #resultados-producto {
        position: absolute; left: 0; right: 0; top: 100%; z-index: 20;
        background: #fff; border: 1px solid #cbd5e1; border-radius: .5rem;
        max-height: 240px; overflow: auto; box-shadow: 0 8px 24px rgba(0,0,0,.1);
        display: none;
    }
    #resultados-producto.activo { display: block; }
    #resultados-producto .item {
        padding: .65rem .75rem; cursor: pointer; border-bottom: 1px solid #f1f5f9;
    }
    #resultados-producto .item:hover { background: #f0f9ff; }
    #resultados-producto .item small { color: #64748b; display: block; margin-top: .15rem; }
    #sin-resultados {
        padding: .75rem; color: #64748b; font-size: .9rem; display: none;
        background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: .5rem; margin-bottom: .75rem;
    }
    #sin-resultados.activo { display: block; }
    .buscar-hint { font-size: .82rem; color: #64748b; margin-top: .35rem; }
</style>
@endpush

@section('contenido')
@include('app.compras._nav')

<h1 style="margin-bottom:1rem">Registrar compra</h1>

@if(session('ok'))
<p style="padding:.65rem .85rem;background:#d1fae5;color:#065f46;border-radius:.5rem;margin-bottom:1rem">{{ session('ok') }}</p>
@endif

@if($proveedores->isEmpty())
<div class="card" style="max-width:560px">
    <p style="color:#64748b;margin-bottom:1rem">Debe registrar al menos un proveedor antes de ingresar compras.</p>
    <a href="{{ route('proveedores.crear') }}" class="btn btn-primary">+ Nuevo proveedor</a>
</div>
@else
<div class="card">
    <form method="post" action="{{ route('compras.guardar') }}" id="form-compra">
        @csrf

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem">
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Proveedor *</label>
                <select name="proveedor_id" required style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    <option value="">Seleccione...</option>
                    @foreach($proveedores as $p)
                    <option value="{{ $p->id }}" @selected(old('proveedor_id') == $p->id)>{{ $p->razon_social }} ({{ $p->numero_documento }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Tipo documento *</label>
                <select name="tipo_documento" required style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    @foreach(['factura' => 'Factura proveedor', 'guia' => 'Guía de remisión', 'orden' => 'Orden de compra'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('tipo_documento', 'factura') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Serie</label>
                <input name="serie" maxlength="10" value="{{ old('serie') }}" placeholder="Ej. F001"
                       style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Número *</label>
                <input name="numero_documento" required maxlength="20" value="{{ old('numero_documento') }}"
                       style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Fecha emisión *</label>
                <input type="date" name="fecha_emision" required value="{{ old('fecha_emision', today()->toDateString()) }}"
                       style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Fecha recepción</label>
                <input type="date" name="fecha_recepcion" value="{{ old('fecha_recepcion', today()->toDateString()) }}"
                       style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Observaciones</label>
            <input name="observaciones" maxlength="500" value="{{ old('observaciones') }}"
                   style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <h3 style="margin:1.25rem 0 .75rem">Detalle de productos</h3>
        @error('lineas')<p style="color:#991b1b;margin-bottom:.75rem">{{ $message }}</p>@enderror

        <div class="buscar-producto-wrap">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Buscar producto</label>
            <input type="text" id="buscar-producto" autocomplete="off"
                   placeholder="Escanee EAN / código de barra o escriba nombre (Enter)">
            <p class="buscar-hint">Escriba al menos 2 letras o escanee el código y pulse Enter.</p>
            <div id="resultados-producto"></div>
        </div>

        <div id="sin-resultados">
            <span id="sin-resultados-texto">No se encontró el producto.</span>
            @if($puedeCrearProducto)
            <a href="#" id="btn-crear-producto" class="btn btn-primary" style="margin-top:.5rem;display:inline-block">+ Crear producto nuevo</a>
            @else
            <span style="display:block;margin-top:.35rem;font-size:.85rem">Solicite al administrador que registre el producto en el catálogo.</span>
            @endif
        </div>

        <div style="overflow:auto">
            <table class="data" id="tabla-lineas">
                <thead>
                    <tr><th>Producto</th><th>Código</th><th>Cantidad</th><th>Costo unit.</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody id="lineas-body">
                    <tr id="fila-vacia"><td colspan="6" style="color:#64748b">Sin líneas — busque o escanee un producto arriba</td></tr>
                </tbody>
                <tfoot>
                    <tr><td colspan="4" style="text-align:right">Subtotal</td><td id="lbl-subtotal">S/ 0.00</td><td></td></tr>
                    <tr><td colspan="4" style="text-align:right">IGV (18%)</td><td id="lbl-igv">S/ 0.00</td><td></td></tr>
                    <tr><td colspan="4" style="text-align:right"><strong>Total</strong></td><td id="lbl-total"><strong>S/ 0.00</strong></td><td></td></tr>
                </tfoot>
            </table>
        </div>
        <p style="font-size:.85rem;color:#64748b;margin-top:.5rem">El IGV se calcula sobre productos afectos según catálogo.</p>

        <div style="margin-top:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem">
            <button type="submit" name="recibir" value="0" class="btn" style="background:#e2e8f0;color:#334155">Guardar borrador</button>
            <button type="submit" name="recibir" value="1" class="btn btn-primary">Registrar y recibir stock</button>
            <a href="{{ route('compras.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endif
@endsection

@push('scripts')
<script>
const TASA_IGV = 0.18;
const URL_BUSCAR = @json(route('compras.buscar-productos'));
const URL_EXACTO = @json(route('compras.buscar-exacto'));
const URL_CREAR_PRODUCTO = @json(route('productos.crear'));
const puedeCrearProducto = @json($puedeCrearProducto);

const productosMap = {};
const tbody = document.getElementById('lineas-body');
const buscar = document.getElementById('buscar-producto');
const resultados = document.getElementById('resultados-producto');
const sinResultados = document.getElementById('sin-resultados');
const sinResultadosTexto = document.getElementById('sin-resultados-texto');
const btnCrearProducto = document.getElementById('btn-crear-producto');
let idx = 0;
let timer = null;
let ultimaBusqueda = '';

function esc(s) { return String(s ?? '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
function fmt(n) { return 'S/ ' + n.toFixed(2); }

function registrarEnMap(p) {
    productosMap[p.id] = {
        nombre: p.nombre,
        barra: p.codigo_barra || p.sku || '—',
        costo: +p.costo_compra || 0,
        igv: !!p.afecto_igv,
    };
}

function ocultarResultados() {
    resultados.classList.remove('activo');
    resultados.innerHTML = '';
}

function mostrarSinResultados(q) {
    ultimaBusqueda = q;
    sinResultadosTexto.textContent = 'No se encontró ningún producto para «' + q + '».';
    if (btnCrearProducto && puedeCrearProducto) {
        const params = new URLSearchParams({ retorno: 'compras' });
        if (/^\d+$/.test(q)) params.set('barra', q);
        else params.set('nombre', q);
        btnCrearProducto.href = URL_CREAR_PRODUCTO.split('?')[0] + '?' + params.toString();
    }
    sinResultados.classList.add('activo');
}

function ocultarSinResultados() {
    sinResultados.classList.remove('activo');
}

function recalcular() {
    let sub = 0, igv = 0;
    tbody.querySelectorAll('tr[data-idx]').forEach(row => {
        const cant = +row.querySelector('.inp-cant').value || 0;
        const costo = +row.querySelector('.inp-costo').value || 0;
        const pid = +row.dataset.productoId;
        const lineSub = cant * costo;
        sub += lineSub;
        if (productosMap[pid]?.igv) igv += lineSub * TASA_IGV;
        row.querySelector('.lbl-line-sub').textContent = fmt(lineSub);
    });
    document.getElementById('lbl-subtotal').textContent = fmt(sub);
    document.getElementById('lbl-igv').textContent = fmt(igv);
    document.getElementById('lbl-total').innerHTML = '<strong>' + fmt(sub + igv) + '</strong>';
    document.getElementById('fila-vacia')?.remove();
    if (!tbody.querySelector('tr[data-idx]')) {
        tbody.innerHTML = '<tr id="fila-vacia"><td colspan="6" style="color:#64748b">Sin líneas — busque o escanee un producto arriba</td></tr>';
    }
}

function agregarLinea(p, cantidad = 1, costo = null) {
    if (!p || !p.id) return false;
    registrarEnMap(p);
    const existente = tbody.querySelector(`tr[data-producto-id="${p.id}"]`);
    if (existente) {
        existente.querySelector('.inp-cant').value = (+existente.querySelector('.inp-cant').value || 0) + cantidad;
        recalcular();
        return true;
    }
    document.getElementById('fila-vacia')?.remove();
    const i = idx++;
    const info = productosMap[p.id];
    const c = costo ?? info.costo ?? 0;
    const tr = document.createElement('tr');
    tr.dataset.idx = i;
    tr.dataset.productoId = p.id;
    tr.innerHTML = `
        <td>${esc(info.nombre)}<input type="hidden" name="lineas[${i}][producto_id]" value="${p.id}"></td>
        <td style="font-family:monospace;font-size:.85rem">${esc(info.barra)}</td>
        <td><input type="number" class="inp-cant" name="lineas[${i}][cantidad]" value="${cantidad}" min="0.01" step="0.01" required style="width:90px;padding:.4rem"></td>
        <td><input type="number" class="inp-costo" name="lineas[${i}][costo_unitario]" value="${c}" min="0" step="0.01" required style="width:100px;padding:.4rem"></td>
        <td class="lbl-line-sub">${fmt(cantidad * c)}</td>
        <td><button type="button" class="btn-quitar" style="border:none;background:none;color:#dc2626;cursor:pointer">✕</button></td>`;
    tr.querySelector('.inp-cant').oninput = recalcular;
    tr.querySelector('.inp-costo').oninput = recalcular;
    tr.querySelector('.btn-quitar').onclick = () => { tr.remove(); recalcular(); };
    tbody.appendChild(tr);
    recalcular();
    return true;
}

function renderResultados(items) {
    if (!items.length) {
        ocultarResultados();
        return;
    }
    ocultarSinResultados();
    resultados.innerHTML = items.map(p => {
        const barra = p.codigo_barra || p.sku || '—';
        return `<div class="item">
            <strong>${esc(p.nombre)}</strong>
            <small>${esc(barra)} · Costo ref. S/ ${(+p.costo_compra).toFixed(2)}</small>
        </div>`;
    }).join('');
    resultados.classList.add('activo');
    resultados.querySelectorAll('.item').forEach((el, i) => {
        el.onclick = () => {
            agregarLinea(items[i]);
            buscar.value = '';
            ocultarResultados();
            buscar.focus();
        };
    });
}

async function buscarExacto(q) {
    const res = await fetch(`${URL_EXACTO}?q=${encodeURIComponent(q)}`);
    const p = await res.json();
    if (p && p.id) {
        agregarLinea(p);
        buscar.value = '';
        ocultarResultados();
        ocultarSinResultados();
        buscar.focus();
        return true;
    }
    return false;
}

async function buscarTexto(q) {
    const res = await fetch(`${URL_BUSCAR}?q=${encodeURIComponent(q)}`);
    const items = await res.json();
    if (items.length === 1) {
        agregarLinea(items[0]);
        buscar.value = '';
        ocultarResultados();
        ocultarSinResultados();
        buscar.focus();
        return;
    }
    if (items.length > 1) {
        renderResultados(items);
        return;
    }
    mostrarSinResultados(q);
    ocultarResultados();
}

buscar?.addEventListener('input', () => {
    clearTimeout(timer);
    ocultarSinResultados();
    const q = buscar.value.trim();
    if (q.length < 2) {
        ocultarResultados();
        return;
    }
    timer = setTimeout(() => buscarTexto(q), 280);
});

buscar?.addEventListener('keydown', async e => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const q = buscar.value.trim();
    if (!q) return;
    if (await buscarExacto(q)) return;
    if (q.length >= 2) await buscarTexto(q);
    else mostrarSinResultados(q);
});

document.addEventListener('click', e => {
    if (!e.target.closest('.buscar-producto-wrap')) ocultarResultados();
});

document.getElementById('form-compra')?.addEventListener('submit', e => {
    if (!tbody.querySelector('tr[data-idx]')) {
        e.preventDefault();
        alert('Agregue al menos un producto.');
    }
});

@foreach(old('lineas', []) as $linea)
@php $p = $productosRestaurar->get((int) ($linea['producto_id'] ?? 0)); @endphp
@if($p)
agregarLinea(@json($p), {{ (float) ($linea['cantidad'] ?? 1) }}, {{ (float) ($linea['costo_unitario'] ?? $p->costo_compra) }});
@endif
@endforeach

@if($productoNuevo)
agregarLinea(@json($productoNuevo));
@endif
</script>
@endpush
