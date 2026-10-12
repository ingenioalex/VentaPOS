@extends('seccionk.admin.layout')

@section('title', 'Dashboard - Admin Sección K')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
        <p class="text-gray-500 text-sm font-semibold mb-1">Promociones / Eventos</p>
        <p class="text-3xl font-bold text-blue-600">{{ $totalPromociones }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $totalEventos }} eventos activos</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Recaudación por mes</h3>
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">Eventos recientes</h3>
            <div class="space-y-3">
                @forelse($eventosRecientes as $evento)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $evento->nombre }}</p>
                            <p class="text-sm text-gray-600">{{ $evento->fecha->format('d/m/Y') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Evento</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No hay eventos registrados</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
