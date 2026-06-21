@extends('layouts.app')

@section('titulo', 'Guía rápida')

@push('head')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { padding: 0 !important; }
        .card { box-shadow: none; border: 1px solid #e2e8f0; }
    }
    .guia h2 { color: #0f766e; font-size: 1.05rem; margin: 1.25rem 0 .5rem; }
    .guia ol { margin-left: 1.25rem; line-height: 1.65; }
    .guia li { margin-bottom: .35rem; }
    .guia p { line-height: 1.6; color: #334155; }
    .guia-kbd { background: #f1f5f9; padding: .1rem .35rem; border-radius: .25rem; font-family: monospace; font-size: .85rem; }
</style>
@endpush

@section('contenido')
<div class="guia" style="max-width:720px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
        <h1>Guía rápida VentaPOS</h1>
        <button type="button" class="btn btn-primary no-print" onclick="window.print()">Imprimir / PDF</button>
    </div>

    <div class="card">
        <p><strong>{{ $usuario->empresa?->nombre }}</strong> — {{ $usuario->nombre }} ({{ $usuario->etiquetaRol() }})</p>
        <p style="margin-top:.5rem;color:#64748b;font-size:.9rem">Use esta guía para operar el día a día. Guárdela como PDF desde el botón Imprimir.</p>
    </div>

    <div class="card">
        <h2>1. Ingresar al sistema</h2>
        <ol>
            <li>Abra la página de login de su empresa.</li>
            <li>Ingrese el <strong>código de empresa</strong> (ej: bodega-el-sol).</li>
            <li>Usuario y contraseña asignados por el administrador.</li>
        </ol>
    </div>

    @if($usuario->tieneModulo('pos'))
    <div class="card">
        <h2>2. Vender en el POS</h2>
        <ol>
            <li>Menú <strong>POS Venta</strong> (o <strong>POS Móvil</strong> desde el celular si está activo).</li>
            <li>Busque el producto por nombre, SKU o escanee el código de barras (<span class="guia-kbd">F3</span> cámara en escritorio).</li>
            <li>Toque el producto para agregarlo al carrito. Ajuste cantidades con + / −.</li>
            <li>Elija medio de pago y pulse <strong>Cobrar</strong> (<span class="guia-kbd">F9</span> en escritorio).</li>
            <li>Se abre el ticket para imprimir o enviar al cliente.</li>
        </ol>
    </div>
    @endif

    @if($usuario->tieneModulo('productos'))
    <div class="card">
        <h2>3. Productos e importación</h2>
        <ol>
            <li><strong>Productos → Importar CSV</strong> para cargar el catálogo masivo.</li>
            <li>Descargue la plantilla, complétela en Excel y súbala.</li>
            <li>Revise alertas de <strong>stock bajo</strong> en Inicio o Productos.</li>
        </ol>
    </div>
    @endif

    @if($usuario->tieneModulo('inventario'))
    <div class="card">
        <h2>4. Inventario</h2>
        <ol>
            <li><strong>Entrada</strong>: mercadería recibida de proveedor.</li>
            <li><strong>Salida</strong>: mermas, consumo interno.</li>
            <li><strong>Ajuste</strong>: conteo físico que no coincide con el sistema.</li>
            <li><strong>Kardex</strong>: historial por producto.</li>
        </ol>
    </div>
    @endif

    @if($usuario->tieneModulo('proveedores') || $usuario->tieneModulo('compras'))
    <div class="card">
        <h2>Compras a proveedor</h2>
        <ol>
            @if($usuario->tieneModulo('proveedores'))
            <li>Registre sus proveedores en <strong>Proveedores</strong> (RUC, razón social, contacto).</li>
            @endif
            @if($usuario->tieneModulo('compras'))
            <li>Ingrese <strong>facturas, guías u órdenes de compra</strong> en Compras → Nueva compra.</li>
            <li>Use <strong>Registrar y recibir stock</strong> para actualizar inventario y costo de compra automáticamente.</li>
            @endif
        </ol>
    </div>
    @endif

    @if($usuario->tieneModulo('cierres'))
    <div class="card">
        <h2>5. Cierre de caja</h2>
        <ol>
            <li>Al final del turno: <strong>Cierre de caja → Cierre X</strong> (arqueo del cajero).</li>
            <li>El supervisor cierra la sesión del cajero cuando corresponda.</li>
            <li><strong>Cierre Z</strong> al final del día (dueño/supervisor).</li>
        </ol>
    </div>
    @endif

    @if($usuario->tieneModulo('reportes'))
    <div class="card">
        <h2>6. Reportes y exportación</h2>
        <ol>
            <li><strong>Reportes → Ventas</strong>: resumen por período, cajero y medio de pago.</li>
            <li>Exporte a <strong>CSV</strong> o <strong>Excel</strong> para contabilidad.</li>
            <li>Pestaña <strong>Inventario</strong>: stock valorizado y movimientos.</li>
        </ol>
    </div>
    @endif

    @if($usuario->tieneModulo('sunat'))
    <div class="card">
        <h2>Facturación electrónica SUNAT</h2>
        <ol>
            <li>Configure el <strong>RUC</strong> en Configuración de empresa.</li>
            <li>Active SUNAT en <strong>Administración → SUNAT</strong> (modo simulación para pruebas).</li>
            <li>Al cobrar en POS, se genera el XML, el estado SUNAT y el <strong>código QR</strong> en el ticket impreso.</li>
            <li>El POS muestra el resultado CPE al cobrar (simulado, aceptado o error).</li>
            <li>Revise el estado en <strong>Ventas → detalle</strong>; reenvíe o descargue XML/CDR si hubo error.</li>
        </ol>
    </div>
    @endif

    <div class="card">
        <h2>Soporte</h2>
        <p>Ante dudas contacte al administrador de su empresa o al soporte VentaPOS por WhatsApp.</p>
    </div>
</div>
@endsection
