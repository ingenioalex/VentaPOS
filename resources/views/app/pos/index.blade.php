@extends('layouts.app')

@section('titulo', 'POS Venta')

@push('head')
<style>
    .pos-header { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; margin-bottom: 1rem; }
    .pos-badge { background: #0f766e; color: #fff; padding: .35rem .75rem; border-radius: .4rem; font-size: .85rem; font-weight: 600; }
    .pos-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
    .pos-actions button { padding: .6rem 1rem; border: none; border-radius: .5rem; font-weight: 700; cursor: pointer; font-size: .85rem; }
    .btn-iniciar { background: #16a34a; color: #fff; }
    .btn-suspender { background: #d97706; color: #fff; }
    .btn-recuperar { background: #2563eb; color: #fff; }
    .btn-cancelar { background: #dc2626; color: #fff; }
    .pos-layout { display: grid; grid-template-columns: 1fr 380px; gap: 1rem; }
    @media (max-width: 1100px) { .pos-layout { grid-template-columns: 1fr; } }
    .pos-left { display: flex; flex-direction: column; gap: .75rem; }
    #buscar { flex: 1; padding: .85rem; font-size: 1.05rem; border: 2px solid #0f766e; border-radius: .5rem; }
    .buscar-row { display: flex; gap: .5rem; align-items: stretch; }
    .btn-escanear { padding: 0 1rem; border: 2px solid #0f766e; background: #f0fdfa; color: #0f766e; border-radius: .5rem; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .btn-escanear:hover { background: #ccfbf1; }
    .tabs { display: flex; flex-wrap: wrap; gap: .4rem; }
    .tab { padding: .45rem .85rem; border: 1px solid #cbd5e1; background: #fff; border-radius: .4rem; cursor: pointer; font-size: .85rem; }
    .tab.activo { background: #0f766e; color: #fff; border-color: #0f766e; }
    .grid-productos { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: .5rem; max-height: 320px; overflow-y: auto; }
    .prod-card { border: 1px solid #e2e8f0; border-radius: .5rem; padding: .6rem; cursor: pointer; background: #fff; transition: .15s; }
    .prod-card:hover { border-color: #0f766e; background: #f0fdfa; }
    .prod-card strong { display: block; font-size: .82rem; line-height: 1.2; margin-bottom: .25rem; }
    .prod-barra { display: block; font-family: ui-monospace, monospace; font-size: .72rem; color: #475569; margin-bottom: .2rem; }
    .prod-card small { color: #64748b; }
    .grid-titulo { font-size: .85rem; color: #64748b; margin-bottom: .5rem; font-weight: 600; }
    #resultados div { padding: .55rem; border-bottom: 1px solid #e2e8f0; cursor: pointer; }
    #resultados div:hover { background: #f0fdfa; }
    .panel-derecho { display: flex; flex-direction: column; gap: .75rem; }
    .cliente-row { display: flex; gap: .4rem; }
    .cliente-row input { flex: 1; padding: .5rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
    #carrito table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    #carrito th, #carrito td { padding: .35rem .25rem; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .qty-btn { width: 22px; height: 22px; border: 1px solid #cbd5e1; background: #f8fafc; cursor: pointer; border-radius: 3px; }
    .totales { font-size: .9rem; line-height: 1.7; }
    .total-grande { font-size: 1.6rem; font-weight: 800; color: #0f766e; }
    .pago-row { display: flex; gap: .5rem; margin-bottom: .5rem; }
    .pago-row input, .pago-row select { flex: 1; padding: .5rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
    #btn-cobrar { width: 100%; padding: .85rem; font-size: 1rem; background: #0f766e; color: #fff; border: none; border-radius: .5rem; font-weight: 800; cursor: pointer; }
    #btn-cobrar:hover { background: #115e59; }
    .atajos { font-size: .75rem; color: #64748b; margin-top: .5rem; }
    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 100; align-items: center; justify-content: center; }
    .modal.activo { display: flex; }
    .modal-box { background: #fff; padding: 1.25rem; border-radius: .75rem; width: 90%; max-width: 420px; }
    .hold-item { padding: .6rem; border: 1px solid #e2e8f0; border-radius: .4rem; margin-bottom: .4rem; cursor: pointer; }
    .hold-item:hover { background: #f0fdfa; }
    .tipo-doc { display: flex; gap: .4rem; margin-bottom: .6rem; }
    .tipo-doc button { flex: 1; padding: .5rem; border: 2px solid #cbd5e1; background: #fff; border-radius: .4rem; font-weight: 700; cursor: pointer; }
    .tipo-doc button.activo { border-color: #0f766e; background: #f0fdfa; color: #0f766e; }
    .cliente-estado { font-size: .8rem; color: #64748b; margin-top: .5rem; padding: .4rem .5rem; background: #f8fafc; border-radius: .35rem; }
    .cliente-estado.ok { color: #065f46; background: #f0fdf4; }
    .cliente-acciones { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .5rem; }
    .cliente-acciones button { padding: .4rem .65rem; border: none; border-radius: .35rem; font-size: .8rem; font-weight: 600; cursor: pointer; }
    .btn-guardar-cli { background: #2563eb; color: #fff; }
    .btn-sin-dni { background: #e2e8f0; color: #334155; }
    .mixto-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem; margin-bottom: .5rem; }
    .mixto-grid label { font-size: .75rem; color: #64748b; display: block; margin-bottom: .15rem; }
    .mixto-grid input { width: 100%; padding: .45rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
    .mixto-total { font-size: .8rem; color: #64748b; margin-bottom: .5rem; }
    .mixto-total.ok { color: #065f46; }
    .mixto-total.err { color: #991b1b; }
    #qr-reader { width: 100%; border-radius: .5rem; overflow: hidden; }
    .modal-scanner { max-width: 480px; }
    #carrito table { table-layout: fixed; }
    #carrito th { white-space: nowrap; }
    #carrito td { overflow-wrap: anywhere; }
    /* Detalle venta: en móvil cada línea se muestra como tarjeta para evitar columnas comprimidas */
    @media (max-width: 640px) {
        #carrito table, #carrito tbody, #carrito tr, #carrito td { display: block; width: 100%; }
        #carrito thead { display: none; }
        #carrito tr {
            display: grid;
            grid-template-columns: 1fr auto;
            grid-template-areas:
                "nombre quitar"
                "barra barra"
                "cant sub"
                "desc sub";
            gap: .35rem .5rem;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            padding: .55rem .65rem;
            margin-bottom: .5rem;
        }
        #carrito td { border: none; padding: 0; overflow-wrap: anywhere; }
        #carrito .c-nombre { grid-area: nombre; font-weight: 700; font-size: .92rem; line-height: 1.25; }
        #carrito .c-quitar { grid-area: quitar; text-align: right; align-self: start; }
        #carrito .c-barra { grid-area: barra; color: #64748b; }
        #carrito .c-cant { grid-area: cant; white-space: nowrap; }
        #carrito .c-desc { grid-area: desc; white-space: nowrap; }
        #carrito .c-sub { grid-area: sub; text-align: right; font-weight: 700; font-size: 1.05rem; color: #0f766e; align-self: center; }
        #carrito .c-desc::before { content: "Desc% "; color: #64748b; font-size: .72rem; }
        #carrito .c-cant::before { content: "Cant "; color: #64748b; font-size: .72rem; }
    }
</style>
@endpush

@section('contenido')
<div class="pos-header">
    <h1 style="margin-right:auto">POS Venta</h1>
    <span class="pos-badge" id="lbl-tipo">Tipo: Boleta</span>
    <span class="pos-badge">Serie: <span id="lbl-serie">{{ $preview['serie'] }}</span></span>
    <span class="pos-badge">N°: <span id="lbl-numero">{{ str_pad($preview['numero'], 6, '0', STR_PAD_LEFT) }}</span></span>
    <span class="pos-badge">Terminal:
        @if($terminalesDisponibles->count() > 1)
        <select id="sel-terminal" style="border:none;background:transparent;color:#fff;font-weight:700;cursor:pointer">
            @foreach($terminalesDisponibles as $t)
            <option value="{{ $t->id }}" @selected($terminalActiva['id'] === $t->id)>
                {{ $t->codigo }} — {{ $t->nombre }}@if($t->local) ({{ $t->local }})@endif
            </option>
            @endforeach
        </select>
        @else
        <span id="lbl-terminal-codigo">{{ $terminalActiva['codigo'] }}</span>
        @endif
    </span>
    @if($terminalActiva['local'])
    <span class="pos-badge" id="lbl-terminal-local">{{ $terminalActiva['local'] }}</span>
    @endif
    <span class="pos-badge">Caja: <span id="lbl-terminal-caja">{{ $terminalActiva['caja'] }}</span></span>
    @if(auth()->user()->rol === 'cajero' && auth()->user()->en_sesion)
    <span class="pos-badge" style="background:#2563eb">● En sesión desde {{ auth()->user()->sesion_inicio_en?->format('H:i') }}</span>
    @endif
</div>

<div class="pos-actions">
    <button type="button" class="btn-iniciar" id="btn-iniciar">INICIAR</button>
    <button type="button" class="btn-suspender" id="btn-suspender">SUSPENDER</button>
    <button type="button" class="btn-recuperar" id="btn-recuperar">RECUPERAR</button>
    <button type="button" class="btn-cancelar" id="btn-cancelar">CANCELAR</button>
</div>

<div class="pos-layout">
    <div class="pos-left">
        <div class="card" style="margin:0">
            <div class="buscar-row">
                <input type="text" id="buscar" placeholder="Escanee código de barra / SKU o busque por nombre (Enter)" autocomplete="off" autofocus>
                <button type="button" class="btn-escanear" id="btn-escanear" title="Escanear con cámara">📷 Escanear</button>
            </div>
            <div id="resultados" style="max-height:160px;overflow:auto"></div>
        </div>
        <div class="card" style="margin:0">
            <div class="tabs" id="tabs-cat">
                <button type="button" class="tab activo" data-id="todos">Todos</button>
                @foreach($categorias as $cat)
                    <button type="button" class="tab" data-id="{{ $cat->id }}">{{ $cat->nombre }}</button>
                @endforeach
            </div>
            <p class="grid-titulo" id="grid-titulo" style="margin-top:.75rem">Top 5 más vendidos</p>
            <div class="grid-productos" id="grid-productos"></div>
        </div>
    </div>

    <div class="panel-derecho">
        <div class="card" style="margin:0">
            <strong>Comprobante</strong>
            <div class="tipo-doc" style="margin-top:.5rem">
                <button type="button" class="activo" data-tipo="boleta" id="btn-tipo-boleta">Boleta</button>
                <button type="button" data-tipo="factura" id="btn-tipo-factura">Factura</button>
            </div>
            <strong>Cliente</strong>
            <div class="cliente-row" style="margin-top:.5rem">
                <input type="text" id="cliente-doc" placeholder="DNI (opcional)" maxlength="11" inputmode="numeric">
                <button type="button" id="btn-buscar-cliente" class="btn btn-primary" style="padding:.5rem .75rem">Buscar</button>
            </div>
            <input type="text" id="cliente-nombre" placeholder="Nombre o razón social"
                   style="width:100%;margin-top:.4rem;padding:.5rem;border:1px solid #cbd5e1;border-radius:.4rem">
            <input type="hidden" id="cliente-id">
            <input type="hidden" id="cliente-tipo" value="dni">
            <div class="cliente-acciones">
                <button type="button" id="btn-guardar-cliente" class="btn-guardar-cli">Guardar cliente</button>
                <button type="button" id="btn-sin-dni" class="btn-sin-dni">Sin DNI — Consumidor final</button>
            </div>
            <div class="cliente-estado ok" id="cliente-estado">Consumidor final (venta sin DNI)</div>
        </div>

        <div class="card" style="margin:0;flex:1">
            <strong>Detalle venta</strong>
            <div id="carrito" style="margin-top:.5rem;max-height:220px;overflow:auto"><p style="color:#64748b">Sin productos</p></div>
            <div class="totales" style="margin-top:.75rem;border-top:1px solid #e2e8f0;padding-top:.5rem">
                <div>Subtotal: S/ <span id="subtotal">0.00</span></div>
                <div>IGV incl.: S/ <span id="igv">0.00</span></div>
                <div>Descuento: S/ <input type="number" id="descuento-global" value="0" min="0" step="0.01" style="width:70px;border:1px solid #cbd5e1;border-radius:3px;padding:2px 4px"></div>
                <div class="total-grande">TOTAL: S/ <span id="total">0.00</span></div>
            </div>
        </div>

        <div class="card" style="margin:0">
            <div class="pago-row">
                <select id="medio_pago">
                    <option value="efectivo">Efectivo</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="mixto">Mixto</option>
                </select>
            </div>
            <div id="fila-mixto" style="display:none">
                <div class="mixto-grid">
                    <div><label>Efectivo</label><input type="number" id="mix_efectivo" min="0" step="0.01" placeholder="0.00"></div>
                    <div><label>Yape</label><input type="number" id="mix_yape" min="0" step="0.01" placeholder="0.00"></div>
                    <div><label>Plin</label><input type="number" id="mix_plin" min="0" step="0.01" placeholder="0.00"></div>
                    <div><label>Tarjeta</label><input type="number" id="mix_tarjeta" min="0" step="0.01" placeholder="0.00"></div>
                </div>
                <div class="mixto-total" id="mixto-estado">Distribuya el total entre los medios de pago.</div>
            </div>
            <div class="pago-row" id="fila-efectivo">
                <input type="number" id="monto_recibido" placeholder="Recibido S/" min="0" step="0.01">
                <input type="text" id="vuelto" placeholder="Vuelto" readonly style="background:#f1f5f9">
            </div>
            <button type="button" id="btn-cobrar">COBRAR (F9)</button>
            <p id="msg" style="margin-top:.5rem;font-size:.85rem"></p>
            <p class="atajos">F2 buscar · F3 escanear · F9 cobrar · F10 suspender · Esc cancelar</p>
        </div>
    </div>
</div>

<div class="modal" id="modal-recuperar">
    <div class="modal-box">
        <h3 style="margin-bottom:.75rem">Recuperar venta suspendida</h3>
        <input type="text" id="codigo-hold" placeholder="Código HOLD (13 dígitos)" maxlength="13" style="width:100%;padding:.6rem;margin-bottom:.75rem;border:1px solid #cbd5e1;border-radius:.4rem">
        <div id="lista-holds" style="max-height:200px;overflow:auto;margin-bottom:.75rem"></div>
        <div style="display:flex;gap:.5rem">
            <button type="button" id="btn-confirmar-recuperar" class="btn btn-primary">Recuperar</button>
            <button type="button" id="btn-cerrar-modal" class="btn" style="background:#94a3b8;color:#fff">Cerrar</button>
        </div>
    </div>
</div>

<div class="modal" id="modal-escanear">
    <div class="modal-box modal-scanner">
        <h3 style="margin-bottom:.75rem">Escanear código de barras</h3>
        <div id="qr-reader"></div>
        <p style="font-size:.8rem;color:#64748b;margin-top:.75rem">Apunte la cámara al código de barras del producto.</p>
        <button type="button" id="btn-cerrar-escanear" class="btn" style="margin-top:.75rem;background:#94a3b8;color:#fff;width:100%">Cerrar cámara</button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const carrito = [];
let tipoDocumento = 'boleta';
const previews = {
    boleta: @json($preview),
    factura: @json($previewFactura),
};
let preview = previews.boleta;
const csrf = '{{ csrf_token() }}';
const TASA_IGV = 0.18;

const buscar = document.getElementById('buscar');
const resultados = document.getElementById('resultados');
const grid = document.getElementById('grid-productos');
let timer;

function headers() {
    return { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' };
}

function etiquetaSunatCobro(data) {
    if (!data.sunat_estado || data.sunat_estado === 'omitido') return '';
    const map = {
        simulado: 'CPE simulado',
        aceptado: 'CPE aceptado SUNAT',
        rechazado: 'CPE rechazado',
        error: 'Error CPE',
        pendiente: 'CPE pendiente',
    };
    const base = map[data.sunat_estado] || data.sunat_estado;
    return ' · ' + base + (data.sunat_mensaje ? ' — ' + data.sunat_mensaje : '');
}

function actualizarPreview(p) {
    preview = p;
    document.getElementById('lbl-tipo').textContent = 'Tipo: ' + (p.tipo_etiqueta || 'Boleta');
    document.getElementById('lbl-serie').textContent = p.serie;
    document.getElementById('lbl-numero').textContent = String(p.numero).padStart(6, '0');
}

function setTipoDocumento(tipo) {
    tipoDocumento = tipo;
    document.querySelectorAll('.tipo-doc button').forEach(b => {
        b.classList.toggle('activo', b.dataset.tipo === tipo);
    });
    const esFactura = tipo === 'factura';
    const doc = document.getElementById('cliente-doc');
    const nom = document.getElementById('cliente-nombre');
    doc.placeholder = esFactura ? 'RUC (11 dígitos) *' : 'DNI (opcional)';
    doc.maxLength = esFactura ? 11 : 8;
    nom.placeholder = esFactura ? 'Razón social *' : 'Nombre del cliente';
    document.getElementById('btn-sin-dni').style.display = esFactura ? 'none' : '';
    if (esFactura) {
        limpiarCliente();
        document.getElementById('cliente-estado').textContent = 'Factura: ingrese RUC y razón social del cliente.';
        document.getElementById('cliente-estado').className = 'cliente-estado';
    } else {
        setConsumidorFinal();
    }
    actualizarPreview(previews[tipo]);
}

function limpiarCliente() {
    document.getElementById('cliente-doc').value = '';
    document.getElementById('cliente-nombre').value = '';
    document.getElementById('cliente-id').value = '';
    document.getElementById('cliente-tipo').value = tipoDocumento === 'factura' ? 'ruc' : 'dni';
}

function setConsumidorFinal() {
    limpiarCliente();
    const el = document.getElementById('cliente-estado');
    el.textContent = 'Consumidor final (venta sin DNI)';
    el.className = 'cliente-estado ok';
}

function setClienteEstado(texto, ok = true) {
    const el = document.getElementById('cliente-estado');
    el.textContent = texto;
    el.className = ok ? 'cliente-estado ok' : 'cliente-estado';
}

document.getElementById('btn-tipo-boleta').onclick = () => setTipoDocumento('boleta');
document.getElementById('btn-tipo-factura').onclick = () => setTipoDocumento('factura');
document.getElementById('btn-sin-dni').onclick = () => setConsumidorFinal();

async function cargarMasVendidos(categoriaId) {
    const params = new URLSearchParams({ categoria_id: categoriaId });
    const res = await fetch(`{{ route('pos.mas-vendidos') }}?${params}`);
    const items = await res.json();
    const catNombre = categoriaId === 'todos'
        ? 'todas las categorías'
        : (document.querySelector(`.tab[data-id="${categoriaId}"]`)?.textContent || '');
    document.getElementById('grid-titulo').textContent =
        `Top 5 más vendidos${categoriaId !== 'todos' ? ' · ' + catNombre : ''}`;

    grid.innerHTML = items.map(p => cardProducto(p)).join('')
        || '<p style="color:#64748b">Sin productos en esta categoría</p>';
}

function cardProducto(p) {
    const barra = p.codigo_barra || p.sku || '—';
    return `
        <div class="prod-card" data-id="${p.id}" data-nombre="${esc(p.nombre)}" data-barra="${esc(barra)}"
             data-precio="${p.precio_venta}" data-stock="${p.stock_disponible}" data-min="${p.stock_minimo || 0}" data-igv="${p.afecto_igv ? 1 : 0}">
            <span class="prod-barra">${esc(barra)}</span>
            <strong>${esc(p.nombre)}</strong>
            <small>S/ ${(+p.precio_venta).toFixed(2)} · Stock: ${p.stock_disponible}</small>
        </div>`;
}

function esc(s) { return String(s ?? '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

document.querySelectorAll('.tab').forEach(tab => {
    tab.onclick = () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('activo'));
        tab.classList.add('activo');
        cargarMasVendidos(tab.dataset.id);
    };
});

cargarMasVendidos('todos');

grid.addEventListener('click', e => {
    const card = e.target.closest('.prod-card');
    if (!agregarDesdeFila(card)) return;
});

buscar.addEventListener('keydown', async e => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const q = buscar.value.trim();
    if (!q) {
        resultados.innerHTML = '';
        return;
    }
    const res = await fetch(`{{ route('pos.buscar-exacto') }}?q=${encodeURIComponent(q)}`);
    const p = await res.json();
    if (agregarDesdeApi(p)) {
        buscar.value = '';
        resultados.innerHTML = '';
    } else {
        buscar.dispatchEvent(new Event('input'));
    }
});

buscar.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(async () => {
        const q = buscar.value.trim();
        if (q.length < 2) { resultados.innerHTML = ''; return; }
        const res = await fetch(`{{ route('pos.buscar') }}?q=${encodeURIComponent(q)}`);
        const items = await res.json();
        resultados.innerHTML = items.filter(productoApiValido).map(p => {
            const barra = p.codigo_barra || p.sku || '—';
            return `
            <div class="resultado-busqueda" data-id="${p.id}" data-nombre="${esc(p.nombre)}" data-barra="${esc(barra)}"
                 data-precio="${p.precio_venta}" data-stock="${p.stock_disponible}" data-min="${p.stock_minimo || 0}" data-igv="${p.afecto_igv ? 1 : 0}">
                <strong>${esc(p.nombre)}</strong><br>
                <small><span style="font-family:monospace">${esc(barra)}</span> · S/ ${(+p.precio_venta).toFixed(2)} · Stock: ${p.stock_disponible}</small>
            </div>`;
        }).join('') || '<p style="padding:.5rem;color:#64748b">Sin resultados</p>';
    }, 250);
});

resultados.addEventListener('click', e => {
    const row = e.target.closest('.resultado-busqueda');
    if (!agregarDesdeFila(row)) return;
    buscar.value = '';
    resultados.innerHTML = '';
    buscar.focus();
});

function productoApiValido(p) {
    return p && p.id != null && String(p.nombre || '').trim() !== '' && Number.isFinite(+p.precio_venta);
}

function agregarDesdeApi(p) {
    if (!productoApiValido(p)) return false;
    return agregar(p.id, p.nombre, p.codigo_barra || p.sku || '', +p.precio_venta, +p.stock_disponible, p.afecto_igv ? 1 : 0, +p.stock_minimo || 0);
}

function agregarDesdeFila(row) {
    if (!row) return false;
    const id = +row.dataset.id;
    const nombre = row.dataset.nombre;
    if (!Number.isFinite(id) || id <= 0 || !nombre) return false;
    return agregar(id, nombre, row.dataset.barra || '', +row.dataset.precio, +row.dataset.stock, +row.dataset.igv, +row.dataset.min);
}

function agregar(id, nombre, codigoBarra, precio, stock, afectoIgv = 1, stockMinimo = 0) {
    id = +id;
    nombre = String(nombre || '').trim();
    precio = +precio;
    stock = +stock || 0;
    stockMinimo = +stockMinimo || 0;
    afectoIgv = +afectoIgv ? 1 : 0;
    if (!Number.isFinite(id) || id <= 0 || !nombre || !Number.isFinite(precio) || precio < 0) return false;

    const existente = carrito.find(l => l.producto_id === id);
    let cantidadFinal;
    if (existente) {
        existente.cantidad++;
        existente.stock = stock;
        existente.stock_minimo = stockMinimo;
        cantidadFinal = existente.cantidad;
    } else {
        carrito.push({
            producto_id: id, nombre, codigo_barra: codigoBarra || '—',
            precio, cantidad: 1, stock, stock_minimo: stockMinimo, descuento_pct: 0, afecto_igv: afectoIgv
        });
        cantidadFinal = 1;
    }
    const restante = stock - cantidadFinal;
    const msg = document.getElementById('msg');
    if (restante < 0) {
        msg.style.color = '#991b1b';
        msg.textContent = `⚠ ${nombre}: stock insuficiente (quedan ${stock})`;
    } else if (stockMinimo > 0 && restante <= stockMinimo) {
        msg.style.color = '#92400e';
        msg.textContent = `⚠ ${nombre}: stock bajo — quedarán ${restante} (mín. ${stockMinimo})`;
    } else {
        msg.textContent = '';
    }
    render();
    return true;
}

function calcularTotales() {
    let subtotal = 0, igv = 0;
    carrito.forEach(l => {
        const bruto = l.precio * l.cantidad;
        const desc = bruto * (l.descuento_pct / 100);
        const linea = bruto - desc;
        subtotal += linea;
        if (l.afecto_igv) igv += linea * TASA_IGV / (1 + TASA_IGV);
    });
    const descGlobal = Math.min(subtotal, +document.getElementById('descuento-global').value || 0);
    const total = subtotal - descGlobal;
    return { subtotal, igv, descGlobal, total };
}

function render() {
    const el = document.getElementById('carrito');
    if (!carrito.length) {
        el.innerHTML = '<p style="color:#64748b">Sin productos</p>';
        document.getElementById('subtotal').textContent = '0.00';
        document.getElementById('igv').textContent = '0.00';
        document.getElementById('total').textContent = '0.00';
        calcularVuelto();
        return;
    }
    el.innerHTML = `<table><thead><tr><th>Producto</th><th>C.Barra</th><th>Cant</th><th>Desc%</th><th>Sub</th><th></th></tr></thead><tbody>` +
        carrito.map((l, i) => {
            const bruto = l.precio * l.cantidad;
            const sub = bruto - bruto * (l.descuento_pct / 100);
            return `<tr>
                <td class="c-nombre">${l.nombre}</td>
                <td class="c-barra" style="font-family:monospace;font-size:.78rem">${l.codigo_barra || '—'}</td>
                <td class="c-cant">
                    <button type="button" class="qty-btn" data-i="${i}" data-d="-1">−</button>
                    ${l.cantidad}
                    <button type="button" class="qty-btn" data-i="${i}" data-d="1">+</button>
                </td>
                <td class="c-desc"><input type="number" class="inp-desc" data-i="${i}" value="${l.descuento_pct}" min="0" max="100" style="width:42px"></td>
                <td class="c-sub">S/ ${sub.toFixed(2)}</td>
                <td class="c-quitar"><button type="button" class="btn-quitar" data-i="${i}" style="border:none;background:none;color:#dc2626;cursor:pointer">✕</button></td>
            </tr>`;
        }).join('') + '</tbody></table>';

    document.querySelectorAll('.qty-btn').forEach(b => b.onclick = () => {
        const i = +b.dataset.i;
        const d = +b.dataset.d;
        carrito[i].cantidad += d;
        if (carrito[i].cantidad <= 0) carrito.splice(i, 1);
        render();
    });
    document.querySelectorAll('.inp-desc').forEach(inp => inp.onchange = () => {
        carrito[+inp.dataset.i].descuento_pct = Math.min(100, Math.max(0, +inp.value || 0));
        render();
    });
    document.querySelectorAll('.btn-quitar').forEach(b => b.onclick = () => { carrito.splice(+b.dataset.i, 1); render(); });

    const t = calcularTotales();
    document.getElementById('subtotal').textContent = t.subtotal.toFixed(2);
    document.getElementById('igv').textContent = t.igv.toFixed(2);
    document.getElementById('total').textContent = t.total.toFixed(2);
    calcularVuelto();
    actualizarMixtoEstado();
}

function calcularVuelto() {
    const t = calcularTotales();
    const medio = document.getElementById('medio_pago').value;
    const rec = +document.getElementById('monto_recibido').value || 0;
    let base = t.total;
    if (medio === 'mixto') {
        base = +document.getElementById('mix_efectivo').value || 0;
    }
    document.getElementById('vuelto').value = rec > 0 && base > 0 ? Math.max(0, rec - base).toFixed(2) : '';
}

function sumaMixto() {
    return ['mix_efectivo', 'mix_yape', 'mix_plin', 'mix_tarjeta']
        .reduce((s, id) => s + (+document.getElementById(id).value || 0), 0);
}

function actualizarMixtoEstado() {
    const el = document.getElementById('mixto-estado');
    if (!el || document.getElementById('medio_pago').value !== 'mixto') return;
    const t = calcularTotales();
    const suma = sumaMixto();
    const diff = Math.abs(suma - t.total);
    if (suma === 0) {
        el.textContent = `Total a distribuir: S/ ${t.total.toFixed(2)}`;
        el.className = 'mixto-total';
    } else if (diff <= 0.01) {
        el.textContent = `✓ Pagos cuadran: S/ ${suma.toFixed(2)}`;
        el.className = 'mixto-total ok';
    } else {
        el.textContent = `Faltan S/ ${(t.total - suma).toFixed(2)} (suma S/ ${suma.toFixed(2)} de S/ ${t.total.toFixed(2)})`;
        el.className = 'mixto-total err';
    }
    calcularVuelto();
}

function actualizarFilasPago() {
    const medio = document.getElementById('medio_pago').value;
    document.getElementById('fila-mixto').style.display = medio === 'mixto' ? 'block' : 'none';
    const muestraEfectivo = medio === 'efectivo' || (medio === 'mixto' && (+document.getElementById('mix_efectivo').value || 0) > 0);
    document.getElementById('fila-efectivo').style.display = muestraEfectivo ? 'flex' : 'none';
    if (medio !== 'mixto') {
        ['mix_efectivo', 'mix_yape', 'mix_plin', 'mix_tarjeta'].forEach(id => document.getElementById(id).value = '');
    }
    actualizarMixtoEstado();
}

document.getElementById('descuento-global').oninput = render;
document.getElementById('monto_recibido').oninput = calcularVuelto;
document.getElementById('medio_pago').onchange = actualizarFilasPago;
['mix_efectivo', 'mix_yape', 'mix_plin', 'mix_tarjeta'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        actualizarMixtoEstado();
        actualizarFilasPago();
    });
});

function limpiarVenta() {
    carrito.length = 0;
    if (tipoDocumento === 'boleta') {
        setConsumidorFinal();
    } else {
        limpiarCliente();
        setClienteEstado('Factura: ingrese RUC y razón social del cliente.', false);
    }
    document.getElementById('descuento-global').value = '0';
    document.getElementById('monto_recibido').value = '';
    document.getElementById('vuelto').value = '';
    document.getElementById('medio_pago').value = 'efectivo';
    ['mix_efectivo', 'mix_yape', 'mix_plin', 'mix_tarjeta'].forEach(id => document.getElementById(id).value = '');
    actualizarFilasPago();
    document.getElementById('msg').textContent = '';
    render();
}

document.getElementById('btn-iniciar').onclick = () => {
    limpiarVenta();
    document.getElementById('msg').textContent = 'Nueva venta iniciada';
    document.getElementById('msg').style.color = '#065f46';
    buscar.focus();
};

document.getElementById('btn-cancelar').onclick = () => {
    if (carrito.length && !confirm('¿Cancelar venta actual?')) return;
    limpiarVenta();
};

document.getElementById('btn-suspender').onclick = async () => {
    if (!carrito.length) return alert('No hay productos para suspender');
    const t = calcularTotales();
    const res = await fetch('{{ route('pos.suspender') }}', {
        method: 'POST', headers: headers(),
        body: JSON.stringify({
            lineas: carrito.map(l => ({ producto_id: l.producto_id, cantidad: l.cantidad, descuento_pct: l.descuento_pct })),
            lineas_detalle: carrito.map(l => ({ ...l })),
            total: t.total,
            cliente_id: document.getElementById('cliente-id').value || null,
            descuento_global: t.descGlobal,
        })
    });
    const data = await res.json();
    if (data.ok) {
        alert(`Venta suspendida\nCódigo HOLD: ${data.codigo_hold}\nTotal: S/ ${data.total}`);
        limpiarVenta();
    } else alert(data.error || 'Error');
};

document.getElementById('btn-recuperar').onclick = async () => {
    document.getElementById('modal-recuperar').classList.add('activo');
    const res = await fetch('{{ route('pos.suspendidas') }}');
    const holds = await res.json();
    document.getElementById('lista-holds').innerHTML = holds.length ? holds.map(h =>
        `<div class="hold-item" data-codigo="${h.codigo_hold}"><strong>${h.codigo_hold}</strong><br><small>S/ ${h.total} · ${h.creado_en}</small></div>`
    ).join('') : '<p style="color:#64748b">No hay ventas suspendidas</p>';
};

document.getElementById('lista-holds').addEventListener('click', e => {
    const item = e.target.closest('.hold-item');
    if (item) document.getElementById('codigo-hold').value = item.dataset.codigo;
});

document.getElementById('btn-cerrar-modal').onclick = () => document.getElementById('modal-recuperar').classList.remove('activo');

document.getElementById('btn-confirmar-recuperar').onclick = async () => {
    const codigo = document.getElementById('codigo-hold').value.trim();
    if (codigo.length !== 13) return alert('Código HOLD debe tener 13 dígitos');
    const res = await fetch('{{ route('pos.recuperar') }}', {
        method: 'POST', headers: headers(),
        body: JSON.stringify({ codigo_hold: codigo })
    });
    const data = await res.json();
    if (!data.ok) return alert(data.error || 'Error');
    limpiarVenta();
    const v = data.venta;
    if (v.cliente_id) document.getElementById('cliente-id').value = v.cliente_id;
    document.getElementById('descuento-global').value = v.descuento_global || 0;
    (v.lineas_detalle || []).forEach(l => carrito.push({ ...l }));
    document.getElementById('modal-recuperar').classList.remove('activo');
    render();
};

async function buscarCliente() {
    const doc = document.getElementById('cliente-doc').value.replace(/\D/g, '');
    if (!doc) return;
    document.getElementById('cliente-doc').value = doc;
    const res = await fetch(`{{ route('pos.cliente.buscar') }}?doc=${encodeURIComponent(doc)}`);
    const c = await res.json();
    if (c && c.id) {
        document.getElementById('cliente-id').value = c.id;
        document.getElementById('cliente-nombre').value = c.nombre;
        document.getElementById('cliente-tipo').value = c.tipo_documento;
        const tipo = (c.tipo_documento || '').toUpperCase();
        setClienteEstado(`Cliente: ${c.nombre} (${tipo} ${c.numero_documento})`);
        if (c.tipo_documento === 'ruc' && tipoDocumento === 'boleta') {
            setTipoDocumento('factura');
        }
    } else {
        document.getElementById('cliente-id').value = '';
        const esRuc = doc.length === 11 || tipoDocumento === 'factura';
        document.getElementById('cliente-tipo').value = esRuc ? 'ruc' : 'dni';
        setClienteEstado(esRuc ? 'RUC no registrado — complete razón social y guarde' : 'DNI no registrado — complete nombre y guarde', false);
        document.getElementById('cliente-nombre').focus();
    }
}

document.getElementById('btn-buscar-cliente').onclick = buscarCliente;
document.getElementById('cliente-doc').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); buscarCliente(); }
});

document.getElementById('btn-guardar-cliente').onclick = async () => {
    const doc = document.getElementById('cliente-doc').value.replace(/\D/g, '');
    const nombre = document.getElementById('cliente-nombre').value.trim();
    const esFactura = tipoDocumento === 'factura';

    if (esFactura) {
        if (doc.length !== 11) return alert('La factura requiere un RUC de 11 dígitos.');
        if (!nombre) return alert('Ingrese la razón social.');
    } else if (doc && doc.length !== 8) {
        return alert('El DNI debe tener 8 dígitos.');
    } else if (doc && !nombre) {
        return alert('Ingrese el nombre del cliente.');
    } else if (!doc) {
        return setConsumidorFinal();
    }

    const tipoDoc = doc.length === 11 ? 'ruc' : 'dni';
    const res = await fetch('{{ route('pos.cliente.guardar') }}', {
        method: 'POST', headers: headers(),
        body: JSON.stringify({ tipo_documento: tipoDoc, numero_documento: doc, nombre })
    });
    const nuevo = await res.json();
    if (!res.ok) {
        const err = nuevo.error || nuevo.message || Object.values(nuevo.errors || {})[0]?.[0];
        return alert(err || 'No se pudo guardar el cliente');
    }
    document.getElementById('cliente-id').value = nuevo.id;
    document.getElementById('cliente-nombre').value = nuevo.nombre;
    document.getElementById('cliente-tipo').value = nuevo.tipo_documento;
    setClienteEstado(`Cliente: ${nuevo.nombre} (${nuevo.tipo_documento.toUpperCase()} ${nuevo.numero_documento})`);
};

document.getElementById('btn-cobrar').onclick = async () => {
    if (!carrito.length) return;
    const t = calcularTotales();
    const medio = document.getElementById('medio_pago').value;
    const recibido = +document.getElementById('monto_recibido').value || 0;
    let pagosMixtos = null;

    if (medio === 'efectivo' && recibido < t.total) return alert('Monto recibido insuficiente');

    if (medio === 'mixto') {
        const medios = [
            { medio: 'efectivo', monto: +document.getElementById('mix_efectivo').value || 0 },
            { medio: 'yape', monto: +document.getElementById('mix_yape').value || 0 },
            { medio: 'plin', monto: +document.getElementById('mix_plin').value || 0 },
            { medio: 'tarjeta', monto: +document.getElementById('mix_tarjeta').value || 0 },
        ].filter(p => p.monto > 0);

        if (!medios.length) return alert('Indique al menos un monto en el pago mixto');
        const suma = medios.reduce((s, p) => s + p.monto, 0);
        if (Math.abs(suma - t.total) > 0.01) {
            return alert(`Los pagos deben sumar S/ ${t.total.toFixed(2)} (actual: S/ ${suma.toFixed(2)})`);
        }
        const efectivoMonto = medios.find(p => p.medio === 'efectivo')?.monto || 0;
        if (efectivoMonto > 0 && recibido < efectivoMonto) {
            return alert('Monto en efectivo recibido insuficiente');
        }
        pagosMixtos = medios;
    }

    const clienteId = document.getElementById('cliente-id').value || null;
    if (tipoDocumento === 'factura' && !clienteId) {
        return alert('La factura requiere un cliente con RUC. Busque o registre el cliente.');
    }

    const msg = document.getElementById('msg');
    msg.textContent = 'Procesando...';
    const res = await fetch('{{ route('pos.cobrar') }}', {
        method: 'POST', headers: headers(),
        body: JSON.stringify({
            lineas: carrito.map(l => ({ producto_id: l.producto_id, cantidad: l.cantidad, descuento_pct: l.descuento_pct })),
            medio_pago: medio,
            tipo_documento: tipoDocumento,
            cliente_id: clienteId,
            descuento_global: t.descGlobal,
            monto_recibido: (medio === 'efectivo' || (medio === 'mixto' && pagosMixtos?.some(p => p.medio === 'efectivo')))
                ? recibido || null
                : null,
            pagos_mixtos: pagosMixtos,
        })
    });
    const data = await res.json();
    if (data.ok) {
        msg.style.color = '#065f46';
        msg.textContent = `✓ ${data.tipo_etiqueta} ${data.numero} — Total S/ ${data.total}` + (data.vuelto != null ? ` · Vuelto S/ ${data.vuelto}` : '') + etiquetaSunatCobro(data);
        if (data.preview_boleta) previews.boleta = data.preview_boleta;
        if (data.preview_factura) previews.factura = data.preview_factura;
        actualizarPreview(data.preview || previews[tipoDocumento]);
        if (data.ticket_url) window.open(data.ticket_url, '_blank');
        limpiarVenta();
    } else {
        msg.style.color = '#991b1b';
        msg.textContent = data.error || 'Error al cobrar';
    }
};

document.addEventListener('keydown', e => {
    if (e.key === 'F2') { e.preventDefault(); buscar.focus(); }
    if (e.key === 'F3') { e.preventDefault(); document.getElementById('btn-escanear').click(); }
    if (e.key === 'F9') { e.preventDefault(); document.getElementById('btn-cobrar').click(); }
    if (e.key === 'F10') { e.preventDefault(); document.getElementById('btn-suspender').click(); }
    if (e.key === 'Escape') { e.preventDefault(); document.getElementById('btn-cancelar').click(); }
});

let html5QrCode = null;

async function buscarPorCodigo(codigo) {
    const q = String(codigo || '').trim();
    if (!q) return;
    const res = await fetch(`{{ route('pos.buscar-exacto') }}?q=${encodeURIComponent(q)}`);
    const p = await res.json();
    if (agregarDesdeApi(p)) {
        buscar.value = '';
        resultados.innerHTML = '';
        buscar.focus();
    } else {
        alert('Producto no encontrado: ' + q);
    }
}

async function cerrarEscaner() {
    document.getElementById('modal-escanear').classList.remove('activo');
    if (html5QrCode) {
        try { await html5QrCode.stop(); } catch (_) {}
        try { await html5QrCode.clear(); } catch (_) {}
        html5QrCode = null;
    }
}

document.getElementById('btn-cerrar-escanear').onclick = cerrarEscaner;

document.getElementById('btn-escanear').onclick = async () => {
    if (typeof Html5Qrcode === 'undefined') {
        return alert('No se pudo cargar el escáner. Verifique su conexión a internet.');
    }
    document.getElementById('modal-escanear').classList.add('activo');
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode('qr-reader');
    }
    try {
        await html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 260, height: 120 } },
            async (decoded) => {
                await cerrarEscaner();
                await buscarPorCodigo(decoded);
            },
            () => {}
        );
    } catch (err) {
        await cerrarEscaner();
        alert('No se pudo acceder a la cámara. Use el lector USB o escriba el código manualmente.');
    }
};

actualizarFilasPago();

const selTerminal = document.getElementById('sel-terminal');
if (selTerminal) {
    selTerminal.addEventListener('change', async () => {
        const res = await fetch('{{ route('pos.cambiar-terminal') }}', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ terminal_id: +selTerminal.value }),
        });
        const data = await res.json();
        if (!data.ok) {
            return alert(data.error || 'No se pudo cambiar la terminal');
        }
        if (data.preview_boleta) previews.boleta = data.preview_boleta;
        if (data.preview_factura) previews.factura = data.preview_factura;
        actualizarPreview(previews[tipoDocumento]);
        const lblCaja = document.getElementById('lbl-terminal-caja');
        if (lblCaja) lblCaja.textContent = data.terminal.caja;
        const lblLocal = document.getElementById('lbl-terminal-local');
        if (lblLocal) {
            lblLocal.textContent = data.terminal.local || '';
            lblLocal.style.display = data.terminal.local ? '' : 'none';
        }
    });
}

</script>
@endpush
