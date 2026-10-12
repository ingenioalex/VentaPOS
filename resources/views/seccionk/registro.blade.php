@extends('seccionk.layout')

@section('title', 'Registro - Sección K Promoción 2001')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Registro de Miembro</h1>
            <p class="text-gray-600">Sección K - Promoción 2001</p>
        </div>

        <form action="{{ route('seccionk.registro.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- DNI -->
                <div class="md:col-span-2">
                    <label for="dni" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-id-card text-purple-600 mr-2"></i>Número de DNI *
                    </label>
                    <input 
                        type="text" 
                        name="dni" 
                        id="dni" 
                        maxlength="8"
                        required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('dni') border-red-500 @enderror"
                        value="{{ old('dni') }}"
                    >
                    @error('dni')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user text-purple-600 mr-2"></i>Nombres *
                    </label>
                    <input 
                        type="text" 
                        name="nombre" 
                        id="nombre" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('nombre') border-red-500 @enderror"
                        value="{{ old('nombre') }}"
                    >
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido Paterno -->
                <div>
                    <label for="apellido_paterno" class="block text-sm font-semibold text-gray-700 mb-2">
                        Apellido Paterno *
                    </label>
                    <input 
                        type="text" 
                        name="apellido_paterno" 
                        id="apellido_paterno" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('apellido_paterno') border-red-500 @enderror"
                        value="{{ old('apellido_paterno') }}"
                    >
                    @error('apellido_paterno')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido Materno -->
                <div>
                    <label for="apellido_materno" class="block text-sm font-semibold text-gray-700 mb-2">
                        Apellido Materno *
                    </label>
                    <input 
                        type="text" 
                        name="apellido_materno" 
                        id="apellido_materno" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('apellido_materno') border-red-500 @enderror"
                        value="{{ old('apellido_materno') }}"
                    >
                    @error('apellido_materno')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Celular -->
                <div>
                    <label for="celular" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone text-purple-600 mr-2"></i>Celular
                    </label>
                    <input 
                        type="text" 
                        name="celular" 
                        id="celular" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('celular') border-red-500 @enderror"
                        value="{{ old('celular') }}"
                    >
                    @error('celular')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dirección -->
                <div class="md:col-span-2">
                    <label for="direccion" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt text-purple-600 mr-2"></i>Dirección
                    </label>
                    <input 
                        type="text" 
                        name="direccion" 
                        id="direccion" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('direccion') border-red-500 @enderror"
                        value="{{ old('direccion') }}"
                    >
                    @error('direccion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Referencias -->
                <div class="md:col-span-2">
                    <label for="referencias" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-users text-purple-600 mr-2"></i>Referencias de otras promociones
                    </label>
                    <textarea 
                        name="referencias" 
                        id="referencias" 
                        rows="2"
                        placeholder="Ej: Manuel Llauce Pizarro celular 909090678"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition @error('referencias') border-red-500 @enderror"
                    >{{ old('referencias') }}</textarea>
                    @error('referencias')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Participa en Cuotas -->
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="participa_cuotas" 
                            value="1"
                            {{ old('participa_cuotas') ? 'checked' : '' }}
                            class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500"
                        >
                        <span class="text-gray-700">
                            <i class="fas fa-hand-holding-usd text-purple-600 mr-2"></i>
                            <strong>Deseo participar en las cuotas mensuales</strong>
                            <span class="block text-sm text-gray-500 ml-7">
                                Aporte mensual de S/ 10.00 desde Octubre 2026 hasta Diciembre 2027
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('seccionk.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <button 
                    type="submit" 
                    class="gradient-bg text-white font-bold py-3 px-8 rounded-lg hover:opacity-90 transition transform hover:scale-105"
                >
                    <i class="fas fa-check mr-2"></i>
                    Registrarse
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
