<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#0f766e">
    <title>POS Móvil — VentaPOS</title>
    <style>
        :root { --primary: #0f766e; --safe-b: env(safe-area-inset-bottom, 0); }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; color: #1e293b; height: 100dvh; overflow: hidden; }
        .app { display: flex; flex-direction: column; height: 100dvh; }
        .top { background: var(--primary); color: #fff; padding: .65rem .85rem; padding-top: max(.65rem, env(safe-area-inset-top)); }
        .top-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .45rem; }
        .top h1 { font-size: 1rem; flex: 1; }
        .top a { color: #ccfbf1; font-size: .8rem; text-decoration: none; }
        .badge { background: rgba(255,255,255,.2); padding: .2rem .5rem; border-radius: .35rem; font-size: .72rem; font-weight: 700; }
        .buscar-row { display: flex; gap: .45rem; }
        #buscar { flex: 1; padding: .75rem; font-size: 1rem; border: none; border-radius: .5rem; }
        .btn-icon { width: 48px; height: 48px; border: none; border-radius: .5rem; background: #115e59; color: #fff; font-size: 1.2rem; cursor: pointer; flex-shrink: 0; }
        .main { flex: 1; overflow-y: auto; padding: .65rem; padding-bottom: calc(88px + var(--safe-b)); }
        .tabs { display: flex; gap: .35rem; overflow-x: auto; margin-bottom: .65rem; padding-bottom: .2rem; }
        .tab { padding: .45rem .75rem; border-radius: 999px; border: 1px solid #cbd5e1; background: #fff; white-space: nowrap; font-size: .82rem; font-weight: 600; cursor: pointer; }
        .tab.activo { background: var(--primary); color: #fff; border-color: var(--primary); }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .5rem; }
        .card { background: #fff; border-radius: .65rem; padding: .75rem; border: 1px solid #e2e8f0; min-height: 88px; cursor: pointer; }
        .card:active { background: #f0fdfa; border-color: var(--primary); }
        .card .barra { font-family: monospace; font-size: .68rem; color: #64748b; }
        .card strong { display: block; font-size: .85rem; line-height: 1.25; margin: .2rem 0; }
        .card .precio { color: var(--primary); font-weight: 800; font-size: .95rem; }
        #resultados { background: #fff; border-radius: .65rem; margin-bottom: .65rem; overflow: hidden; border: 1px solid #e2e8f0; }
        #resultados div { padding: .75rem; border-bottom: 1px solid #f1f5f9; cursor: pointer; }
        #resultados div:active { background: #f0fdfa; }
        .msg { font-size: .8rem; min-height: 1.2rem; margin-bottom: .35rem; }
        .barra-carrito { position: fixed; left: 0; right: 0; bottom: 0; background: #fff; border-top: 2px solid var(--primary); padding: .65rem .85rem; padding-bottom: calc(.65rem + var(--safe-b)); z-index: 50; }
        .barra-carrito button { width: 100%; padding: .9rem; border: none; border-radius: .6rem; background: var(--primary); color: #fff; font-size: 1.05rem; font-weight: 800; cursor: pointer; }
        .barra-carrito .resumen { display: flex; justify-content: space-between; align-items: center; margin-bottom: .45rem; font-size: .9rem; }
        .barra-carrito .total { font-size: 1.35rem; font-weight: 800; color: var(--primary); }
        .panel { display: none; position: fixed; inset: 0; background: #fff; z-index: 60; flex-direction: column; }
        .panel.activo { display: flex; }
        .panel-head { background: var(--primary); color: #fff; padding: .85rem; display: flex; align-items: center; gap: .5rem; }
        .panel-head h2 { flex: 1; font-size: 1rem; }
        .panel-body { flex: 1; overflow-y: auto; padding: .85rem; }
        .linea { display: flex; gap: .5rem; align-items: center; padding: .65rem 0; border-bottom: 1px solid #e2e8f0; }
        .linea-info { flex: 1; }
        .linea-info small { color: #64748b; }
        .qty { display: flex; align-items: center; gap: .35rem; }
        .qty button { width: 36px; height: 36px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: .4rem; font-size: 1.1rem; cursor: pointer; }
        .pago select, .pago input { width: 100%; padding: .7rem; border: 1px solid #cbd5e1; border-radius: .5rem; font-size: 1rem; margin-bottom: .5rem; }
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 80; align-items: center; justify-content: center; padding: 1rem; }
        .modal.activo { display: flex; }
        .modal-box { background: #fff; border-radius: .75rem; padding: 1rem; width: 100%; max-width: 400px; }
        #qr-reader { border-radius: .5rem; overflow: hidden; }
    </style>
</head>
<body>
<div class="app">
    <header class="top">
        <div class="top-row">
            <h1>POS Móvil</h1>
            @if($usuario->tieneModulo('pos'))
            <a href="{{ route('pos.index') }}">Escritorio</a>
            @endif
            <span class="badge">{{ $preview['serie'] }}-{{ str_pad($preview['numero'], 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="buscar-row">
            <input type="search" id="buscar" placeholder="Buscar o escanear código..." autocomplete="off" enterkeyhint="search">
            <button type="button" class="btn-icon" id="btn-escanear" title="Cámara">📷</button>
        </div>
    </header>

    <main class="main">
        <p class="msg" id="msg"></p>
        <div id="resultados" style="display:none"></div>
        <div class="tabs" id="tabs">
            <button type="button" class="tab activo" data-id="todos">Todos</button>
            @foreach($categorias as $c)
            <button type="button" class="tab" data-id="{{ $c->id }}">{{ $c->nombre }}</button>
            @endforeach
        </div>
        <div class="grid" id="grid"></div>
    </main>

    <div class="barra-carrito" id="barra-carrito">
        <div class="resumen">
            <span><strong id="lbl-items">0</strong> productos</span>
            <span class="total" id="lbl-total">S/ 0.00</span>
        </div>
        <button type="button" id="btn-ver-carrito" disabled>Ver carrito y cobrar</button>
    </div>
</div>

<div class="panel" id="panel-carrito">
    <div class="panel-head">
        <button type="button" id="btn-cerrar-carrito" style="background:none;border:none;color:#fff;font-size:1.4rem;cursor:pointer">←</button>
        <h2>Carrito</h2>
        <span id="lbl-total-panel" style="font-weight:800">S/ 0.00</span>
    </div>
    <div class="panel-body">
        <div id="lista-carrito"></div>
        <div class="pago" style="margin-top:1rem">
            <label style="font-weight:600;font-size:.85rem;display:block;margin-bottom:.35rem">Medio de pago</label>
            <select id="medio-pago">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="yape">Yape / Plin</option>
                <option value="transferencia">Transferencia</option>
            </select>
            <label style="font-weight:600;font-size:.85rem;display:block;margin-bottom:.35rem">Monto recibido (efectivo)</label>
            <input type="number" id="monto-recibido" step="0.01" min="0" placeholder="Opcional">
        </div>
        <button type="button" id="btn-cobrar" style="width:100%;margin-top:1rem;padding:.95rem;border:none;border-radius:.6rem;background:#0f766e;color:#fff;font-size:1.05rem;font-weight:800">Cobrar boleta</button>
        <p class="msg" id="msg-cobro" style="margin-top:.75rem"></p>
    </div>
</div>

<div class="modal" id="modal-escanear">
    <div class="modal-box">
        <h3 style="margin-bottom:.75rem">Escanear código</h3>
        <div id="qr-reader"></div>
        <button type="button" id="btn-cerrar-escanear" style="width:100%;margin-top:.75rem;padding:.65rem;border:none;border-radius:.5rem;background:#94a3b8;color:#fff;font-weight:600">Cerrar</button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const carrito = [];
let preview = @json($preview);
const csrf = '{{ csrf_token() }}';
const TASA_IGV = 0.18;
let html5QrCode = null;
let timer;

const buscar = document.getElementById('buscar');
const resultados = document.getElementById('resultados');
const grid = document.getElementById('grid');

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

function esc(s) { return String(s ?? '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

function totales() {
    let sub = 0, igv = 0;
    carrito.forEach(l => {
        const bruto = l.precio * l.cantidad;
        const desc = bruto * (l.descuento_pct / 100);
        const neto = bruto - desc;
        sub += neto;
        if (l.afecto_igv) igv += neto - neto / (1 + TASA_IGV);
    });
    return { sub, igv, total: sub };
}

function pintarCarrito() {
    const t = totales();
    document.getElementById('lbl-items').textContent = carrito.reduce((s, l) => s + l.cantidad, 0);
    document.getElementById('lbl-total').textContent = 'S/ ' + t.total.toFixed(2);
    document.getElementById('lbl-total-panel').textContent = 'S/ ' + t.total.toFixed(2);
    document.getElementById('btn-ver-carrito').disabled = carrito.length === 0;
    document.getElementById('lista-carrito').innerHTML = carrito.map((l, i) => `
        <div class="linea">
            <div class="linea-info">
                <strong>${esc(l.nombre)}</strong><br>
                <small>S/ ${l.precio.toFixed(2)} × ${l.cantidad}</small>
            </div>
            <div class="qty">
                <button type="button" data-i="${i}" data-d="-1">−</button>
                <span>${l.cantidad}</span>
                <button type="button" data-i="${i}" data-d="1">+</button>
            </div>
        </div>`).join('') || '<p style="color:#64748b">Carrito vacío</p>';
}

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

function agregar(id, nombre, barra, precio, stock, afectoIgv = 1, stockMinimo = 0) {
    id = +id;
    nombre = String(nombre || '').trim();
    precio = +precio;
    stock = +stock || 0;
    stockMinimo = +stockMinimo || 0;
    afectoIgv = +afectoIgv ? 1 : 0;
    if (!Number.isFinite(id) || id <= 0 || !nombre || !Number.isFinite(precio) || precio < 0) return false;

    const ex = carrito.find(l => l.producto_id === id);
    if (ex) ex.cantidad++;
    else carrito.push({ producto_id: id, nombre, codigo_barra: barra, precio, cantidad: 1, stock, stock_minimo: stockMinimo, descuento_pct: 0, afecto_igv: afectoIgv });
    const msg = document.getElementById('msg');
    msg.style.color = '#065f46';
    msg.textContent = '✓ ' + nombre;
    pintarCarrito();
    return true;
}

async function buscarExacto(q) {
    q = String(q || '').trim();
    if (!q) return;
    const res = await fetch(`{{ route('pos.buscar-exacto') }}?q=${encodeURIComponent(q)}`);
    const p = await res.json();
    if (agregarDesdeApi(p)) {
        buscar.value = '';
        resultados.style.display = 'none';
    } else {
        document.getElementById('msg').style.color = '#991b1b';
        document.getElementById('msg').textContent = 'No encontrado: ' + q;
    }
}

function cardHtml(p) {
    const barra = p.codigo_barra || p.sku || '—';
    return `<div class="card" data-id="${p.id}" data-nombre="${esc(p.nombre)}" data-barra="${esc(barra)}"
        data-precio="${p.precio_venta}" data-stock="${p.stock_disponible}" data-min="${p.stock_minimo || 0}" data-igv="${p.afecto_igv ? 1 : 0}">
        <div class="barra">${esc(barra)}</div>
        <strong>${esc(p.nombre)}</strong>
        <div class="precio">S/ ${(+p.precio_venta).toFixed(2)}</div>
    </div>`;
}

async function cargarGrid(categoriaId) {
    const params = new URLSearchParams({ categoria_id: categoriaId });
    const res = await fetch(`{{ route('pos.mas-vendidos') }}?${params}`);
    const items = await res.json();
    grid.innerHTML = items.map(cardHtml).join('') || '<p style="grid-column:1/-1;color:#64748b">Sin productos</p>';
}

document.querySelectorAll('.tab').forEach(tab => {
    tab.onclick = () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('activo'));
        tab.classList.add('activo');
        cargarGrid(tab.dataset.id);
    };
});

grid.addEventListener('click', e => {
    const card = e.target.closest('.card[data-id]');
    agregarDesdeFila(card);
});

buscar.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        buscarExacto(buscar.value.trim());
    }
});

buscar.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(async () => {
        const q = buscar.value.trim();
        if (q.length < 2) { resultados.style.display = 'none'; return; }
        const res = await fetch(`{{ route('pos.buscar') }}?q=${encodeURIComponent(q)}`);
        const items = await res.json();
        resultados.style.display = 'block';
        resultados.innerHTML = items.filter(productoApiValido).map(p => {
            const barra = p.codigo_barra || p.sku || '—';
            return `<div class="resultado-busqueda" data-id="${p.id}" data-nombre="${esc(p.nombre)}" data-barra="${esc(barra)}"
                data-precio="${p.precio_venta}" data-stock="${p.stock_disponible}" data-min="${p.stock_minimo || 0}" data-igv="${p.afecto_igv ? 1 : 0}">
                <strong>${esc(p.nombre)}</strong><br><small>${esc(barra)} · S/ ${(+p.precio_venta).toFixed(2)}</small></div>`;
        }).join('') || '<div style="padding:.75rem;color:#64748b">Sin resultados</div>';
    }, 280);
});

resultados.addEventListener('click', e => {
    const row = e.target.closest('.resultado-busqueda');
    if (!agregarDesdeFila(row)) return;
    buscar.value = '';
    resultados.style.display = 'none';
});

document.getElementById('lista-carrito').addEventListener('click', e => {
    const btn = e.target.closest('button[data-i]');
    if (!btn) return;
    const i = +btn.dataset.i;
    const d = +btn.dataset.d;
    carrito[i].cantidad += d;
    if (carrito[i].cantidad <= 0) carrito.splice(i, 1);
    pintarCarrito();
});

document.getElementById('btn-ver-carrito').onclick = () => document.getElementById('panel-carrito').classList.add('activo');
document.getElementById('btn-cerrar-carrito').onclick = () => document.getElementById('panel-carrito').classList.remove('activo');

document.getElementById('btn-cobrar').onclick = async () => {
    if (!carrito.length) return;
    const medio = document.getElementById('medio-pago').value;
    const recibido = parseFloat(document.getElementById('monto-recibido').value) || null;
    const msg = document.getElementById('msg-cobro');
    msg.textContent = 'Procesando...';
    msg.style.color = '#64748b';
    const res = await fetch('{{ route('pos.cobrar') }}', {
        method: 'POST', headers: headers(),
        body: JSON.stringify({
            lineas: carrito.map(l => ({ producto_id: l.producto_id, cantidad: l.cantidad, descuento_pct: l.descuento_pct })),
            medio_pago: medio,
            tipo_documento: 'boleta',
            cliente_id: null,
            descuento_global: 0,
            monto_recibido: medio === 'efectivo' ? recibido : null,
        })
    });
    const data = await res.json();
    if (data.ok) {
        msg.style.color = '#065f46';
        msg.textContent = `✓ Boleta ${data.numero} — S/ ${data.total}` + etiquetaSunatCobro(data);
        if (data.preview) preview = data.preview;
        if (data.ticket_url) window.open(data.ticket_url, '_blank');
        carrito.length = 0;
        pintarCarrito();
        setTimeout(() => document.getElementById('panel-carrito').classList.remove('activo'), 800);
    } else {
        msg.style.color = '#991b1b';
        msg.textContent = data.error || 'Error al cobrar';
    }
};

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
    if (typeof Html5Qrcode === 'undefined') return alert('Sin conexión para cargar el escáner.');
    document.getElementById('modal-escanear').classList.add('activo');
    if (!html5QrCode) html5QrCode = new Html5Qrcode('qr-reader');
    try {
        await html5QrCode.start({ facingMode: 'environment' }, { fps: 8, qrbox: { width: 240, height: 100 } },
            async (code) => { await cerrarEscaner(); await buscarExacto(code); }, () => {});
    } catch (_) {
        await cerrarEscaner();
        alert('No se pudo usar la cámara.');
    }
};

cargarGrid('todos');
pintarCarrito();
</script>
</body>
</html>
