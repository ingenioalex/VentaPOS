@extends('seccionk.admin.layout')

@section('title', 'Dashboard - Admin Sección K')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <p class="text-gray-500 text-sm font-semibold mb-1">Quiénes pagaron cuotas</p>
        <p class="text-3xl font-bold text-purple-600">{{ $promocionesQuePagaron }}/{{ $participanCuotas }}</p>
        <p class="text-xs text-gray-500 mt-1">Miembros que ya registraron al menos un pago</p>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <p class="text-gray-500 text-sm font-semibold mb-1">Pagos de cuotas</p>
        <p class="text-3xl font-bold text-indigo-600">{{ $cuotasPagadasCount }}/{{ $esperadosCuotas }}</p>
        <p class="text-xs text-gray-500 mt-1">Cuotas cobradas de {{ $totalCuotas }} meses × {{ $participanCuotas }} miembros</p>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <p class="text-gray-500 text-sm font-semibold mb-1">Monto recaudado (cuotas)</p>
        <p class="text-3xl font-bold text-green-600">S/ {{ number_format($montoRecaudadoCuotas, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Suma de aportes mensuales pagados</p>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <p class="text-gray-500 text-sm font-semibold mb-1">Monto recaudado (eventos)</p>
        <p class="text-3xl font-bold text-blue-600">S/ {{ number_format($montoRecaudadoEventos, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $aportantesEventos }}/{{ $totalPromociones }} promociones aportaron · {{ $totalEventos }} eventos</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Recaudación por mes</h3>
            <a href="{{ route('seccionk.admin.resumen.cuotas') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-800">
                Ver detalle por promoción
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pb-2 pr-4">Mes</th>
                        <th class="pb-2 pr-4">Pagaron</th>
                        <th class="pb-2 pr-4">Faltan</th>
                        <th class="pb-2 text-right">Recaudado</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($resumenMeses as $mes)
                        <tr>
                            <td class="py-3 pr-4 font-semibold">{{ $mes['evento']->mes_cuota ?: $mes['evento']->nombre }}</td>
                            <td class="py-3 pr-4 text-green-700">{{ $mes['pagaron'] }}</td>
                            <td class="py-3 pr-4 text-amber-600">{{ $mes['faltan'] }}</td>
                            <td class="py-3 text-right font-semibold">S/ {{ number_format($mes['recaudado'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500">No hay cuotas mensuales</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-6">
        <a href="{{ route('seccionk.admin.resumen.cuotas') }}" class="block p-5 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg text-white hover:shadow-xl transition">
            <p class="text-sm uppercase tracking-wide text-purple-100">Acceso directo</p>
            <p class="text-xl font-bold mt-1">Resumen de cuotas</p>
            <p class="text-sm text-purple-100 mt-2">Detalle de pago por mes y por promoción</p>
        </a>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <p class="text-gray-500 text-sm font-semibold">Participantes en eventos</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $participantesEventos }}</p>
            <p class="text-xs text-gray-500 mt-2">Suma de participaciones en {{ $totalEventos }} eventos · {{ $totalPromociones }} promociones activas</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Eventos · monto y participantes</h3>
        <a href="{{ route('seccionk.admin.eventos.index', ['tipo' => 'evento']) }}" class="text-sm font-semibold text-purple-600 hover:text-purple-800">
            Ver todos los eventos
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="pb-2 pr-4">Evento</th>
                    <th class="pb-2 pr-4">Fecha</th>
                    <th class="pb-2 pr-4">Participantes</th>
                    <th class="pb-2 pr-4 text-right">Recaudado</th>
                    <th class="pb-2 text-right">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($resumenEventos as $fila)
                    <tr>
                        <td class="py-3 pr-4 font-semibold">{{ $fila['evento']->nombre }}</td>
                        <td class="py-3 pr-4 text-gray-600">{{ $fila['evento']->fecha->format('d/m/Y') }}</td>
                        <td class="py-3 pr-4">
                            <span class="font-semibold text-blue-700">{{ $fila['participantes'] }}/{{ $fila['total_promociones'] }}</span>
                            <span class="text-xs text-gray-500 block">{{ $fila['aportantes'] }} aportaron</span>
                        </td>
                        <td class="py-3 pr-4 text-right font-semibold text-green-700">S/ {{ number_format($fila['recaudado'], 2) }}</td>
                        <td class="py-3 text-right">
                            <a href="{{ route('seccionk.admin.eventos.detalle', $fila['evento']) }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                                Ver detalle
                                <i class="fas fa-chevron-right ml-2 text-xs"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500">No hay eventos registrados</td>
                    </tr>
                @endforelse
            </tbody>
            @if($resumenEventos->isNotEmpty())
                <tfoot>
                    <tr class="font-bold">
                        <td class="pt-4" colspan="3">Total eventos</td>
                        <td class="pt-4 text-right text-green-700">S/ {{ number_format($montoRecaudadoEventos, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
