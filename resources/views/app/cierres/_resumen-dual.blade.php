@php
    $tickets = (int) ($tickets ?? 0);
    $efectivo = (float) ($efectivo ?? 0);
    $tarjeta = (float) ($tarjeta ?? 0);
    $otros = (float) ($otros ?? 0);
    $totalVentas = round($efectivo + $tarjeta + $otros, 2);
    $efectivoSistema = (float) ($efectivoSistema ?? $efectivo);
    $efectivoContado = isset($efectivoContado) ? (float) $efectivoContado : null;
    if (! isset($diferencia) && $efectivoContado !== null) {
        $diferencia = round($efectivoContado - $efectivoSistema, 2);
    } elseif (isset($diferencia)) {
        $diferencia = (float) $diferencia;
    } else {
        $diferencia = null;
    }
    $tieneDif = $diferencia !== null && abs($diferencia) > 0.001;
    $compacto = (bool) ($compacto ?? false);
    $periodoDesde = $periodoDesde ?? null;
    $periodoHasta = $periodoHasta ?? null;
@endphp
<div class="resumen-dual {{ $compacto ? 'resumen-dual--compacto' : '' }}">
    <div class="resumen-dual__ventas">
        @if(!empty($titulo))
        <h4 class="resumen-dual__titulo">{{ $titulo }}</h4>
        @endif
        @if(!empty($subtitulo))
        <p class="resumen-dual__sub">{{ $subtitulo }}</p>
        @elseif($periodoDesde && $periodoHasta)
        <p class="resumen-dual__sub">Del {{ $periodoDesde }} al {{ $periodoHasta }}</p>
        @endif
        <p class="resumen-dual__meta"><strong>{{ $tickets }}</strong> tickets · solo ventas registradas</p>
        <table class="resumen-medios">
            <tbody>
                <tr>
                    <td><span class="medio-dot medio-dot--ef"></span> Ventas en efectivo</td>
                    <td class="col-money">S/ {{ number_format($efectivo, 2) }}</td>
                </tr>
                <tr>
                    <td><span class="medio-dot medio-dot--tar"></span> Ventas con tarjeta</td>
                    <td class="col-money">S/ {{ number_format($tarjeta, 2) }}</td>
                </tr>
                <tr>
                    <td><span class="medio-dot medio-dot--otr"></span> Yape / Plin / otros</td>
                    <td class="col-money">S/ {{ number_format($otros, 2) }}</td>
                </tr>
                <tr class="resumen-medios__total">
                    <td>Total ventas</td>
                    <td class="col-money">S/ {{ number_format($totalVentas, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="resumen-dual__arqueo">
        <h4 class="resumen-dual__titulo">Arqueo de efectivo</h4>
        <p class="resumen-dual__sub">Dinero físico en caja — no incluye tarjeta ni Yape/Plin</p>
        <table class="resumen-arqueo">
            <tbody>
                <tr>
                    <td>Efectivo según ventas</td>
                    <td class="col-money">S/ {{ number_format($efectivoSistema, 2) }}</td>
                </tr>
                <tr>
                    <td>Efectivo contado{{ $efectivoContado === null ? ' (pendiente)' : '' }}</td>
                    <td class="col-money" @if(!empty($idArqueoContado)) id="{{ $idArqueoContado }}" @endif>
                        @if($efectivoContado !== null)
                        S/ {{ number_format($efectivoContado, 2) }}
                        @else
                        <span style="color:#94a3b8">—</span>
                        @endif
                    </td>
                </tr>
                <tr class="resumen-arqueo__dif {{ $tieneDif ? 'resumen-arqueo__dif--alerta' : '' }}">
                    <td>Diferencia (contado − sistema)</td>
                    <td class="col-money" @if(!empty($idArqueoDif)) id="{{ $idArqueoDif }}" @endif>
                        @if($diferencia !== null)
                        S/ {{ number_format($diferencia, 2) }}
                        @else
                        <span style="color:#94a3b8">—</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
