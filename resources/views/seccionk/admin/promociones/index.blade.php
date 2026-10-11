@extends('seccionk.admin.layout')

@section('title', 'Promociones - Admin Sección K')
@section('page-title', 'Gestión de Promociones')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <form method="GET" class="flex space-x-4">
        <input type="text" name="buscar" placeholder="Buscar por DNI o nombre..." value="{{ request('buscar') }}" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
        <select name="participa_cuotas" class="px-4 py-2 border rounded-lg">
            <option value="">Todos</option>
            <option value="1" {{ request('participa_cuotas') === '1' ? 'selected' : '' }}>Participa en cuotas</option>
            <option value="0" {{ request('participa_cuotas') === '0' ? 'selected' : '' }}>No participa</option>
        </select>
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="mostrar_inactivos" {{ request('mostrar_inactivos') ? 'checked' : '' }}>
            <span>Mostrar inactivos</span>
        </label>
        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Filtrar</button>
    </form>
    <a href="{{ route('seccionk.admin.promociones.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
        <i class="fas fa-plus mr-2"></i>Nueva Promoción
    </a>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">DNI</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nombre Completo</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Celular</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Participa Cuotas</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($promociones as $promo)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">{{ $promo->dni }}</td>
                <td class="px-6 py-4 font-semibold">{{ $promo->nombreCompleto() }}</td>
                <td class="px-6 py-4">{{ $promo->celular ?? '-' }}</td>
                <td class="px-6 py-4 text-center">
                    @if($promo->participa_cuotas)
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Sí</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-semibold">No</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    @if($promo->activo)
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Activo</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Inactivo</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center space-x-2">
                    <a href="{{ route('seccionk.admin.promociones.edit', $promo) }}" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('seccionk.admin.promociones.destroy', $promo) }}" method="POST" class="inline" onsubmit="return confirm('¿Ocultar esta promoción?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No hay promociones registradas</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $promociones->links() }}
</div>
@endsection
