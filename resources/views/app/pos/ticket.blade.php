<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>{{ strtoupper($comprobante->tipo_documento) }} {{ $comprobante->serie }}-{{ str_pad($comprobante->numero, 6, '0', STR_PAD_LEFT) }}</title>

    <style>

        body { font-family: 'Courier New', monospace; font-size: 13px; max-width: 360px; margin: 1rem auto; padding: 1rem; color: #000; }

        .centro { text-align: center; }

        hr { border: none; border-top: 1px dashed #000; margin: .5rem 0; }

        .fila { display: flex; justify-content: space-between; margin: .2rem 0; gap: .5rem; }

        .fila span:last-child { text-align: right; }

        .grande { font-size: 16px; font-weight: bold; }

        table { width: 100%; font-size: 11px; border-collapse: collapse; margin: .35rem 0; }

        th, td { text-align: left; padding: .15rem 0; vertical-align: top; }

        th { border-bottom: 1px solid #000; }

        td.num, th.num { text-align: right; }

        .qr-wrap { margin: .5rem auto; padding: 6px; background: #fff; display: inline-block; border: 1px solid #000; }

        .qr-wrap img {

            display: block;

            width: 168px;

            height: 168px;

            image-rendering: pixelated;

            image-rendering: crisp-edges;

        }

        @media print {

            .no-print { display: none; }

            body { margin: 0; padding: .25rem; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            .qr-wrap img { width: 42mm; height: 42mm; }

        }

    </style>

</head>

<body>

    <div class="centro">

        @if($comprobante->empresa->logoUrl())

        <img src="{{ $comprobante->empresa->logoUrl() }}" alt="Logo" style="max-height:56px;max-width:180px;margin-bottom:.35rem"><br>

        @endif

        <strong>{{ $comprobante->empresa->nombre }}</strong><br>

        @if($comprobante->empresa->ruc)

        RUC: {{ $comprobante->empresa->ruc }}<br>

        @endif

        @if($comprobante->empresa->direccion)

        {{ $comprobante->empresa->direccion }}<br>

        @endif

        <span class="grande">{{ strtoupper($comprobante->tipo_documento) }} DE VENTA</span><br>

        {{ $comprobante->serie }}-{{ str_pad($comprobante->numero, 6, '0', STR_PAD_LEFT) }}

    </div>

    <hr>

    <div class="fila"><span>Fecha:</span><span>{{ $comprobante->emitido_en->format('d/m/Y H:i') }}</span></div>

    <div class="fila"><span>Cajero:</span><span>{{ $comprobante->usuario->nombre }}</span></div>

    <div class="fila"><span>Terminal:</span><span>{{ $comprobante->terminal?->codigo ?? (($comprobante->empresa->configuracion ?? [])['terminal'] ?? '01') }}</span></div>

    @if($comprobante->terminal?->local)

    <div class="fila"><span>Local:</span><span>{{ $comprobante->terminal->local }}</span></div>

    @endif

    <hr>

    @if($comprobante->cliente)

    <div class="fila"><span>Cliente:</span><span>{{ $comprobante->cliente->nombre }}</span></div>

    <div class="fila"><span>{{ strtoupper($comprobante->cliente->tipo_documento) }}:</span><span>{{ $comprobante->cliente->numero_documento }}</span></div>

    @else

    <div class="fila"><span>Cliente:</span><span>CONSUMIDOR FINAL</span></div>

    @endif

    <div class="fila"><span>Pago:</span><span>{{ $comprobante->etiquetaMedioPago() }}</span></div>

    <hr>

    <table>

        <thead>

            <tr>

                <th>Producto</th>

                <th class="num">Cant</th>

                <th class="num">P.U.</th>

                <th class="num">Importe</th>

            </tr>

        </thead>

        <tbody>

            @foreach($comprobante->detalles as $d)

            <tr>

                <td>{{ $d->descripcion_producto }}</td>

                <td class="num">{{ number_format($d->cantidad, $d->cantidad == floor($d->cantidad) ? 0 : 2) }}</td>

                <td class="num">{{ number_format($d->precio_unitario, 2) }}</td>

                <td class="num">{{ number_format($d->subtotal, 2) }}</td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <hr>

    <div class="fila"><span>Subtotal:</span><span>S/ {{ number_format($comprobante->subtotal, 2) }}</span></div>

    <div class="fila"><span>IGV incl.:</span><span>S/ {{ number_format($comprobante->valor_igv, 2) }}</span></div>

    @if($comprobante->descuento > 0)

    <div class="fila"><span>Descuento:</span><span>S/ {{ number_format($comprobante->descuento, 2) }}</span></div>

    @endif

    <div class="fila grande"><span>TOTAL:</span><span>S/ {{ number_format($comprobante->total, 2) }}</span></div>

    @if($comprobante->medio_pago === 'efectivo' && $comprobante->monto_recibido !== null)

    <div class="fila"><span>Recibido:</span><span>S/ {{ number_format($comprobante->monto_recibido, 2) }}</span></div>

    <div class="fila"><span>Vuelto:</span><span>S/ {{ number_format($comprobante->vuelto ?? 0, 2) }}</span></div>

    @elseif($comprobante->medio_pago === 'mixto' && $comprobante->monto_recibido !== null)

    <div class="fila"><span>Efectivo recibido:</span><span>S/ {{ number_format($comprobante->monto_recibido, 2) }}</span></div>

    <div class="fila"><span>Vuelto:</span><span>S/ {{ number_format($comprobante->vuelto ?? 0, 2) }}</span></div>

    @endif

    <hr>

    @if($qrDataUri)

    <div class="centro" style="margin-top:.5rem">

        <div style="font-size:11px;margin-bottom:.35rem">

            <strong>COMPROBANTE ELECTRÓNICO</strong><br>

            @if($comprobante->sunat_estado === 'simulado')

            Modo simulación — sin validez tributaria

            @else

            Autorizado por SUNAT

            @endif

        </div>

        <div class="qr-wrap">

            <img src="{{ $qrDataUri }}" alt="QR SUNAT" width="168" height="168">

        </div>

        <div style="font-size:9px;word-break:break-all;margin-top:.35rem;padding:0 .25rem">

            Hash: {{ $comprobante->sunat_hash }}

        </div>

    </div>

    <hr>

    @endif

    <div class="centro" style="font-size:11px;margin-top:.5rem">

        Representación impresa del comprobante<br>

        {{ $comprobante->numero_interno }}

    </div>

    <p class="no-print centro" style="margin-top:1rem">

        <button type="button" onclick="window.print()">Imprimir</button>

        <button type="button" onclick="window.close()">Cerrar</button>

    </p>

    <script>

    (function () {

        function imprimirCuandoListo() {

            var imgs = document.querySelectorAll('img');

            var pendientes = imgs.length;

            if (!pendientes) {

                setTimeout(function () { window.print(); }, 150);

                return;

            }

            imgs.forEach(function (img) {

                if (img.complete) {

                    pendientes--;

                    if (pendientes === 0) setTimeout(function () { window.print(); }, 200);

                } else {

                    img.addEventListener('load', function () {

                        pendientes--;

                        if (pendientes === 0) setTimeout(function () { window.print(); }, 200);

                    });

                    img.addEventListener('error', function () {

                        pendientes--;

                        if (pendientes === 0) setTimeout(function () { window.print(); }, 200);

                    });

                }

            });

        }

        if (document.readyState === 'complete') imprimirCuandoListo();

        else window.addEventListener('load', imprimirCuandoListo);

    })();

    </script>

</body>

</html>


