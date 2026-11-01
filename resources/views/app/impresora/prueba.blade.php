<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de prueba — VentaPOS</title>
    @include('partials.ticket-estilos', ['estilos' => $estilos, 'impresora' => $impresora])
</head>
<body>
    <div class="centro">
        @if($impresora['mostrar_logo'] && $usuario->empresa?->logoUrl())
        <img src="{{ $usuario->empresa->logoUrl() }}" alt="Logo" class="ticket-logo"><br>
        @endif
        <strong>{{ $usuario->empresa?->nombre ?? 'Mi negocio' }}</strong><br>
        @if($usuario->empresa?->ruc)
        RUC: {{ $usuario->empresa->ruc }}<br>
        @endif
        <span class="grande">TICKET DE PRUEBA</span><br>
        {{ now()->format('d/m/Y H:i') }}
    </div>
    <hr>
    <div class="fila"><span>Usuario:</span><span>{{ $usuario->nombre }}</span></div>
    <div class="fila"><span>Ancho:</span><span>{{ $impresora['ancho_mm'] }} mm</span></div>
    <div class="fila"><span>Fuente:</span><span>{{ $impresora['tamano_fuente'] }}</span></div>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="num">Cant</th>
                <th class="num">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Producto demo 500ml</td>
                <td class="num">2</td>
                <td class="num">9.60</td>
            </tr>
            <tr>
                <td>Galletas surtidas</td>
                <td class="num">1</td>
                <td class="num">3.50</td>
            </tr>
        </tbody>
    </table>
    <hr>
    <div class="fila grande"><span>TOTAL:</span><span>S/ 13.10</span></div>
    @if($impresora['notas_pie'])
    <hr>
    <div class="centro notas-pie">{{ $impresora['notas_pie'] }}</div>
    @endif
    <div class="centro pie-legal">VentasÁgil — ticket de prueba</div>

    <p class="no-print centro" style="margin-top:1rem">
        <button type="button" onclick="window.print()">Imprimir</button>
        <button type="button" onclick="window.close()">Cerrar</button>
    </p>

    @if($autoImprimir)
    @include('partials.ticket-auto-print', ['copias' => (int) ($impresora['copias'] ?? 1)])
    @endif
</body>
</html>
