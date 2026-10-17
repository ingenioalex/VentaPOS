@extends('seccionk.layout')

@section('title', 'Consultar - Sección K Promoción 2001')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-8 sm:py-12 px-3 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-6 sm:mb-8">
            <div class="mx-auto w-16 h-16 sm:w-24 sm:h-24 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-users text-white text-2xl sm:text-4xl"></i>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">Sección K</h1>
            <p class="text-lg sm:text-xl text-gray-600 mb-1">Promoción 2001</p>
            <p class="text-sm text-gray-500">Identifícate con tu DNI para ver tu participación y los aportes</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">Buscar Mi Perfil</h2>

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
                    class="w-full gradient-bg text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition flex items-center justify-center"
                >
                    <i class="fas fa-search mr-2"></i>
                    Identificarme
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
    </div>
</div>
@endsection
