@php
    $tz = config('app.timezone', 'America/Lima');
    $editable = (bool) ($editable ?? false);
    $efSistTot = (float) ($totalesX['total_efectivo'] ?? 0);
    $efContTot = (float) ($totalContado ?? 0);
    $difTot = round($efContTot - $efSistTot, 2);
    $inputPrefix = $inputPrefix ?? 'rectificar';
@endphp
@if($editable)
<p style="font-size:.78rem;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:.45rem;padding:.55rem .65rem;margin:-.25rem 0 .65rem">
    Si hubo error al digitar el efectivo contado en un Cierre X, corríjalo en <strong>Ef. contado</strong> antes del Cierre Z.
    Quedará registrado en observaciones del X.
</p>
@endif
<div class="tabla-wrap">
<table class="data tabla-cierres-x" style="font-size:.82rem;margin-bottom:1rem"
    @if($editable) data-arqueo-sistema="{{ number_format($efSistTot, 2, '.', '') }}" @endif>
    <thead>
        <tr>
            <th colspan="4"></th>
            <th colspan="4" class="grupo-th col-ventas">Ventas del turno</th>
            <th colspan="3" class="grupo-th col-arqueo">Arqueo (solo efectivo)</th>
        </tr>
        <tr>
            <th>#</th>
            <th>Registro</th>
            <th>Período del turno</th>
            <th>Cajero</th>
            <th class="col-money col-ventas">Tk</th>
            <th class="col-money">Ef. ventas</th>
            <th class="col-money">Tarjeta</th>
            <th class="col-money">Otros</th>
            <th class="col-money col-arqueo">Ef. sistema</th>
            <th class="col-money">Ef. contado</th>
            <th class="col-money">Dif.</th>
        </tr>
    </thead>
    <tbody>
        @php $n = 0; @endphp
        @foreach($cierresX as $cx)
        @php
            $n++;
            $ini = $cx->fecha_inicio->timezone($tz);
            $fin = $cx->fecha_fin->timezone($tz);
            $efSist = (float) $cx->total_efectivo;
            $efCont = (float) ($cx->monto_efectivo_contado ?? 0);
            $difCalc = round($efCont - $efSist, 2);
            $tieneDif = abs($difCalc) > 0.001;
        @endphp
        <tr>
            <td>{{ $n }}</td>
            <td>{{ $fin->format('d/m/Y H:i') }}</td>
            <td class="col-periodo">{{ $ini->format('d/m H:i') }} — {{ $fin->format('d/m H:i') }}</td>
            <td>{{ $cx->usuario->nombre }}</td>
            <td class="col-money col-ventas">{{ $cx->cantidad_ventas }}</td>
            <td class="col-money">S/ {{ number_format($efSist, 2) }}</td>
            <td class="col-money">S/ {{ number_format($cx->total_tarjeta, 2) }}</td>
            <td class="col-money">S/ {{ number_format($cx->total_otros, 2) }}</td>
            <td class="col-money col-arqueo">S/ {{ number_format($efSist, 2) }}</td>
            <td class="col-money">
                @if($editable)
                <input type="number" name="{{ $inputPrefix }}[{{ $cx->id }}]" value="{{ number_format($efCont, 2, '.', '') }}"
                    min="0" step="0.01" inputmode="decimal" required
                    class="input-rect-arqueo" data-rectificar-arqueo data-ef-sistema="{{ number_format($efSist, 2, '.', '') }}"
                    style="width:6.5rem;padding:.3rem .4rem;border:1px solid #cbd5e1;border-radius:.35rem;text-align:right;font-size:.82rem">
                @else
                S/ {{ number_format($efCont, 2) }}
                @endif
            </td>
            <td class="col-money celda-dif-arqueo" data-dif-arqueo style="color:{{ $tieneDif ? '#dc2626' : 'inherit' }}">
                S/ {{ number_format($difCalc, 2) }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight:700;background:#f8fafc">
            <td colspan="4">Total ventas ({{ $cierresX->count() }} X)</td>
            <td class="col-money col-ventas">{{ $totalesX['cantidad_ventas'] }}</td>
            <td class="col-money">S/ {{ number_format($totalesX['total_efectivo'], 2) }}</td>
            <td class="col-money">S/ {{ number_format($totalesX['total_tarjeta'], 2) }}</td>
            <td class="col-money">S/ {{ number_format($totalesX['total_otros'], 2) }}</td>
            <td class="col-money col-arqueo" colspan="3" style="text-align:left;font-weight:500;font-size:.78rem;color:#64748b">
                Total ventas S/ {{ number_format($totalesX['total_ventas'], 2) }}
            </td>
        </tr>
        <tr style="font-weight:700;background:#fffbeb">
            <td colspan="8" style="text-align:right;font-size:.8rem;color:#92400e">Arqueo acumulado de los X</td>
            <td class="col-money col-arqueo" data-arqueo-sistema-tot>S/ {{ number_format($efSistTot, 2) }}</td>
            <td class="col-money" data-arqueo-contado-tot>S/ {{ number_format($efContTot, 2) }}</td>
            <td class="col-money" data-arqueo-dif-tot style="color:{{ abs($difTot) > 0.001 ? '#dc2626' : 'inherit' }}">
                S/ {{ number_format($difTot, 2) }}
            </td>
        </tr>
    </tfoot>
</table>
</div>
