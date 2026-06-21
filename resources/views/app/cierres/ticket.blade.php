<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Ticket Cierre {{ $cierre->tipo_cierre }} #{{ $cierre->id }}</title>

    <style>

        body { font-family: 'Courier New', monospace; font-size: 13px; max-width: 360px; margin: 1rem auto; padding: 1rem; }

        .centro { text-align: center; }

        hr { border: none; border-top: 1px dashed #000; margin: .5rem 0; }

        .fila { display: flex; justify-content: space-between; margin: .2rem 0; }

        .grande { font-size: 16px; font-weight: bold; }

        table { width: 100%; font-size: 11px; border-collapse: collapse; margin: .35rem 0; }

        th, td { text-align: left; padding: .15rem 0; vertical-align: top; }

        th { border-bottom: 1px solid #000; }

        @media print { .no-print { display: none; } body { margin: 0; } }

    </style>

</head>

<body onload="window.print()">

    <div class="centro">

        <strong>{{ $cierre->empresa->nombre }}</strong><br>

        CIERRE DE CAJA {{ $cierre->tipo_cierre }}<br>

        @if($cierre->tipo_cierre === 'Z')

        REPORTE CONSOLIDADO DEL DÍA

        @endif

    </div>

    <hr>

    <div class="fila"><span>Terminal:</span><span>{{ $cierre->terminal }}</span></div>

    <div class="fila"><span>Caja:</span><span>{{ $cierre->caja ?? '01' }}</span></div>

    @if($cierre->tipo_cierre === 'X')

    <div class="fila"><span>Cajero:</span><span>{{ $cierre->usuario->nombre }}</span></div>

    @else

    <div class="fila"><span>Generado por:</span><span>{{ $cierre->usuario->nombre }}</span></div>

    @endif

    @if($cierre->supervisor && $cierre->tipo_cierre === 'X')

    <div class="fila"><span>Supervisor:</span><span>{{ $cierre->supervisor->nombre }}</span></div>

    @if($cierre->supervisor_usuario_id !== $cierre->usuario_id)

    <div class="centro" style="font-size:11px;margin:.25rem 0">CIERRE SUPERVISADO</div>

    @endif

    @endif

    <div class="fila"><span>Desde:</span><span>{{ $cierre->fecha_inicio->format('d/m/Y H:i') }}</span></div>

    <div class="fila"><span>Hasta:</span><span>{{ $cierre->fecha_fin->format('d/m/Y H:i') }}</span></div>

    <hr>

    <div class="fila"><span>Tickets:</span><span>{{ $cierre->cantidad_ventas }}</span></div>

    <div class="fila"><span>Total ventas:</span><span>S/ {{ number_format($cierre->total_ventas, 2) }}</span></div>

    <div class="fila"><span>Ticket prom.:</span><span>S/ {{ number_format($cierre->ticket_promedio, 2) }}</span></div>

    <div class="fila"><span>IGV:</span><span>S/ {{ number_format($cierre->total_igv, 2) }}</span></div>

    <hr>

    <div class="fila"><span>Efectivo sistema:</span><span>S/ {{ number_format($cierre->total_efectivo, 2) }}</span></div>

    <div class="fila"><span>Tarjeta:</span><span>S/ {{ number_format($cierre->total_tarjeta, 2) }}</span></div>

    <div class="fila"><span>Yape/Plin/Otros:</span><span>S/ {{ number_format($cierre->total_otros, 2) }}</span></div>



    @if($cierre->tipo_cierre === 'Z' && ($porCajero ?? collect())->count())

    <hr>

    <div class="centro" style="font-weight:bold;margin:.25rem 0">DETALLE POR CAJERO</div>

    <div class="fila"><span>Cajeros con cierre X:</span><span>{{ $porCajero->count() }}</span></div>

    <table>

        <thead>

            <tr><th>Cajero</th><th>X</th><th>Tk</th><th>Cont.</th><th>Dif.</th></tr>

        </thead>

        <tbody>

            @foreach($porCajero as $fila)

            <tr>

                <td>{{ $fila['cajero']->nombre }}</td>

                <td>{{ $fila['cantidad_cierres'] }}</td>

                <td>{{ $fila['cantidad_ventas'] }}</td>

                <td>{{ number_format($fila['monto_contado'], 2) }}</td>

                <td>{{ number_format($fila['diferencia'], 2) }}</td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <div class="fila"><span>Total contado (X):</span><span>S/ {{ number_format($cierre->monto_efectivo_contado, 2) }}</span></div>

    <div class="fila grande"><span>Diferencia total:</span><span>S/ {{ number_format($cierre->diferencia_efectivo, 2) }}</span></div>

    @else

    <hr>

    <div class="fila grande"><span>Efectivo contado:</span><span>S/ {{ number_format($cierre->monto_efectivo_contado, 2) }}</span></div>

    <div class="fila"><span>Diferencia:</span><span>S/ {{ number_format($cierre->diferencia_efectivo, 2) }}</span></div>

    @endif



    @if($cierre->observaciones)

    <hr>

    <div>Obs: {{ $cierre->observaciones }}</div>

    @endif

    <hr>

    <div class="centro" style="font-size:11px">Cierre #{{ $cierre->id }} · {{ $cierre->creado_en }}</div>



    <p class="no-print centro" style="margin-top:1.5rem">

        <button onclick="window.print()">Imprimir</button>

        <a href="{{ route('cierres.index') }}" style="margin-left:.5rem">Volver</a>

    </p>

</body>

</html>

