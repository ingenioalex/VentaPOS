@extends('seccionk.admin.layout')

@section('title', 'Dashboard - Admin Sección K')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-semibold mb-1">Total Promociones</p>
                <p class="text-3xl font-bold text-purple-600">{{ $totalPromociones }}</p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-users text-purple-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-semibold mb-1">Participan en Cuotas</p>
                <p class="text-3xl font-bold text-green-600">{{ $participanCuotas }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-hand-holding-usd text-green-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-semibold mb-1">Total Eventos</p>
                <p class="text-3xl font-bold text-blue-600">{{ $totalEventos }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-calendar-alt text-blue-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-semibold mb-1">Cuotas Mensuales</p>
                <p class="text-3xl font-bold text-indigo-600">{{ $totalCuotas }}</p>
            </div>
            <div class="bg-indigo-100 rounded-full p-3">
                <i class="fas fa-money-bill-wave text-indigo-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Eventos Recientes</h3>
        <div class="space-y-3">
            @forelse($eventosRecientes as $evento)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $evento->nombre }}</p>
                        <p class="text-sm text-gray-600">{{ $evento->fecha->format('d/m/Y') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $evento->tipo === 'cuota_mensual' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $evento->tipo === 'cuota_mensual' ? 'Cuota' : 'Evento' }}
                    </span>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No hay eventos registrados</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Accesos Rápidos</h3>
        </div>
        <div class="space-y-3">
            <a href="{{ route('seccionk.admin.promociones.create') }}" class="flex items-center space-x-4 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg hover:shadow-md transition">
                <div class="bg-purple-600 rounded-lg p-3">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Nueva Promoción</p>
                    <p class="text-sm text-gray-600">Registrar nuevo miembro</p>
                </div>
            </a>

            <a href="{{ route('seccionk.admin.eventos.create') }}" class="flex items-center space-x-4 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg hover:shadow-md transition">
                <div class="bg-blue-600 rounded-lg p-3">
                    <i class="fas fa-calendar-plus text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Nuevo Evento</p>
                    <p class="text-sm text-gray-600">Crear evento o cuota</p>
                </div>
            </a>

            <a href="{{ route('seccionk.admin.promociones.index') }}" class="flex items-center space-x-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg hover:shadow-md transition">
                <div class="bg-green-600 rounded-lg p-3">
                    <i class="fas fa-list text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Listar Promociones</p>
                    <p class="text-sm text-gray-600">Ver todos los miembros</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
