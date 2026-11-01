@extends('seccionk.layout')

@section('title', 'Consultar - Sección K Promoción 2001')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Hero Section -->
        <div class="text-center mb-8">
            <div class="mx-auto w-24 h-24 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-users text-white text-4xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Sección K</h1>
            <p class="text-xl text-gray-600 mb-1">Promoción 2001</p>
            <p class="text-sm text-gray-500">Consulta tu participación y aportes</p>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Buscar Mi Perfil</h2>
            
            @if($errors->has('busqueda'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                        <p class="text-red-700 text-sm">{{ $errors->first('busqueda') }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('seccionk.buscar') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="dni" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-id-card text-purple-600 mr-2"></i>Número de DNI
                    </label>
                    <input 
                        type="text" 
                        name="dni" 
                        id="dni" 
                        maxlength="8"
                        required
                        placeholder="Ej: 12345678"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('dni') border-red-500 @enderror"
                        value="{{ old('dni') }}"
                    >
                    @error('dni')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="apellido" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user text-purple-600 mr-2"></i>Primer Apellido
                    </label>
                    <input 
                        type="text" 
                        name="apellido" 
                        id="apellido" 
                        required
                        placeholder="Ej: García"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('apellido') border-red-500 @enderror"
                        value="{{ old('apellido') }}"
                    >
                    @error('apellido')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button 
                    type="submit" 
                    class="w-full gradient-bg text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition transform hover:scale-105 flex items-center justify-center"
                >
                    <i class="fas fa-search mr-2"></i>
                    Buscar Mi Información
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600 mb-3">¿Aún no estás registrado?</p>
                <a 
                    href="{{ route('seccionk.registro') }}" 
                    class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold transition"
                >
                    <i class="fas fa-user-plus mr-2"></i>
                    Regístrate aquí
                </a>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4 shadow text-center">
                <i class="fas fa-calendar-alt text-purple-600 text-2xl mb-2"></i>
                <p class="text-sm font-semibold text-gray-700">Seguimiento</p>
                <p class="text-xs text-gray-500">de eventos y pagos</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow text-center">
                <i class="fas fa-money-bill-wave text-purple-600 text-2xl mb-2"></i>
                <p class="text-sm font-semibold text-gray-700">Cuotas Mensuales</p>
                <p class="text-xs text-gray-500">Octubre 2026 - Diciembre 2027</p>
            </div>
        </div>
    </div>
</div>
@endsection
