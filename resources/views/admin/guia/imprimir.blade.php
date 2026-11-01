<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guía cliente — VentasÁgil</title>
    <style>
        @page { margin: 18mm 16mm; size: A4; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Segoe UI", system-ui, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #1e293b;
            background: #fff;
            max-width: 210mm;
            margin: 0 auto;
            padding: 12mm;
        }
        .header {
            background: linear-gradient(135deg, #4c1d95, #7c3aed 50%, #a78bfa);
            color: #fff;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .header h1 { font-size: 17pt; font-weight: 700; margin-bottom: 4px; }
        .header p { font-size: 9.5pt; opacity: .92; }
        h2 {
            font-size: 11pt;
            color: #5b21b6;
            margin: 14px 0 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #ddd6fe;
        }
        h3 { font-size: 10pt; color: #334155; margin: 10px 0 6px; }
        p { margin-bottom: 8px; }
        ul, ol { margin: 0 0 10px 18px; }
        li { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f5f3ff; color: #5b21b6; font-weight: 700; }
        .checklist {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            margin: 10px 0;
        }
        .checklist label { display: block; margin-bottom: 6px; font-size: 10pt; }
        .checklist .line { border-bottom: 1px solid #94a3b8; display: inline-block; min-width: 200px; }
        .box-ok { background: #ecfdf5; border-left: 4px solid #10b981; padding: 8px 12px; margin: 8px 0; font-size: 9.5pt; }
        .box-warn { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 8px 12px; margin: 8px 0; font-size: 9.5pt; }
        .box-info { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 8px 12px; margin: 8px 0; font-size: 9.5pt; }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .footer { margin-top: 16px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 8.5pt; color: #64748b; text-align: center; }
        .toolbar { text-align: center; margin-bottom: 12px; }
        .toolbar button, .toolbar a {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 4px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 10pt;
        }
        .btn-print { background: #7c3aed; color: #fff; }
        .btn-back { background: #e2e8f0; color: #334155; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>

<div class="toolbar no-print">
    <button type="button" class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
    <a href="{{ route('admin.guia.index') }}" class="btn-back">← Volver a guía</a>
</div>

<header class="header">
    <h1>VentasÁgil</h1>
    <p>Guía para nuevos clientes — Planes, puesta en marcha y facturación electrónica SUNAT</p>
    <p style="font-size:9pt;margin-top:6px;opacity:.85">https://ventasagil.com</p>
</header>

<h2>1. ¿Qué es VentasÁgil?</h2>
<p>Punto de venta en la nube para bodegas y negocios: vende en caja, controla stock, cierra caja y revisa ventas del día desde celular o computadora.</p>

<h2>2. Planes y precios</h2>
<table>
    <thead>
        <tr><th>Plan</th><th>Arranque</th><th>Mensual (mes 2)</th><th>Ideal para</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Beta</strong></td>
            <td>Gratis</td>
            <td>Gratis 1 mes</td>
            <td>Probar (3 usuarios)</td>
        </tr>
        <tr>
            <td><strong>Básico</strong></td>
            <td>S/ {{ $arranque['monto'] }}</td>
            <td>S/ {{ $planes['basico']['mensual'] }}</td>
            <td>POS + ventas (sin SUNAT)</td>
        </tr>
        <tr>
            <td><strong>Negocio</strong></td>
            <td>S/ {{ $arranque['monto'] }}</td>
            <td>S/ {{ $planes['negocio']['mensual'] }}</td>
            <td>Inventario, compras</td>
        </tr>
        <tr>
            <td><strong>Pro</strong></td>
            <td>S/ {{ $arranque['monto'] }}</td>
            <td>S/ {{ $planes['pro']['mensual'] }}</td>
            <td>Facturación electrónica SUNAT</td>
        </tr>
    </tbody>
</table>
<p><strong>Arranque:</strong> {{ $arranque['detalle'] ?? 'Instalación + primer mes incluidos.' }}</p>

<h2>3. Proceso de alta</h2>
<ol>
    <li>Eliges plan y confirmas pago de arranque.</li>
    <li>Creamos tu empresa y te enviamos acceso.</li>
    <li>Capacitación (1–2 horas).</li>
    <li>Primera venta en tu negocio.</li>
    <li>Si es plan Pro: activamos SUNAT (sección 5).</li>
</ol>

<div class="page-break"></div>

<h2>4. ¿Tienes RUC?</h2>
<div class="two-col">
    <div>
        <h3>Sin RUC</h3>
        <div class="box-warn">Puedes usar ventas y stock, pero <strong>no</strong> boleta electrónica legal.</div>
        <p>Tramitar en SUNAT: inscripción → RUC → régimen → local → series → Nubefact.</p>
    </div>
    <div>
        <h3>Con RUC</h3>
        <div class="box-ok">Plan Pro + Nubefact si estás habilitado como emisor electrónico.</div>
        <p>RET/RMT: confirma con contador boleta y factura electrónica.</p>
    </div>
</div>

<h2>5. Facturación SUNAT (plan Pro)</h2>
<div class="box-info">Envío a SUNAT vía <strong>Nubefact</strong>. Ticket con <strong>QR</strong> válido.</div>
<div class="checklist">
    <label>☐ RUC: <span class="line">&nbsp;</span></label>
    <label>☐ Razón social: <span class="line">&nbsp;</span></label>
    <label>☐ Dirección fiscal: <span class="line">&nbsp;</span></label>
    <label>☐ Series: ☐ Boleta ☐ Factura</label>
    <label>☐ Cuenta nubefact.com</label>
    <label>☐ Ruta API: <span class="line">&nbsp;</span></label>
    <label>☐ Token API: <span class="line">&nbsp;</span></label>
</div>

<h2>6. Datos del cliente</h2>
<table>
    <tr><th style="width:35%">Negocio</th><td>&nbsp;</td></tr>
    <tr><th>WhatsApp</th><td>&nbsp;</td></tr>
    <tr><th>Plan</th><td>☐ Beta ☐ Básico ☐ Negocio ☐ Pro</td></tr>
    <tr><th>Fecha alta</th><td>&nbsp;</td></tr>
</table>

<footer class="footer">
    VentasÁgil · Documento informativo — confirmar obligaciones con SUNAT y contador.<br>
    {{ now()->format('d/m/Y') }}
</footer>

</body>
</html>
