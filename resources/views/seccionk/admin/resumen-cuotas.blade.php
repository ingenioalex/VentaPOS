@extends('seccionk.admin.layout')

@section('title', 'Resumen de cuotas - Admin Sección K')
@section('page-title', 'Resumen de cuotas por mes y promoción')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <p class="text-gray-600">Cada celda muestra si el miembro pagó esa cuota y el monto.</p>
    <a href="{{ route('seccionk.admin.dashboard') }}" class="text-purple-600 font-semibold">Volver al dashboard</a>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-700 sticky left-0 bg-gray-50">Promoción</th>
                @foreach($cuotas as $cuota)
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">
                        {{ $cuota->mes_cuota }}
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
