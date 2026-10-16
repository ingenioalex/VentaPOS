@extends('seccionk.admin.layout')

@section('title', 'Referencias - Admin Sección K')
@section('page-title', 'Referencias de promociones')

@section('content')
<div class="mb-6 bg-white rounded-xl shadow-lg p-5">
    <p class="text-gray-600 text-sm">
        Aquí se ven los nombres o celulares que cada promoción indicó de otras promociones.
        {{ $conReferencia }} {{ $conReferencia === 1 ? 'promoción ya dejó' : 'promociones ya dejaron' }} referencia.
    </p>
</div>

<div class="mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <input type="text" name="buscar" placeholder="Buscar nombre, DNI, celular o referencia..." value="{{ request('buscar') }}" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 w-full sm:w-72">
        <select name="filtro" class="px-4 py-2 border rounded-lg">
            <option value="con" @selected(request('filtro', 'con') !== 'todas')>Con referencia</option>
            <option value="todas" @selected(request('filtro') === 'todas')>Todas las promociones</option>
        </select>
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="mostrar_inactivos" value="1" @checked(request('mostrar_inactivos'))>
            <span>Mostrar inactivos</span>
        </label>
        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Buscar</button>
    </form>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('seccionk.admin.referencias.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
            <i class="fas fa-file-excel mr-2"></i>Excel
        </a>
        <a href="{{ route('seccionk.admin.referencias.exportar', array_merge(request()->query(), ['formato' => 'whatsapp'])) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            <i class="fab fa-whatsapp mr-2"></i>WhatsApp
        </a>
    </div>
</div>

<div class="space-y-3 lg:hidden">
    @forelse($promociones as $promo)
        <div class="bg-white rounded-xl shadow p-4">
            <a href="{{ route('seccionk.admin.promociones.edit', $promo) }}" class="font-semibold text-purple-700">{{ $promo->nombreCompleto() }}</a>
            <p class="text-sm text-gray-500 mt-1">DNI {{ $promo->dni }} @if($promo->celular)· {{ $promo->celular }}@endif</p>
            <p class="text-sm text-gray-800 mt-2 whitespace-pre-line">{{ $promo->referencias ?: 'Sin referencia' }}</p>
            <a href="{{ route('seccionk.admin.promociones.edit', $promo) }}" class="inline-block mt-3 text-sm font-semibold text-blue-700">Editar</a>
        </div>
    @empty
        <p class="text-center text-gray-500 py-8">Nadie ha dejado referencias todavía.</p>
    @endforelse
</div>

<div class="hidden lg:block bg-white rounded-xl shadow-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Quién lo indicó</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">DNI</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Celular</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Referencias (nombres o números)</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acción</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($promociones as $promo)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4">
                        <a href="{{ route('seccionk.admin.promociones.edit', $promo) }}" class="font-semibold text-purple-700 hover:text-purple-900">
                            {{ $promo->nombreCompleto() }}
                        </a>
                    </td>
                    <td class="px-4 py-4">{{ $promo->dni }}</td>
                    <td class="px-4 py-4">{{ $promo->celular ?: '—' }}</td>
                    <td class="px-4 py-4">
                        @if($promo->referencias)
                            <p class="text-gray-800 whitespace-pre-line">{{ $promo->referencias }}</p>
                        @else
                            <span class="text-gray-400">Sin referencia</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <a href="{{ route('seccionk.admin.promociones.edit', $promo) }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        Nadie ha dejado referencias todavía. Se cargan desde el registro o al editar una promoción.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $promociones->withQueryString()->links() }}
</div>
@endsection
