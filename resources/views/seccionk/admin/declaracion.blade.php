@extends('seccionk.admin.layout')

@section('title', 'Declaración de fondos - Admin Sección K')
@section('page-title', 'Declaración de fondos')

@section('content')
<p class="text-sm text-gray-600 mb-6">
    Recaudado es la suma de aportes ya registrados. Utilizado es el gasto declarado al cerrar una cuota o un evento.
    El saldo es recaudado menos utilizado. Cerrar no cambia los pagos de las promociones.
</p>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Recaudado total</p>
        <p class="text-3xl font-bold text-green-600">S/ {{ number_format($total['recaudado'], 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Cuotas S/ {{ number_format($cuotas['recaudado'], 2) }} · Eventos S/ {{ number_format($eventos['recaudado'], 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Utilizado total</p>
        <p class="text-3xl font-bold text-amber-600">S/ {{ number_format($total['utilizado'], 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Cuotas S/ {{ number_format($cuotas['utilizado'], 2) }} · Eventos S/ {{ number_format($eventos['utilizado'], 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Saldo (recaudado − usado)</p>
        <p class="text-3xl font-bold {{ $total['saldo'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">S/ {{ number_format($total['saldo'], 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Cuotas S/ {{ number_format($cuotas['saldo'], 2) }} · Eventos S/ {{ number_format($eventos['saldo'], 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Cerrados / abiertos</p>
        <p class="text-3xl font-bold text-purple-600">{{ $total['cerrados'] }}/{{ $total['cerrados'] + $total['abiertos'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $total['abiertos'] }} aún sin declarar uso</p>
    </div>
</div>

@foreach([
    ['titulo' => 'Cuotas pagadas', 'filas' => $filas_cuotas, 'icono' => 'fa-calendar-check', 'totales' => $cuotas],
    ['titulo' => 'Eventos', 'filas' => $filas_eventos, 'icono' => 'fa-star', 'totales' => $eventos],
] as $bloque)
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h2 class="text-lg font-bold text-gray-900">
                <i class="fas {{ $bloque['icono'] }} text-purple-600 mr-2"></i>{{ $bloque['titulo'] }}
            </h2>
            <p class="text-sm text-gray-600">
                Recaudado S/ {{ number_format($bloque['totales']['recaudado'], 2) }}
                · Usado S/ {{ number_format($bloque['totales']['utilizado'], 2) }}
                · Saldo S/ {{ number_format($bloque['totales']['saldo'], 2) }}
            </p>
        </div>

        <div class="space-y-4 lg:hidden">
            @forelse($bloque['filas'] as $fila)
                <div class="border rounded-xl p-4">
                    <div class="flex justify-between gap-2">
                        <p class="font-semibold">{{ $fila['evento']->mes_cuota ?: $fila['evento']->nombre }}</p>
                        @if($fila['cerrado'])
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-800">Cerrado</span>
                        @else
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-800">Abierto</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $fila['aportantes'] }} aportes · Recaudado S/ {{ number_format($fila['recaudado'], 2) }}
                        · Usado S/ {{ number_format($fila['utilizado'], 2) }}
                        · Saldo S/ {{ number_format($fila['saldo'], 2) }}
                    </p>
                    @include('seccionk.admin.partials.cerrar-evento', [
                        'evento' => $fila['evento'],
                        'recaudado' => $fila['recaudado'],
                        'volver' => 'declaracion',
                    ])
                </div>
            @empty
                <p class="text-center text-gray-500 py-6">No hay registros</p>
            @endforelse
        </div>

        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pb-2 pr-4">Nombre</th>
                        <th class="pb-2 pr-4">Aportes</th>
                        <th class="pb-2 pr-4 text-right">Recaudado</th>
                        <th class="pb-2 pr-4 text-right">Utilizado</th>
                        <th class="pb-2 pr-4 text-right">Saldo</th>
                        <th class="pb-2 pr-4">Estado</th>
                        <th class="pb-2">Declarar uso</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($bloque['filas'] as $fila)
                        <tr>
                            <td class="py-3 pr-4 font-semibold">
                                {{ $fila['evento']->mes_cuota ?: $fila['evento']->nombre }}
                                <div class="text-xs text-gray-500">{{ $fila['evento']->fecha->format('d/m/Y') }}</div>
                            </td>
                            <td class="py-3 pr-4">{{ $fila['aportantes'] }}</td>
                            <td class="py-3 pr-4 text-right text-green-700 font-semibold">S/ {{ number_format($fila['recaudado'], 2) }}</td>
                            <td class="py-3 pr-4 text-right text-amber-700 font-semibold">S/ {{ number_format($fila['utilizado'], 2) }}</td>
                            <td class="py-3 pr-4 text-right font-semibold {{ $fila['saldo'] >= 0 ? 'text-blue-700' : 'text-red-700' }}">S/ {{ number_format($fila['saldo'], 2) }}</td>
                            <td class="py-3 pr-4">
                                @if($fila['cerrado'])
                                    <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">Cerrado</span>
                                    @if($fila['fecha_cierre'])
                                        <div class="text-xs text-gray-500 mt-1">{{ $fila['fecha_cierre']->format('d/m/Y') }}</div>
                                    @endif
                                @else
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Abierto</span>
                                @endif
                            </td>
                            <td class="py-3 min-w-[16rem]">
                                @include('seccionk.admin.partials.cerrar-evento', [
                                    'evento' => $fila['evento'],
                                    'recaudado' => $fila['recaudado'],
                                    'volver' => 'declaracion',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">No hay registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
