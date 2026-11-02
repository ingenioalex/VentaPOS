@extends('seccionk.admin.layout')

@section('title', 'Promociones - Admin Sección K')
@section('page-title', 'Gestión de Promociones')

@section('content')
<div class="mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
    <form method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <input type="text" name="buscar" placeholder="Buscar por DNI o nombre..." value="{{ request('buscar') }}" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 w-full sm:w-64">
        <select name="participa_cuotas" class="px-4 py-2 border rounded-lg">
            <option value="">Todos</option>
            <option value="1" {{ request('participa_cuotas') === '1' ? 'selected' : '' }}>Participa en cuotas</option>
            <option value="0" {{ request('participa_cuotas') === '0' ? 'selected' : '' }}>No participa</option>
        </select>
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="mostrar_inactivos" value="1" {{ $mostrarInactivos ? 'checked' : '' }} onchange="this.form.submit()">
            <span>Mostrar inactivos</span>
            @if($ocultas > 0)
                <span class="text-xs text-amber-700 font-semibold">({{ $ocultas }} ocultas)</span>
            @endif
        </label>
        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Filtrar</button>
    </form>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('seccionk.admin.promociones.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-center">
            <i class="fas fa-file-excel mr-2"></i>Excel
        </a>
        <a href="{{ route('seccionk.admin.promociones.exportar', array_merge(request()->query(), ['formato' => 'whatsapp'])) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-center">
            <i class="fab fa-whatsapp mr-2"></i>WhatsApp
        </a>
        <a href="{{ route('seccionk.admin.promociones.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-center">
            <i class="fas fa-plus mr-2"></i>Nueva Promoción
        </a>
    </div>
</div>

<div class="space-y-3 lg:hidden">
    @forelse($promociones as $promo)
        @php
            $cuotasPagadas = (int) $promo->cuotas_pagadas;
            $cuotasFaltan = max($totalCuotas - $cuotasPagadas, 0);
            $eventosPagados = (int) $promo->eventos_pagados;
            $eventosFaltan = max($totalEventos - $eventosPagados, 0);
        @endphp
        <div class="bg-white rounded-xl shadow p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 break-words">{{ $promo->nombreCompleto() }}</p>
                    <p class="text-sm text-gray-500">DNI {{ $promo->dni }} @if($promo->celular)· {{ $promo->celular }}@endif</p>
                </div>
                @if($promo->activo)
                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold flex-shrink-0">Activo</span>
                @else
                    <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold flex-shrink-0">Inactivo</span>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-2 mt-3 text-sm">
                <a href="{{ route('seccionk.admin.promociones.show', $promo) }}" class="bg-purple-50 rounded-lg p-2">
                    <p class="text-xs text-gray-500">Cuotas</p>
                    <p class="font-bold text-purple-700">{{ $cuotasPagadas }}/{{ $totalCuotas }}</p>
                </a>
                <a href="{{ route('seccionk.admin.promociones.show', $promo) }}" class="bg-blue-50 rounded-lg p-2">
                    <p class="text-xs text-gray-500">Eventos</p>
                    <p class="font-bold text-blue-700">{{ $eventosPagados }}/{{ $totalEventos }}</p>
                </a>
            </div>
            <div class="flex flex-wrap gap-3 mt-3 text-sm">
                <a href="{{ route('seccionk.admin.promociones.show', $promo) }}" class="text-purple-700 font-semibold">Pagos</a>
                <a href="{{ route('seccionk.admin.promociones.edit', $promo) }}" class="text-blue-700 font-semibold">Editar</a>
                @if($promo->activo)
                    <form action="{{ route('seccionk.admin.promociones.destroy', $promo) }}" method="POST" onsubmit="return confirm('¿Ocultar esta promoción?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-amber-700 font-semibold">Ocultar</button>
                    </form>
                @else
                    <form action="{{ route('seccionk.admin.promociones.restaurar', $promo) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-green-700 font-semibold">Restaurar</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="text-center text-gray-500 py-8">No hay promociones registradas</p>
    @endforelse
</div>

<div class="hidden lg:block bg-white rounded-xl shadow-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">DNI</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nombre Completo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Celular</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Cuotas</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Eventos</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($promociones as $promo)
                @php
                    $cuotasPagadas = (int) $promo->cuotas_pagadas;
                    $cuotasFaltan = max($totalCuotas - $cuotasPagadas, 0);
                    $eventosPagados = (int) $promo->eventos_pagados;
                    $eventosFaltan = max($totalEventos - $eventosPagados, 0);
                @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-4">{{ $promo->dni }}</td>
                <td class="px-4 py-4">
                    <div class="font-semibold">{{ $promo->nombreCompleto() }}</div>
                    @if($promo->participa_cuotas)
                        <span class="text-xs text-green-700">Participa en cuotas</span>
                    @else
                        <span class="text-xs text-gray-500">No participa en cuotas</span>
                    @endif
                </td>
                <td class="px-4 py-4">{{ $promo->celular ?? '-' }}</td>
                <td class="px-4 py-4 text-center">
                    <a href="{{ route('seccionk.admin.promociones.show', $promo) }}" class="inline-block">
                        <div class="text-sm font-bold {{ $cuotasFaltan === 0 && $totalCuotas > 0 ? 'text-green-600' : 'text-purple-700' }}">
                            {{ $cuotasPagadas }}/{{ $totalCuotas }} pagadas
                        </div>
                        <div class="text-xs {{ $cuotasFaltan === 0 ? 'text-green-600' : 'text-amber-600' }}">
                            {{ $cuotasFaltan === 0 ? 'Al día' : $cuotasFaltan.' por pagar' }}
                        </div>
                    </a>
                </td>
                <td class="px-4 py-4 text-center">
                    <a href="{{ route('seccionk.admin.promociones.show', $promo) }}" class="inline-block">
                        <div class="text-sm font-bold {{ $eventosFaltan === 0 && $totalEventos > 0 ? 'text-green-600' : 'text-blue-700' }}">
                            {{ $eventosPagados }}/{{ $totalEventos }} pagados
                        </div>
                        <div class="text-xs {{ $eventosFaltan === 0 ? 'text-green-600' : 'text-amber-600' }}">
                            {{ $totalEventos === 0 ? 'Sin eventos' : ($eventosFaltan === 0 ? 'Al día' : $eventosFaltan.' por pagar') }}
                        </div>
                    </a>
                </td>
                <td class="px-4 py-4 text-center">
                    @if($promo->activo)
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Activo</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Inactivo</span>
                    @endif
                </td>
                <td class="px-4 py-4 text-center space-x-2 whitespace-nowrap">
                    <a href="{{ route('seccionk.admin.promociones.show', $promo) }}" class="text-purple-600 hover:text-purple-800" title="Ver pagos">
                        <i class="fas fa-receipt"></i>
                    </a>
                    <a href="{{ route('seccionk.admin.promociones.edit', $promo) }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    @if($promo->activo)
                        <form action="{{ route('seccionk.admin.promociones.destroy', $promo) }}" method="POST" class="inline" onsubmit="return confirm('¿Ocultar esta promoción? Dejará de salir en reportes y en el sitio público.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-amber-600 hover:text-amber-800" title="Ocultar">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('seccionk.admin.promociones.restaurar', $promo) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800" title="Restaurar">
                                <i class="fas fa-undo"></i>
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('seccionk.admin.promociones.eliminar', $promo) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar DEFINITIVO a {{ $promo->nombreCompleto() }}? Se borrarán sus pagos y vínculos de eventos.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar definitivo">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No hay promociones registradas</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $promociones->withQueryString()->links() }}
</div>
@endsection
