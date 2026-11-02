@extends('seccionk.admin.layout')

@section('title', 'Resumen de cuotas - Admin Sección K')
@section('page-title', 'Resumen de cuotas por mes y promoción')

@section('content')
<form method="GET" action="{{ route('seccionk.admin.resumen.cuotas.exportar') }}" onsubmit="return validarMeses()" class="space-y-6">
    <div class="bg-white rounded-xl shadow-lg p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
            <div>
                <p class="font-semibold text-gray-900">Meses a exportar</p>
                <p class="text-sm text-gray-500">Marca solo las cuotas de mes que irán en Excel o WhatsApp. Así el texto no sale largo.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span id="meses-elegidos" class="px-2 py-1 rounded-full bg-purple-100 text-purple-800 font-semibold"></span>
                <button type="button" onclick="marcarMeses(true)" class="text-purple-600 font-semibold">Marcar todos</button>
                <button type="button" onclick="marcarMeses(false)" class="text-gray-600 font-semibold">Quitar todos</button>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($cuotas as $cuota)
                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="meses[]" value="{{ $cuota->id }}" class="check-mes" checked>
                    <span class="text-sm font-semibold tracking-wide">{{ $cuota->mesCorto() }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-4">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="orden" value="mes" checked>
                <span>Ordenar por mes</span>
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="orden" value="promocion">
                <span>Ordenar por promoción</span>
            </label>
            <button type="submit" name="formato" value="excel" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                <i class="fas fa-file-excel mr-2"></i>Excel
            </button>
            <button type="submit" name="formato" value="whatsapp" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fab fa-whatsapp mr-2"></i>WhatsApp
            </button>
            <a href="{{ route('seccionk.admin.dashboard') }}" class="text-purple-600 font-semibold">Volver al dashboard</a>
        </div>
    </div>
</form>

<p class="lg:hidden text-xs text-gray-500 -mt-2">Desliza la tabla hacia los lados para ver cada mes.</p>
<div class="bg-white rounded-xl shadow-lg overflow-x-auto sk-scroll">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-700 sticky left-0 bg-gray-50">Promoción</th>
                @foreach($cuotas as $cuota)
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">
                        {{ $cuota->mesCorto() }}
                        <div class="text-xs font-normal text-gray-500">S/ {{ number_format($cuota->monto, 2) }}</div>
                    </th>
                @endforeach
                <th class="px-4 py-3 text-center font-semibold text-gray-700">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($filas as $fila)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 sticky left-0 bg-white">
                        <a href="{{ route('seccionk.admin.promociones.show', $fila['promocion']) }}" class="font-semibold text-purple-700 hover:text-purple-900">
                            {{ $fila['promocion']->nombreCompleto() }}
                        </a>
                        <div class="text-xs text-gray-500">{{ $fila['promocion']->dni }}</div>
                    </td>
                    @foreach($cuotas as $cuota)
                        @php $celda = $fila['celdas'][$cuota->id]; @endphp
                        <td class="px-4 py-3 text-center">
                            @if($celda['pago'])
                                <span class="inline-block px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Pagó</span>
                                <div class="text-xs text-gray-600 mt-1">S/ {{ number_format($celda['monto'], 2) }}</div>
                            @else
                                <span class="inline-block px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">Pendiente</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-4 py-3 text-center font-semibold">
                        {{ $fila['pagadas'] }}/{{ $cuotas->count() }}
                        <div class="text-xs text-green-700">S/ {{ number_format($fila['recaudado'], 2) }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $cuotas->count() + 2 }}" class="px-4 py-10 text-center text-gray-500">No hay promociones</td>
                </tr>
            @endforelse
        </tbody>
        @if($cuotas->isNotEmpty())
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td class="px-4 py-3 sticky left-0 bg-gray-50">Total del mes</td>
                    @foreach($cuotas as $cuota)
                        <td class="px-4 py-3 text-center">
                            {{ $totalesMes[$cuota->id]['pagaron'] }} pagaron
                            <div class="text-xs text-green-700">S/ {{ number_format($totalesMes[$cuota->id]['recaudado'], 2) }}</div>
                        </td>
                    @endforeach
                    <td class="px-4 py-3 text-center text-green-700">
                        S/ {{ number_format($filas->sum('recaudado'), 2) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
@endsection

@section('scripts')
<script>
function cajasMes() {
    return document.querySelectorAll('.check-mes');
}

function contarMeses() {
    const total = cajasMes().length;
    const elegidos = Array.from(cajasMes()).filter(function (caja) { return caja.checked; }).length;
    const etiqueta = document.getElementById('meses-elegidos');
    if (etiqueta) {
        etiqueta.textContent = elegidos + ' de ' + total + ' meses';
    }
}

function marcarMeses(marcar) {
    cajasMes().forEach(function (caja) {
        caja.checked = marcar;
    });
    contarMeses();
}

function validarMeses() {
    const elegidos = Array.from(cajasMes()).filter(function (caja) { return caja.checked; }).length;
    if (elegidos === 0) {
        alert('Selecciona al menos un mes para exportar.');
        return false;
    }
    return true;
}

cajasMes().forEach(function (caja) {
    caja.addEventListener('change', contarMeses);
});
contarMeses();
</script>
@endsection
