@extends('seccionk.admin.layout')

@section('title', 'Detalle del evento - Admin Sección K')
@section('page-title', $evento->nombre)

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <p class="text-gray-600">
            <i class="fas fa-calendar mr-2"></i>{{ $evento->fecha->format('d/m/Y') }}
            · Sugerido: <span class="font-semibold">S/ {{ number_format($evento->monto, 2) }}</span>
        </p>
        <p class="text-sm text-gray-500 mt-1">Consulta de aportes. No modifica la información registrada.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('seccionk.admin.declaracion') }}" class="text-amber-700 hover:text-amber-900 font-semibold">
            <i class="fas fa-balance-scale mr-2"></i>Declaración de fondos
        </a>
        <a href="{{ route('seccionk.admin.eventos.participantes', $evento) }}" class="text-purple-600 hover:text-purple-800 font-semibold">
            <i class="fas fa-edit mr-2"></i>Editar participantes
        </a>
        <a href="{{ route('seccionk.admin.dashboard') }}" class="text-gray-600 hover:text-gray-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Volver al dashboard
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Participantes</p>
        <p class="text-3xl font-bold text-blue-600">{{ $participantes }}/{{ $total_promociones }}</p>
        <p class="text-xs text-gray-500 mt-1">Del total de promociones activas</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Recaudado</p>
        <p class="text-3xl font-bold text-green-600">S/ {{ number_format($recaudado, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $aportantes }} promociones aportaron</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Utilizado</p>
        <p class="text-3xl font-bold text-amber-600">S/ {{ number_format($utilizado, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $cerrado ? 'Cerrado' : 'Aún abierto' }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500 font-semibold">Saldo</p>
        <p class="text-3xl font-bold {{ $saldo >= 0 ? 'text-blue-600' : 'text-red-600' }}">S/ {{ number_format($saldo, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Recaudado menos utilizado</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow p-5 mb-6">
    <h3 class="font-bold text-gray-900 mb-2">
        <i class="fas fa-lock text-amber-600 mr-2"></i>
        {{ $cerrado ? 'Evento cerrado' : 'Cerrar evento y declarar el dinero usado' }}
    </h3>
    <p class="text-sm text-gray-600 mb-4">
        Esto solo guarda el gasto en la tabla de declaraciones. No cambia los aportes ya registrados.
    </p>
    @include('seccionk.admin.partials.cerrar-evento', ['evento' => $evento, 'recaudado' => $recaudado, 'volver' => 'detalle'])
</div>

<div class="space-y-3 lg:hidden mb-4">
    @forelse($filas as $fila)
        <div class="bg-white rounded-xl shadow p-4 {{ $fila['pago'] ? 'border-l-4 border-green-500' : '' }}">
            <p class="font-semibold break-words">{{ $fila['promocion']->nombreCompleto() }}</p>
            <p class="text-xs text-gray-500">DNI {{ $fila['promocion']->dni }}</p>
            <div class="flex flex-wrap gap-2 mt-2 text-sm">
                <span>{{ $fila['participo'] ? 'Participó' : 'No participó' }}</span>
                <span>·</span>
                <span>{{ $fila['pago'] ? 'Pagó S/ '.number_format($fila['monto'], 2) : 'Pendiente' }}</span>
            </div>
            @if($fila['fecha_pago'] || $fila['transaccion'] || $fila['observaciones'])
                <p class="text-xs text-gray-500 mt-1">
                    {{ $fila['fecha_pago']?->format('d/m/Y') }}
                    {{ $fila['transaccion'] ? ' · '.$fila['transaccion'] : '' }}
                    {{ $fila['observaciones'] ? ' · '.$fila['observaciones'] : '' }}
                </p>
            @endif
        </div>
    @empty
        <p class="text-center text-gray-500 py-8">No hay promociones activas</p>
    @endforelse
    <div class="bg-gray-50 rounded-xl p-4 font-bold flex justify-between">
        <span>Total del evento</span>
        <span class="text-green-700">S/ {{ number_format($recaudado, 2) }}</span>
    </div>
</div>

<div class="hidden lg:block bg-white rounded-xl shadow-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Promoción</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-700">Participó</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-700">Pagó</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700">Aporte S/</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">N° transacción</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Observaciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($filas as $fila)
                <tr class="{{ $fila['pago'] ? 'bg-green-50/60' : ($fila['participo'] ? 'bg-blue-50/40' : '') }}">
                    <td class="px-4 py-3">
                        <div class="font-semibold">{{ $fila['promocion']->nombreCompleto() }}</div>
                        <div class="text-xs text-gray-500">DNI {{ $fila['promocion']->dni }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($fila['participo'])
                            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Sí</span>
                        @else
                            <span class="text-gray-400">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($fila['pago'])
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Pagó</span>
                        @else
                            <span class="text-gray-400">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold">
                        {{ $fila['pago'] || $fila['monto'] > 0 ? 'S/ '.number_format($fila['monto'], 2) : '—' }}
                    </td>
                    <td class="px-4 py-3">{{ $fila['fecha_pago']?->format('d/m/Y') ?: '—' }}</td>
                    <td class="px-4 py-3">{{ $fila['transaccion'] ?: '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $fila['observaciones'] ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">No hay promociones activas</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50">
            <tr class="font-bold">
                <td class="px-4 py-4" colspan="3">Total del evento · {{ $aportantes }} aportes · {{ $participantes }} participantes</td>
                <td class="px-4 py-4 text-right text-green-700">S/ {{ number_format($recaudado, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
