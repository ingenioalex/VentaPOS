@extends('seccionk.admin.layout')

@section('title', 'Dashboard - Admin Sección K')
@section('page-title', 'Dashboard')

@section('content')
<form method="GET" action="{{ route('seccionk.admin.dashboard.exportar') }}" class="bg-white rounded-xl shadow-lg p-4 sm:p-5 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="font-semibold text-gray-900">Exportar dashboard</p>
            <p class="text-sm text-gray-500">Cuotas por mes y eventos con recaudado menos lo declarado, ya ordenados.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="orden" value="fecha" checked>
                <span>Ordenar por fecha</span>
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="orden" value="nombre">
                <span>Ordenar por nombre</span>
            </label>
            <button type="submit" name="formato" value="excel" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 font-semibold">
                <i class="fas fa-file-excel mr-2"></i>Excel
            </button>
            <button type="submit" name="formato" value="whatsapp" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-semibold">
                <i class="fab fa-whatsapp mr-2"></i>WhatsApp
            </button>
        </div>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
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
        <p class="text-gray-500 text-sm font-semibold mb-1">Eventos · recaudado − declarado</p>
        <p class="text-3xl font-bold {{ $montoSaldoEventos >= 0 ? 'text-blue-600' : 'text-red-600' }}">S/ {{ number_format($montoSaldoEventos, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">
            Recaudado S/ {{ number_format($montoRecaudadoEventos, 2) }}
            − declarado S/ {{ number_format($montoUtilizadoEventos, 2) }}
            · {{ $aportantesEventos }}/{{ $totalPromociones }} aportaron
        </p>
    </div>
</div>

@if($prestamosPendientes->isNotEmpty())
<div class="bg-rose-50 border border-rose-200 rounded-xl shadow-lg p-4 sm:p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-rose-800">Préstamos pendientes</p>
            <p class="text-3xl font-bold text-rose-700 mt-1">S/ {{ number_format($totalPrestamosPendientes, 2) }}</p>
            <p class="text-xs text-rose-600 mt-1">Se restan del fondo total hasta que los devuelvan. Cuando paguen, dejan de verse aquí.</p>
            <ul class="mt-3 space-y-1 text-sm text-rose-800">
                @foreach($prestamosPendientes as $prestamo)
                    <li>
                        {{ $prestamo->persona ?: 'Sin nombre' }}
                        · {{ $prestamo->fecha->format('d/m/Y') }}
                        · Debe S/ {{ number_format($prestamo->saldoPendiente(), 2) }}
                        @if($prestamo->comentario)
                            · {{ $prestamo->comentario }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="lg:text-right">
            <p class="text-sm font-semibold text-gray-700">Fondo total disponible</p>
            <p class="text-3xl font-bold {{ $fondoDisponible >= 0 ? 'text-blue-700' : 'text-red-700' }}">S/ {{ number_format($fondoDisponible, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">
                Cuotas S/ {{ number_format($montoRecaudadoCuotas, 2) }}
                + eventos S/ {{ number_format($montoSaldoEventos, 2) }}
                − préstamos S/ {{ number_format($totalPrestamosPendientes, 2) }}
            </p>
            <a href="{{ route('seccionk.admin.fondo') }}" class="inline-block mt-3 text-sm font-semibold text-teal-700 hover:text-teal-900">
                Registrar devolución
            </a>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-4 sm:p-6">
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
        <a href="{{ route('seccionk.admin.declaracion') }}" class="block p-5 bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl shadow-lg text-white hover:shadow-xl transition">
            <p class="text-sm uppercase tracking-wide text-amber-100">Acceso directo</p>
            <p class="text-xl font-bold mt-1">Declaración de fondos</p>
            <p class="text-sm text-amber-100 mt-2">Recaudado vs utilizado en cuotas y eventos</p>
        </a>
        <a href="{{ route('seccionk.admin.fondo') }}" class="block p-5 bg-gradient-to-r from-teal-600 to-cyan-600 rounded-xl shadow-lg text-white hover:shadow-xl transition">
            <p class="text-sm uppercase tracking-wide text-teal-100">Acceso directo</p>
            <p class="text-xl font-bold mt-1">Ingresos / egresos</p>
            <p class="text-sm text-teal-100 mt-2">Préstamos y devoluciones del fondo</p>
        </a>
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <p class="text-gray-500 text-sm font-semibold">Participantes en eventos</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $participantesEventos }}</p>
            <p class="text-xs text-gray-500 mt-2">Suma de participaciones en {{ $totalEventos }} eventos · {{ $totalPromociones }} promociones activas</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Eventos · recaudado − declaración</h3>
        <a href="{{ route('seccionk.admin.eventos.index', ['tipo' => 'evento']) }}" class="text-sm font-semibold text-purple-600 hover:text-purple-800">
            Ver todos los eventos
        </a>
    </div>
    <div class="space-y-3 md:hidden">
        @forelse($resumenEventos as $fila)
            <a href="{{ route('seccionk.admin.eventos.detalle', $fila['evento']) }}" class="block border rounded-lg p-3">
                <p class="font-semibold text-gray-900">{{ $fila['evento']->nombre }}</p>
                <p class="text-xs text-gray-500">{{ $fila['evento']->fecha->format('d/m/Y') }} · {{ $fila['participantes'] }}/{{ $fila['total_promociones'] }} participaron</p>
                <p class="text-sm mt-1">
                    Recaudado <span class="font-semibold text-green-700">S/ {{ number_format($fila['recaudado'], 2) }}</span>
                    · Declarado <span class="font-semibold text-amber-700">S/ {{ number_format($fila['utilizado'], 2) }}</span>
                    · Saldo <span class="font-semibold {{ $fila['saldo'] >= 0 ? 'text-blue-700' : 'text-red-700' }}">S/ {{ number_format($fila['saldo'], 2) }}</span>
                </p>
            </a>
        @empty
            <p class="text-gray-500 text-center py-4">No hay eventos registrados</p>
        @endforelse
    </div>
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="pb-2 pr-4">Evento</th>
                    <th class="pb-2 pr-4">Fecha</th>
                    <th class="pb-2 pr-4">Participantes</th>
                    <th class="pb-2 pr-4 text-right">Recaudado</th>
                    <th class="pb-2 pr-4 text-right">Declarado</th>
                    <th class="pb-2 pr-4 text-right">Saldo</th>
                    <th class="pb-2 text-right">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($resumenEventos as $fila)
                    <tr>
                        <td class="py-3 pr-4 font-semibold">
                            {{ $fila['evento']->nombre }}
                            @if($fila['cerrado'])
                                <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Cerrado</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4 text-gray-600">{{ $fila['evento']->fecha->format('d/m/Y') }}</td>
                        <td class="py-3 pr-4">
                            <span class="font-semibold text-blue-700">{{ $fila['participantes'] }}/{{ $fila['total_promociones'] }}</span>
                            <span class="text-xs text-gray-500 block">{{ $fila['aportantes'] }} aportaron</span>
                        </td>
                        <td class="py-3 pr-4 text-right font-semibold text-green-700">S/ {{ number_format($fila['recaudado'], 2) }}</td>
                        <td class="py-3 pr-4 text-right font-semibold text-amber-700">S/ {{ number_format($fila['utilizado'], 2) }}</td>
                        <td class="py-3 pr-4 text-right font-semibold {{ $fila['saldo'] >= 0 ? 'text-blue-700' : 'text-red-700' }}">S/ {{ number_format($fila['saldo'], 2) }}</td>
                        <td class="py-3 text-right">
                            <a href="{{ route('seccionk.admin.eventos.detalle', $fila['evento']) }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                                Ver detalle
                                <i class="fas fa-chevron-right ml-2 text-xs"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-500">No hay eventos registrados</td>
                    </tr>
                @endforelse
            </tbody>
            @if($resumenEventos->isNotEmpty())
                <tfoot>
                    <tr class="font-bold">
                        <td class="pt-4" colspan="3">Total eventos</td>
                        <td class="pt-4 text-right text-green-700">S/ {{ number_format($montoRecaudadoEventos, 2) }}</td>
                        <td class="pt-4 text-right text-amber-700">S/ {{ number_format($montoUtilizadoEventos, 2) }}</td>
                        <td class="pt-4 text-right {{ $montoSaldoEventos >= 0 ? 'text-blue-700' : 'text-red-700' }}">S/ {{ number_format($montoSaldoEventos, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
