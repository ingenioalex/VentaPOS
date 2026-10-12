@extends('seccionk.admin.layout')

@section('title', 'Eventos - Admin Sección K')
@section('page-title', 'Gestión de Eventos')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <select name="tipo" class="px-4 py-2 border rounded-lg">
            <option value="">Todos los tipos</option>
            <option value="cuota_mensual" {{ request('tipo') === 'cuota_mensual' ? 'selected' : '' }}>Cuotas mensuales</option>
            <option value="evento" {{ request('tipo') === 'evento' ? 'selected' : '' }}>Eventos</option>
        </select>
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="mostrar_inactivos" {{ request('mostrar_inactivos') ? 'checked' : '' }}>
            <span>Mostrar inactivos</span>
        </label>
        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Filtrar</button>
    </form>
    <a href="{{ route('seccionk.admin.eventos.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 text-center">
        <i class="fas fa-plus mr-2"></i>Nuevo Evento
    </a>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tipo</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Monto</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($eventos as $evento)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">{{ $evento->fecha->format('d/m/Y') }}</td>
                <td class="px-6 py-4">
                    <div class="font-semibold">{{ $evento->nombre }}</div>
                    @if($evento->mes_cuota)
                        <div class="text-xs text-gray-500">{{ $evento->mesCorto() }}</div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($evento->tipo === 'cuota_mensual')
                        <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-semibold">Cuota mensual</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Evento</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right font-semibold">S/ {{ number_format($evento->monto, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    @if($evento->activo)
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Activo</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Inactivo</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center space-x-3">
                    <a href="{{ route('seccionk.admin.eventos.participantes', $evento) }}" class="text-purple-600 hover:text-purple-800" title="Participantes">
                        <i class="fas fa-users"></i>
                    </a>
                    <a href="{{ route('seccionk.admin.eventos.edit', $evento) }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('seccionk.admin.eventos.destroy', $evento) }}" method="POST" class="inline" onsubmit="return confirm('¿Ocultar este evento?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Ocultar">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No hay eventos registrados</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $eventos->withQueryString()->links() }}
</div>
@endsection
