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
    .pos-layout { display: grid; grid-template-columns: 1fr minmax(430px, 480px); gap: 1rem; }
    @media (max-width: 1280px) { .pos-layout { grid-template-columns: 1fr minmax(400px, 440px); } }
    @media (max-width: 1100px) { .pos-layout { grid-template-columns: 1fr; } }
    .pos-left { display: flex; flex-direction: column; gap: .75rem; }
    #buscar { flex: 1; padding: .85rem; font-size: 1.05rem; border: 2px solid #0f766e; border-radius: .5rem; }
    .buscar-row { display: flex; gap: .5rem; align-items: stretch; }
    .btn-escanear { padding: 0 1rem; border: 2px solid #0f766e; background: #f0fdfa; color: #0f766e; border-radius: .5rem; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .btn-escanear:hover { background: #ccfbf1; }
    .tabs { display: flex; flex-wrap: wrap; gap: .4rem; }
    .tab { padding: .45rem .85rem; border: 1px solid #cbd5e1; background: #fff; border-radius: .4rem; cursor: pointer; font-size: .85rem; }
    .tab.activo { background: #0f766e; color: #fff; border-color: #0f766e; }
    .tab-colapsada { display: none !important; }
    .tabs-cat-block { margin-bottom: .15rem; }
    .btn-tabs-mas {
        display: none;
        width: 100%;
        margin-top: .35rem;
        padding: .42rem .65rem;
        border: 1px dashed #94a3b8;
        background: #f8fafc;
        color: #0f766e;
        border-radius: .4rem;
        font-size: .78rem;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-tabs-mas.visible { display: block; }
    .grid-productos { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: .5rem; max-height: 320px; overflow-y: auto; }
    .grid-productos.grid-cargando { opacity: .55; pointer-events: none; }
    .prod-card { border: 1px solid #e2e8f0; border-radius: .5rem; padding: .6rem; cursor: pointer; background: #fff; transition: .15s; }
    .prod-card:hover { border-color: #0f766e; background: #f0fdfa; }
    .prod-card strong { display: block; font-size: .82rem; line-height: 1.2; margin-bottom: .25rem; }
    .prod-barra { display: block; font-family: ui-monospace, monospace; font-size: .72rem; color: #475569; margin-bottom: .2rem; }
    .prod-card small { color: #64748b; }
    .grid-titulo { font-size: .85rem; color: #64748b; margin-bottom: .5rem; font-weight: 600; }
    #resultados div { padding: .55rem; border-bottom: 1px solid #e2e8f0; cursor: pointer; }
    #resultados div:hover { background: #f0fdfa; }
    .panel-derecho { display: flex; flex-direction: column; gap: .75rem; min-width: 0; }
    .card-detalle-venta { min-width: 0; }
    #carrito { margin-top: .5rem; max-height: 280px; overflow: auto; min-width: 0; -webkit-overflow-scrolling: touch; }
    #carrito table { width: 100%; border-collapse: collapse; font-size: .84rem; table-layout: fixed; }
    #carrito th, #carrito td { padding: .5rem .4rem; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    #carrito th { font-size: .78rem; color: #64748b; font-weight: 700; }
    #carrito .col-producto { width: 32%; min-width: 0; word-break: normal; overflow-wrap: anywhere; line-height: 1.25; }
    #carrito .col-producto > span:first-child,
    #carrito .carrito-nombre { display: block; font-weight: 600; }
    #carrito .carrito-meta { display: none; }
    #carrito .col-um { width: 2.5rem; text-align: center; font-size: .75rem; color: #64748b; text-transform: lowercase; }
    #carrito .col-pu,
    #carrito .col-sub { width: 4.5rem; text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    #carrito .col-cant { width: 5.5rem; text-align: center; }
    #carrito .inp-cant-kg { width: 100%; max-width: 4.25rem; padding: .2rem .25rem; text-align: center; border: 1px solid #cbd5e1; border-radius: 3px; font-variant-numeric: tabular-nums; }
    #carrito .col-desc { width: 3.25rem; text-align: center; }
    #carrito .col-quitar { width: 1.75rem; text-align: center; padding: .35rem .15rem; }
    #carrito .carrito-barra { display: block; margin-top: .2rem; font-family: ui-monospace, monospace; font-size: .7rem; color: #64748b; word-break: break-all; overflow-wrap: anywhere; }
    #carrito .inp-desc { width: 100%; max-width: 3rem; padding: .2rem .25rem; text-align: center; border: 1px solid #cbd5e1; border-radius: 3px; }
    #carrito .celda-sub { font-weight: 600; color: #0f766e; }
    .qty-btn { width: 24px; height: 24px; border: 1px solid #cbd5e1; background: #f8fafc; cursor: pointer; border-radius: 3px; font-size: .9rem; line-height: 1; }
    .totales { font-size: .92rem; line-height: 1.8; display: grid; grid-template-columns: 1fr auto; gap: .2rem 1rem; align-items: center; }
    .totales .totales-fila { display: contents; }
    .totales .lbl-total { grid-column: 1; }
    .totales .val-total { grid-column: 2; text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; min-width: 5.5rem; }
    .totales .fila-descuento { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .totales .fila-descuento input { width: 5rem; border: 1px solid #cbd5e1; border-radius: 3px; padding: .25rem .4rem; text-align: right; font-variant-numeric: tabular-nums; }
    .total-grande { grid-column: 1 / -1; display: flex; align-items: baseline; justify-content: space-between; gap: .75rem; font-size: 1.55rem; font-weight: 800; color: #0f766e; margin-top: .25rem; padding-top: .35rem; border-top: 1px dashed #cbd5e1; }
    .total-grande .val-total { font-size: inherit; font-weight: inherit; color: inherit; min-width: auto; }
    .cliente-row { display: flex; gap: .4rem; }
    .cliente-row input { flex: 1; padding: .5rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
    .pago-row { display: flex; gap: .5rem; margin-bottom: .5rem; }
    .pago-row input, .pago-row select { flex: 1; padding: .5rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
    #btn-cobrar { width: 100%; padding: .85rem; font-size: 1rem; background: #0f766e; color: #fff; border: none; border-radius: .5rem; font-weight: 800; cursor: pointer; }
    #btn-cobrar:hover:not(:disabled) { background: #115e59; }
    #btn-cobrar:disabled { opacity: .5; cursor: not-allowed; }
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
    .mixto-total.warn { color: #92400e; }
    .mixto-total.err { color: #991b1b; }
    .mixto-ayuda { font-size: .72rem; color: #64748b; margin: 0 0 .5rem; line-height: 1.35; }
    #qr-reader { width: 100%; border-radius: .5rem; overflow: hidden; }
    .modal-scanner { max-width: 480px; }

    @media (max-width: 900px) {
        .pos-header h1 { font-size: 1.1rem; width: 100%; margin-bottom: .15rem; }
        .pos-header { gap: .35rem; margin-bottom: .65rem; }
        .pos-badge { font-size: .72rem; padding: .25rem .5rem; font-weight: 600; }
        .pos-actions { gap: .35rem; margin-bottom: .65rem; }
        .pos-actions button { padding: .45rem .65rem; font-size: .75rem; flex: 1 1 calc(50% - .35rem); min-width: 0; }
        #buscar { padding: .6rem; font-size: .92rem; }
        .btn-escanear { padding: 0 .65rem; font-size: .8rem; }
        .tabs { gap: .3rem; }
        .tab { padding: .35rem .55rem; font-size: .75rem; }
        .grid-productos { grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); max-height: 220px; gap: .35rem; }
        .prod-card { padding: .45rem; }
        .prod-card strong { font-size: .75rem; }
        .grid-titulo { font-size: .78rem; margin-bottom: .35rem; }
        .total-grande { font-size: 1.2rem; }
        #btn-cobrar { padding: .65rem; font-size: .92rem; }
        .tipo-doc button { padding: .4rem; font-size: .78rem; }
        .pago-row { flex-direction: column; }
        .pago-row input, .pago-row select { width: 100%; padding: .55rem; font-size: .9rem; }
        .cliente-row { flex-direction: column; }
        .cliente-row .btn { width: 100%; text-align: center; }
        .atajos { font-size: .68rem; }
        .card { padding: .85rem; }

        /* Detalle venta: tarjetas compactas en móvil */
        #carrito { max-height: none; overflow: visible; }
        #carrito table,
        #carrito thead,
        #carrito tbody,
        #carrito tr,
        #carrito td { display: block; width: 100%; }
        #carrito table { table-layout: auto; border-collapse: separate; border-spacing: 0 .45rem; }
        #carrito thead { display: none; }
        #carrito tbody tr {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            grid-template-areas:
                "prod prod prod quitar"
                "cant desc sub sub";
            gap: .35rem .45rem;
            align-items: center;
            padding: .6rem .65rem;
            border: 1px solid #e2e8f0;
            border-radius: .55rem;
            background: #f8fafc;
        }
        #carrito tbody td { border: none; padding: 0; text-align: left; }
        #carrito .col-producto {
            grid-area: prod;
            width: auto;
            padding-right: 1.5rem;
            margin: 0;
        }
        #carrito .carrito-nombre {
            font-size: .9rem;
            line-height: 1.25;
            margin-bottom: .12rem;
        }
        #carrito .carrito-barra-desktop { display: none; }
        #carrito .carrito-meta {
            display: flex;
            align-items: center;
            gap: .35rem;
            font-size: .72rem;
            color: #64748b;
            line-height: 1.3;
            min-width: 0;
        }
        #carrito .carrito-barra-inline {
            font-family: ui-monospace, monospace;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
            flex: 1 1 auto;
        }
        #carrito .carrito-um-pu {
            white-space: nowrap;
            flex-shrink: 0;
            font-weight: 700;
            color: #475569;
        }
        #carrito .col-only-desktop { display: none !important; }
        #carrito .col-quitar {
            grid-area: quitar;
            position: static;
            width: auto;
            padding: 0;
            justify-self: end;
            align-self: start;
        }
        #carrito .col-quitar .btn-quitar {
            width: 30px;
            height: 30px;
            font-size: .95rem;
            line-height: 1;
        }
        #carrito .col-cant {
            grid-area: cant;
            display: flex;
            align-items: center;
            gap: .25rem;
            width: auto;
            margin: 0;
            font-size: .8rem;
        }
        #carrito .col-cant::before { content: none; }
        #carrito .col-desc {
            grid-area: desc;
            display: flex;
            align-items: center;
            gap: .2rem;
            width: auto;
            margin: 0;
            font-size: .75rem;
            color: #64748b;
        }
        #carrito .col-desc::before { content: 'Desc'; font-size: .68rem; font-weight: 700; }
        #carrito .col-sub {
            grid-area: sub;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            width: auto;
            margin: 0;
            padding: 0;
            border: none;
            font-size: .92rem;
            font-weight: 700;
        }
        #carrito .col-sub::before { content: none; }
        #carrito .col-cant .qty-btn { width: 28px; height: 28px; }
        #carrito .col-cant .qty-num { min-width: 1.1rem; text-align: center; font-weight: 700; }
        #carrito .inp-cant-kg,
        #carrito .inp-desc { max-width: 3.5rem; padding: .3rem .35rem; font-size: .82rem; }
        .totales .fila-descuento { flex-wrap: wrap; }
        .totales .fila-descuento input { width: 6rem; }
    }
    @media (max-width: 480px) {
        .pos-badge { font-size: .68rem; }
        .pos-actions button { font-size: .7rem; padding: .4rem .5rem; }
        .grid-productos { grid-template-columns: repeat(auto-fill, minmax(82px, 1fr)); }
        #carrito tbody tr {
            grid-template-columns: 1fr auto;
            grid-template-areas:
                "prod quitar"
                "cant cant"
                "desc sub";
        }
        #carrito .col-sub { justify-content: space-between; }
        #carrito .col-sub::before { content: 'Sub'; font-size: .72rem; font-weight: 700; color: #64748b; }
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
            <div class="tabs-cat-block">
                <div class="tabs" id="tabs-cat">
                    <button type="button" class="tab activo" data-id="todos">Todos</button>
                    @foreach($categorias as $cat)
                        <button type="button" class="tab" data-id="{{ $cat->id }}">{{ $cat->nombre }}</button>
                    @endforeach
                </div>
                <button type="button" class="btn-tabs-mas" id="btn-tabs-mas" hidden>Ver más categorías</button>
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

        <div class="card card-detalle-venta" style="margin:0;flex:1">
            <strong>Detalle venta</strong>
            <div id="carrito"><p style="color:#64748b">Sin productos</p></div>
            <div class="totales" style="margin-top:.75rem;border-top:1px solid #e2e8f0;padding-top:.5rem">
                <div class="totales-fila"><span class="lbl-total">Subtotal:</span><span class="val-total">S/ <span id="subtotal">0.00</span></span></div>
                <div class="totales-fila"><span class="lbl-total">IGV incl.:</span><span class="val-total">S/ <span id="igv">0.00</span></span></div>
                <div class="fila-descuento">
                    <span class="lbl-total">Descuento:</span>
                    <span>S/ <input type="number" id="descuento-global" value="0" min="0" step="1" inputmode="numeric"></span>
                </div>
                <div class="total-grande"><span>TOTAL:</span><span class="val-total">S/ <span id="total">0.00</span></span></div>
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
                <p class="mixto-ayuda">Yape, Plin y Tarjeta: monto exacto (sin vuelto). Efectivo: puede incluir vuelto si la suma supera el total.</p>
                <div class="mixto-grid">
                    <div><label>Efectivo</label><input type="number" id="mix_efectivo" min="0" step="0.01" placeholder="0.00"></div>
                    <div><label>Yape</label><input type="number" id="mix_yape" min="0" step="0.01" placeholder="0.00"></div>
                    <div><label>Plin</label><input type="number" id="mix_plin" min="0" step="0.01" placeholder="0.00"></div>
                    <div><label>Tarjeta</label><input type="number" id="mix_tarjeta" min="0" step="0.01" placeholder="0.00"></div>
                </div>
                <div class="mixto-total" id="mixto-estado">Distribuya el total entre los medios de pago.</div>
            </div>
            <div class="pago-row" id="fila-efectivo">
                <input type="text" id="monto_recibido" placeholder="Recibido S/" inputmode="decimal" autocomplete="off" spellcheck="false">
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
let rafTotales = 0;
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
let cobrando = false;
let categoriaGridActiva = 'todos';
let topFetchSeq = 0;
let topFetchAbort = null;
const topCacheCliente = new Map();
const topInicial = @json($topProductosInicial ?? []);
const impresoraPrefs = @json($impresoraPrefs ?? ['auto_imprimir' => true]);

function urlTicketImpresion(url) {
    if (!url || !impresoraPrefs.auto_imprimir) return url;
    return url + (url.includes('?') ? '&' : '?') + 'imprimir=1';
}

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

async function cargarMasVendidos(categoriaId, forzar = false) {
    categoriaGridActiva = categoriaId;

    if (!forzar && topCacheCliente.has(categoriaId)) {
        pintarGridTop(topCacheCliente.get(categoriaId), categoriaId);
        return;
    }

    if (topFetchAbort) topFetchAbort.abort();
    topFetchAbort = new AbortController();
    const seq = ++topFetchSeq;

    grid.classList.add('grid-cargando');

    try {
        const params = new URLSearchParams({ categoria_id: categoriaId });
        const res = await fetch(`{{ route('pos.mas-vendidos') }}?${params}`, {
            signal: topFetchAbort.signal,
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) throw new Error('No se pudo cargar el top de productos');
        const items = await res.json();
        if (seq !== topFetchSeq) return;

        topCacheCliente.set(categoriaId, items);
        pintarGridTop(items, categoriaId);
    } catch (e) {
        if (e.name === 'AbortError') return;
        if (seq !== topFetchSeq) return;
        document.getElementById('grid-titulo').textContent = 'Top 5 más vendidos';
        grid.innerHTML = '<p style="color:#991b1b;grid-column:1/-1">No se pudo actualizar el catálogo rápido.</p>';
    } finally {
        if (seq === topFetchSeq) grid.classList.remove('grid-cargando');
    }
}

function pintarGridTop(items, categoriaId) {
    const catNombre = categoriaId === 'todos'
        ? 'todas las categorías'
        : (document.querySelector(`.tab[data-id="${categoriaId}"]`)?.textContent || '');
    document.getElementById('grid-titulo').textContent =
        `Top 5 más vendidos${categoriaId !== 'todos' ? ' · ' + catNombre : ''}`;

    grid.innerHTML = (items || []).map(p => cardProducto(p)).join('')
        || '<p style="color:#64748b;grid-column:1/-1">Sin productos en esta categoría</p>';
}

function cardProducto(p) {
    const barra = p.codigo_barra || p.sku || '—';
    return `
        <div class="prod-card" data-id="${p.id}" data-nombre="${esc(p.nombre)}" data-barra="${esc(barra)}"
             data-precio="${p.precio_venta}" data-stock="${p.stock_disponible}" data-min="${p.stock_minimo || 0}" data-igv="${p.afecto_igv ? 1 : 0}" data-um="${esc(p.unidad_medida || 'UND')}">
            <span class="prod-barra">${esc(barra)}</span>
            <strong>${esc(p.nombre)}</strong>
            <small>${textoPrecioStockUm(p)}</small>
        </div>`;
}

function esc(s) { return String(s ?? '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

function formatoStock(stock) {
    const n = +stock;
    return Number.isFinite(n) ? n.toFixed(2) : '0.00';
}

function textoPrecioStockUm(p) {
    const um = esc(String(p.unidad_medida || 'UND').toLowerCase());
    return `S/ ${(+p.precio_venta).toFixed(2)} · Stock: ${formatoStock(p.stock_disponible)} · ${um}`;
}

document.querySelectorAll('#tabs-cat .tab').forEach(tab => {
    tab.onclick = () => {
        if (tab.classList.contains('activo')) return;
        document.querySelectorAll('#tabs-cat .tab').forEach(t => t.classList.remove('activo'));
        tab.classList.add('activo');
        cargarMasVendidos(tab.dataset.id);
        aplicarTabsCategorias();
    };
});

const TABS_VISIBLES_INICIO = 8;
const TABS_PASO = 6;
let tabsVisibles = TABS_VISIBLES_INICIO;

function aplicarTabsCategorias() {
    const btn = document.getElementById('btn-tabs-mas');
    const tabs = [...document.querySelectorAll('#tabs-cat .tab')];
    const esMovil = window.matchMedia('(max-width: 900px)').matches;

    if (!btn || !tabs.length) return;

    if (!esMovil) {
        tabs.forEach(t => t.classList.remove('tab-colapsada'));
        btn.hidden = true;
        btn.classList.remove('visible');
        return;
    }

    const activoIdx = tabs.findIndex(t => t.classList.contains('activo'));
    tabs.forEach((t, i) => {
        const visible = i < tabsVisibles || i === activoIdx;
        t.classList.toggle('tab-colapsada', !visible);
    });

    const ocultas = tabs.filter(t => t.classList.contains('tab-colapsada')).length;
    if (ocultas > 0) {
        const paso = Math.min(TABS_PASO, ocultas);
        btn.textContent = `Ver más categorías (+${paso})`;
        btn.hidden = false;
        btn.classList.add('visible');
    } else {
        btn.hidden = true;
        btn.classList.remove('visible');
    }
}

document.getElementById('btn-tabs-mas')?.addEventListener('click', () => {
    tabsVisibles += TABS_PASO;
    aplicarTabsCategorias();
});

window.addEventListener('resize', () => {
    if (!window.matchMedia('(max-width: 900px)').matches) {
        tabsVisibles = TABS_VISIBLES_INICIO;
    }
    aplicarTabsCategorias();
});

aplicarTabsCategorias();

topCacheCliente.set('todos', topInicial);
pintarGridTop(topInicial, 'todos');

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
                 data-precio="${p.precio_venta}" data-stock="${p.stock_disponible}" data-min="${p.stock_minimo || 0}" data-igv="${p.afecto_igv ? 1 : 0}" data-um="${esc(p.unidad_medida || 'UND')}">
                <strong>${esc(p.nombre)}</strong><br>
                <small><span style="font-family:monospace">${esc(barra)}</span> · Stock: ${formatoStock(p.stock_disponible)} · ${esc((p.unidad_medida || 'UND').toLowerCase())} · S/ ${(+p.precio_venta).toFixed(2)}</small>
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
    return agregar(
        p.id, p.nombre, p.codigo_barra || p.sku || '', +p.precio_venta, +p.stock_disponible,
        p.afecto_igv ? 1 : 0, +p.stock_minimo || 0, p.unidad_medida || 'UND'
    );
}

function esPesable(unidad) {
    return String(unidad || 'UND').toUpperCase() === 'KG';
}

function agregarDesdeFila(row) {
    if (!row) return false;
    const id = +row.dataset.id;
    const nombre = row.dataset.nombre;
    if (!Number.isFinite(id) || id <= 0 || !nombre) return false;
    return agregar(id, nombre, row.dataset.barra || '', +row.dataset.precio, +row.dataset.stock, +row.dataset.igv, +row.dataset.min, row.dataset.um || 'UND');
}

function agregar(id, nombre, codigoBarra, precio, stock, afectoIgv = 1, stockMinimo = 0, unidadMedida = 'UND') {
    id = +id;
    nombre = String(nombre || '').trim();
    precio = +precio;
    stock = +stock || 0;
    stockMinimo = +stockMinimo || 0;
    afectoIgv = +afectoIgv ? 1 : 0;
    const pesable = esPesable(unidadMedida);
    if (!Number.isFinite(id) || id <= 0 || !nombre || !Number.isFinite(precio) || precio < 0) return false;

    const existente = carrito.find(l => l.producto_id === id);
    let cantidadFinal;
    if (existente) {
        existente.cantidad = pesable
            ? +(existente.cantidad + 0.25).toFixed(4)
            : existente.cantidad + 1;
        existente.stock = stock;
        existente.stock_minimo = stockMinimo;
        cantidadFinal = existente.cantidad;
    } else {
        const cantidadInicial = pesable ? 0.25 : 1;
        carrito.push({
            producto_id: id, nombre, codigo_barra: codigoBarra || '—',
            precio, cantidad: cantidadInicial, stock, stock_minimo: stockMinimo,
            descuento_pct: 0, afecto_igv: afectoIgv, unidad_medida: unidadMedida, pesable
        });
        cantidadFinal = cantidadInicial;
    }
    const msg = document.getElementById('msg');
    if (!pesable) {
        const restante = stock - cantidadFinal;
        if (restante < 0) {
            msg.style.color = '#991b1b';
            msg.textContent = `⚠ ${nombre}: stock insuficiente (quedan ${formatoStock(stock)})`;
        } else if (stockMinimo > 0 && restante <= stockMinimo) {
            msg.style.color = '#92400e';
            msg.textContent = `⚠ ${nombre}: stock bajo — quedarán ${formatoStock(restante)} (mín. ${formatoStock(stockMinimo)})`;
        } else {
            msg.textContent = '';
        }
    } else {
        msg.textContent = '';
    }
    reflejarLineaCarrito(carrito.findIndex(l => l.producto_id === id));
    return true;
}

function etiquetaUm(unidad) {
    const u = String(unidad || 'UND').toUpperCase();
    return u === 'UND' ? 'und' : u.toLowerCase();
}

function cantidadKgTexto(cantidad) {
    const n = +cantidad;
    if (!Number.isFinite(n)) return '0';
    return n.toFixed(3).replace(/\.?0+$/, '');
}

function actualizarStockEnCache(lineas) {
    topCacheCliente.forEach(items => {
        lineas.forEach(l => {
            const p = items.find(x => x.id === l.producto_id);
            if (p) {
                p.stock_disponible = Math.max(0, (+p.stock_disponible || 0) - (+l.cantidad || 0));
            }
        });
    });
}

function actualizarStockEnGrid(lineas) {
    actualizarStockEnCache(lineas);
    lineas.forEach(l => {
        grid.querySelectorAll(`.prod-card[data-id="${l.producto_id}"]`).forEach(card => {
            const stock = Math.max(0, (+card.dataset.stock || 0) - (+l.cantidad || 0));
            card.dataset.stock = stock;
            const small = card.querySelector('small');
            if (small) {
                small.textContent = textoPrecioStockUm({
                    precio_venta: +card.dataset.precio,
                    stock_disponible: stock,
                    unidad_medida: card.dataset.um || 'UND',
                });
            }
        });
    });
}

function parseMontoRecibido(val) {
    const n = parseFloat(String(val || '').replace(',', '.').trim());
    return Number.isFinite(n) && n >= 0 ? n : 0;
}

function leerMontoRecibido() {
    return parseMontoRecibido(document.getElementById('monto_recibido').value);
}

function liberarCampoEfectivo() {
    const inp = document.getElementById('monto_recibido');
    inp.disabled = false;
    inp.readOnly = false;
    inp.value = '';
    document.getElementById('vuelto').value = '';
}

function encolarTotales() {
    if (rafTotales) return;
    rafTotales = requestAnimationFrame(() => {
        rafTotales = 0;
        actualizarTotales();
    });
}

function filaCarritoHtml(l, i) {
    const sub = subtotalLinea(l);
    const um = etiquetaUm(l.unidad_medida);
    const barra = l.codigo_barra || '—';
    const pu = `S/ ${l.precio.toFixed(2)}`;
    return `<tr data-idx="${i}">
        <td class="col-producto">
            <span class="carrito-nombre">${l.nombre}</span>
            <span class="carrito-barra carrito-barra-desktop">${barra}</span>
            <span class="carrito-meta">
                <span class="carrito-barra-inline">${barra}</span>
                <span class="carrito-um-pu">${um} · ${pu}</span>
            </span>
        </td>
        <td class="col-um col-only-desktop">${um}</td>
        <td class="col-pu col-only-desktop">${pu}</td>
        <td class="col-cant">
            ${l.pesable
                ? `<input type="number" class="inp-cant inp-cant-kg" data-i="${i}" value="${cantidadKgTexto(l.cantidad)}" min="0.001" step="0.01" inputmode="decimal">`
                : `<button type="button" class="qty-btn" data-i="${i}" data-d="-1">−</button>
            <span class="qty-num">${l.cantidad}</span>
            <button type="button" class="qty-btn" data-i="${i}" data-d="1">+</button>`}
        </td>
        <td class="col-desc"><input type="number" class="inp-desc" data-i="${i}" value="${l.descuento_pct}" min="0" max="100" step="1" inputmode="numeric"></td>
        <td class="col-sub celda-sub">S/ ${sub.toFixed(2)}</td>
        <td class="col-quitar"><button type="button" class="btn-quitar" data-i="${i}" style="border:none;background:none;color:#dc2626;cursor:pointer">✕</button></td>
    </tr>`;
}

function htmlTablaCarrito() {
    return `<table id="carrito-tabla"><thead><tr>
            <th class="col-producto">Producto</th>
            <th class="col-um">UM</th>
            <th class="col-pu">P.U.</th>
            <th class="col-cant">Cant</th>
            <th class="col-desc">Desc%</th>
            <th class="col-sub">Sub</th>
            <th class="col-quitar"></th>
        </tr></thead><tbody>` +
        carrito.map((l, i) => filaCarritoHtml(l, i)).join('') +
        '</tbody></table>';
}

function sincronizarIndiceFilas() {
    document.querySelectorAll('#carrito tbody tr').forEach((tr, i) => {
        tr.dataset.idx = i;
        tr.querySelectorAll('[data-i]').forEach(el => { el.dataset.i = i; });
    });
}

function reflejarLineaCarrito(i) {
    const el = document.getElementById('carrito');
    if (!carrito.length) {
        el.innerHTML = '<p style="color:#64748b">Sin productos</p>';
        encolarTotales();
        return;
    }
    if (!el.querySelector('#carrito-tabla')) {
        el.innerHTML = htmlTablaCarrito();
        encolarTotales();
        return;
    }
    const tbody = el.querySelector('#carrito tbody');
    const html = filaCarritoHtml(carrito[i], i);
    if (tbody.children[i]) {
        tbody.children[i].outerHTML = html;
    } else {
        tbody.insertAdjacentHTML('beforeend', html);
    }
    sincronizarIndiceFilas();
    encolarTotales();
}

function render() {
    const el = document.getElementById('carrito');
    if (!carrito.length) {
        el.innerHTML = '<p style="color:#64748b">Sin productos</p>';
        encolarTotales();
        return;
    }
    el.innerHTML = htmlTablaCarrito();
    encolarTotales();
}

function subtotalLinea(l) {
    const bruto = l.precio * l.cantidad;
    return bruto - bruto * (l.descuento_pct / 100);
}

function calcularTotales() {
    let subtotal = 0, igv = 0;
    carrito.forEach(l => {
        const linea = subtotalLinea(l);
        subtotal += linea;
        if (l.afecto_igv) igv += linea * TASA_IGV / (1 + TASA_IGV);
    });
    const descGlobal = Math.min(subtotal, Math.round(+document.getElementById('descuento-global').value || 0));
    const total = subtotal - descGlobal;
    return { subtotal, igv, descGlobal, total };
}

function actualizarTotales() {
    const t = calcularTotales();
    document.getElementById('subtotal').textContent = t.subtotal.toFixed(2);
    document.getElementById('igv').textContent = t.igv.toFixed(2);
    document.getElementById('total').textContent = t.total.toFixed(2);
    calcularVuelto();
    actualizarMixtoEstado();
    if (document.getElementById('medio_pago').value !== 'mixto') {
        actualizarBotonCobrar();
    }
}

function actualizarCeldaSubtotal(fila, linea) {
    const celda = fila?.querySelector('.celda-sub');
    if (celda) celda.textContent = 'S/ ' + subtotalLinea(linea).toFixed(2);
}

const carritoEl = document.getElementById('carrito');
carritoEl.addEventListener('click', e => {
    const qb = e.target.closest('.qty-btn');
    if (qb) {
        const i = +qb.dataset.i;
        const d = +qb.dataset.d;
        const paso = carrito[i].pesable ? 0.25 : 1;
        carrito[i].cantidad = +(carrito[i].cantidad + d * paso).toFixed(4);
        if (carrito[i].cantidad <= 0) {
            carrito.splice(i, 1);
            render();
        } else {
            reflejarLineaCarrito(i);
        }
        return;
    }
    const bq = e.target.closest('.btn-quitar');
    if (bq) {
        carrito.splice(+bq.dataset.i, 1);
        render();
    }
});

carritoEl.addEventListener('input', e => {
    const inpDesc = e.target.closest('.inp-desc');
    if (inpDesc) {
        const i = +inpDesc.dataset.i;
        carrito[i].descuento_pct = Math.min(100, Math.max(0, parseInt(inpDesc.value, 10) || 0));
        actualizarCeldaSubtotal(inpDesc.closest('tr'), carrito[i]);
        encolarTotales();
        return;
    }
    const inpCant = e.target.closest('.inp-cant');
    if (inpCant) {
        const i = +inpCant.dataset.i;
        const val = +inpCant.value || 0;
        if (val > 0) {
            carrito[i].cantidad = +val.toFixed(4);
            actualizarCeldaSubtotal(inpCant.closest('tr'), carrito[i]);
            encolarTotales();
        }
    }
});

carritoEl.addEventListener('change', e => {
    const inpDesc = e.target.closest('.inp-desc');
    if (inpDesc) {
        const i = +inpDesc.dataset.i;
        carrito[i].descuento_pct = Math.min(100, Math.max(0, parseInt(inpDesc.value, 10) || 0));
        inpDesc.value = carrito[i].descuento_pct;
        actualizarCeldaSubtotal(inpDesc.closest('tr'), carrito[i]);
        encolarTotales();
        return;
    }
    const inpCant = e.target.closest('.inp-cant');
    if (inpCant) {
        const i = +inpCant.dataset.i;
        const val = +inpCant.value || 0;
        if (val <= 0) {
            carrito.splice(i, 1);
            render();
        } else {
            carrito[i].cantidad = +val.toFixed(4);
            reflejarLineaCarrito(i);
        }
    }
});

function leerMontosMixto() {
    return {
        efectivo: +document.getElementById('mix_efectivo').value || 0,
        yape: +document.getElementById('mix_yape').value || 0,
        plin: +document.getElementById('mix_plin').value || 0,
        tarjeta: +document.getElementById('mix_tarjeta').value || 0,
    };
}

function analizarPagoMixto(total) {
    const m = leerMontosMixto();
    const noEfectivo = m.yape + m.plin + m.tarjeta;
    const suma = noEfectivo + m.efectivo;
    const tol = 0.01;
    const efectivoNecesario = Math.max(0, total - noEfectivo);

    if (suma <= tol) {
        return { ok: false, estado: 'pendiente', mensaje: `Total a distribuir: S/ ${total.toFixed(2)}`, suma, vuelto: 0, pagos: [] };
    }

    if (m.yape > total + tol) {
        return { ok: false, estado: 'err', mensaje: `Yape (S/ ${m.yape.toFixed(2)}) no puede superar el total (S/ ${total.toFixed(2)}).` };
    }
    if (m.plin > total + tol) {
        return { ok: false, estado: 'err', mensaje: `Plin (S/ ${m.plin.toFixed(2)}) no puede superar el total (S/ ${total.toFixed(2)}).` };
    }
    if (m.tarjeta > total + tol) {
        return { ok: false, estado: 'err', mensaje: `Tarjeta (S/ ${m.tarjeta.toFixed(2)}) no puede superar el total (S/ ${total.toFixed(2)}).` };
    }
    if (noEfectivo > total + tol) {
        return {
            ok: false,
            estado: 'err',
            mensaje: `Yape/Plin/Tarjeta suman S/ ${noEfectivo.toFixed(2)} y superan el total (S/ ${total.toFixed(2)}). No hay vuelto en medios digitales.`,
        };
    }

    if (suma < total - tol) {
        return {
            ok: false,
            estado: 'err',
            mensaje: `Faltan S/ ${(total - suma).toFixed(2)} (suma S/ ${suma.toFixed(2)} de S/ ${total.toFixed(2)})`,
            suma,
            vuelto: 0,
        };
    }

    let vuelto = 0;
    let montoRecibido = null;
    const pagos = [];

    if (suma > total + tol) {
        if (m.efectivo <= 0) {
            return {
                ok: false,
                estado: 'err',
                mensaje: `Sobran S/ ${(suma - total).toFixed(2)}. Solo el efectivo genera vuelto; reduzca Yape/Plin/Tarjeta o indique efectivo entregado.`,
            };
        }
        vuelto = +(suma - total).toFixed(2);
        if (m.efectivo + tol < vuelto) {
            return {
                ok: false,
                estado: 'err',
                mensaje: `El vuelto (S/ ${vuelto.toFixed(2)}) debe salir del efectivo entregado (S/ ${m.efectivo.toFixed(2)}).`,
            };
        }
        montoRecibido = m.efectivo;
    } else if (m.efectivo > 0) {
        const rec = leerMontoRecibido();
        if (rec > 0) {
            if (rec + tol < m.efectivo) {
                return { ok: false, estado: 'err', mensaje: 'Efectivo recibido insuficiente para el monto en efectivo.' };
            }
            montoRecibido = rec;
            vuelto = +(rec - m.efectivo).toFixed(2);
        } else {
            montoRecibido = m.efectivo;
        }
    }

    if (efectivoNecesario > tol) {
        pagos.push({ medio: 'efectivo', monto: +efectivoNecesario.toFixed(2) });
    }
    if (m.yape > 0) pagos.push({ medio: 'yape', monto: +m.yape.toFixed(2) });
    if (m.plin > 0) pagos.push({ medio: 'plin', monto: +m.plin.toFixed(2) });
    if (m.tarjeta > 0) pagos.push({ medio: 'tarjeta', monto: +m.tarjeta.toFixed(2) });

    let mensaje;
    let estado = 'ok';
    if (vuelto > tol) {
        mensaje = `✓ Vuelto en efectivo: S/ ${vuelto.toFixed(2)} (pagado S/ ${suma.toFixed(2)} · total S/ ${total.toFixed(2)})`;
    } else {
        mensaje = `✓ Pagos cuadran: S/ ${total.toFixed(2)}`;
    }

    return { ok: true, estado, mensaje, suma, vuelto, montoRecibido, pagos, excedente: suma > total + tol };
}

function calcularVuelto() {
    const t = calcularTotales();
    const medio = document.getElementById('medio_pago').value;
    const vueltoEl = document.getElementById('vuelto');

    if (medio === 'mixto') {
        const analisis = analizarPagoMixto(t.total);
        vueltoEl.value = analisis.vuelto > 0.01 ? analisis.vuelto.toFixed(2) : '';
        return;
    }

    const rec = leerMontoRecibido();
    vueltoEl.value = rec > 0 ? Math.max(0, rec - t.total).toFixed(2) : '';
}

function actualizarBotonCobrar(analisisMixto = null) {
    const btn = document.getElementById('btn-cobrar');
    if (!btn) return;

    const t = calcularTotales();
    const medio = document.getElementById('medio_pago').value;
    let puede = carrito.length > 0;
    let titulo = '';

    if (puede && medio === 'efectivo') {
        const rec = leerMontoRecibido();
        if (rec > 0 && rec + 0.01 < t.total) {
            puede = false;
            titulo = 'Monto recibido insuficiente';
        }
    } else if (puede && medio === 'mixto') {
        const analisis = analisisMixto || analizarPagoMixto(t.total);
        if (!analisis.ok && analisis.estado === 'err') {
            puede = false;
            titulo = analisis.mensaje;
        }
    }

    btn.disabled = !puede;
    btn.title = titulo;
}

function actualizarMixtoEstado() {
    const el = document.getElementById('mixto-estado');
    if (!el || document.getElementById('medio_pago').value !== 'mixto') return;

    const t = calcularTotales();
    const analisis = analizarPagoMixto(t.total);

    el.textContent = analisis.mensaje;
    el.className = 'mixto-total' + (analisis.estado === 'ok' ? ' ok' : analisis.estado === 'warn' ? ' warn' : analisis.estado === 'pendiente' ? '' : ' err');
    calcularVuelto();
    actualizarBotonCobrar(analisis);
}

function actualizarFilasPago() {
    const medio = document.getElementById('medio_pago').value;
    document.getElementById('fila-mixto').style.display = medio === 'mixto' ? 'block' : 'none';

    let muestraEfectivo = medio === 'efectivo';
    if (medio === 'mixto') {
        const m = leerMontosMixto();
        const t = calcularTotales();
        const analisis = analizarPagoMixto(t.total);
        // Recibido extra solo si cuadra exacto y hay parte en efectivo (billetes de más)
        muestraEfectivo = m.efectivo > 0 && analisis.ok && !analisis.excedente;
    }

    document.getElementById('fila-efectivo').style.display = muestraEfectivo ? 'flex' : 'none';
    if (medio !== 'mixto') {
        ['mix_efectivo', 'mix_yape', 'mix_plin', 'mix_tarjeta'].forEach(id => document.getElementById(id).value = '');
    }
    actualizarMixtoEstado();
}

document.getElementById('descuento-global').addEventListener('input', actualizarTotales);
document.getElementById('descuento-global').addEventListener('blur', function () {
    this.value = Math.max(0, Math.round(+this.value || 0));
    actualizarTotales();
});
document.getElementById('monto_recibido').addEventListener('input', () => {
    calcularVuelto();
    actualizarBotonCobrar();
});
document.getElementById('monto_recibido').addEventListener('blur', function () {
    const n = parseMontoRecibido(this.value);
    this.value = n > 0 ? n.toFixed(2) : '';
    calcularVuelto();
    actualizarBotonCobrar();
});
document.getElementById('medio_pago').onchange = () => {
    actualizarFilasPago();
    actualizarBotonCobrar();
};
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
    liberarCampoEfectivo();
    document.getElementById('medio_pago').value = 'efectivo';
    ['mix_efectivo', 'mix_yape', 'mix_plin', 'mix_tarjeta'].forEach(id => document.getElementById(id).value = '');
    actualizarFilasPago();
    document.getElementById('msg').textContent = '';
    render();
    buscar.focus({ preventScroll: true });
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
    const btn = document.getElementById('btn-cobrar');
    if (cobrando || !carrito.length || btn.disabled) return;

    const t = calcularTotales();
    const medio = document.getElementById('medio_pago').value;
    let montoRecibidoEnvio = medio === 'efectivo'
        ? leerMontoRecibido()
        : null;
    let pagosMixtos = null;

    if (medio === 'efectivo' && montoRecibidoEnvio < t.total) {
        return alert('Monto recibido insuficiente');
    }

    if (medio === 'mixto') {
        const analisis = analizarPagoMixto(t.total);
        if (!analisis.ok) {
            return alert(analisis.mensaje || 'Revise los montos del pago mixto');
        }
        pagosMixtos = analisis.pagos;
        montoRecibidoEnvio = analisis.montoRecibido;
    }

    const clienteId = document.getElementById('cliente-id').value || null;
    if (tipoDocumento === 'factura' && !clienteId) {
        return alert('La factura requiere un cliente con RUC. Busque o registre el cliente.');
    }

    const lineasVendidas = carrito.map(l => ({
        producto_id: l.producto_id,
        cantidad: l.cantidad,
    }));

    const msg = document.getElementById('msg');
    msg.textContent = 'Procesando...';
    msg.style.color = '#64748b';
    cobrando = true;
    btn.disabled = true;

    try {
        const res = await fetch('{{ route('pos.cobrar') }}', {
            method: 'POST', headers: headers(),
            body: JSON.stringify({
                lineas: carrito.map(l => ({ producto_id: l.producto_id, cantidad: l.cantidad, descuento_pct: l.descuento_pct })),
                medio_pago: medio,
                tipo_documento: tipoDocumento,
                cliente_id: clienteId,
                descuento_global: t.descGlobal,
                monto_recibido: (medio === 'efectivo' || (medio === 'mixto' && pagosMixtos?.some(p => p.medio === 'efectivo')))
                    ? montoRecibidoEnvio || null
                    : null,
                pagos_mixtos: pagosMixtos,
            })
        });
        const data = await res.json();
        if (data.ok) {
            cobrando = false;
            msg.style.color = '#065f46';
            msg.textContent = `✓ ${data.tipo_etiqueta} ${data.numero} — Total S/ ${data.total}` + (data.vuelto != null ? ` · Vuelto S/ ${data.vuelto}` : '') + etiquetaSunatCobro(data);
            if (data.preview) {
                previews[tipoDocumento] = data.preview;
                actualizarPreview(data.preview);
            }
            actualizarStockEnGrid(lineasVendidas);
            limpiarVenta();
            if (data.ticket_url) {
                setTimeout(() => {
                    const w = window.open(urlTicketImpresion(data.ticket_url), '_blank', 'noopener');
                    if (!w) {
                        msg.textContent += ' · Abra el ticket desde Ventas si el navegador bloqueó la ventana.';
                    }
                }, 150);
            }
        } else {
            msg.style.color = '#991b1b';
            msg.textContent = data.error || 'Error al cobrar';
        }
    } catch (_) {
        msg.style.color = '#991b1b';
        msg.textContent = 'Error de conexión al cobrar. Intente de nuevo.';
    } finally {
        cobrando = false;
        actualizarBotonCobrar();
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
actualizarBotonCobrar();

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
