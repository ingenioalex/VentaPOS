@extends('seccionk.admin.layout')

@php $editando = isset($promocion); @endphp

@section('title', ($editando ? 'Editar' : 'Nueva') . ' Promoción - Admin Sección K')
@section('page-title', $editando ? 'Editar Promoción' : 'Nueva Promoción')

@section('content')
<div class="max-w-3xl">
    <form action="{{ $editando ? route('seccionk.admin.promociones.update', $promocion) : route('seccionk.admin.promociones.store') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8 space-y-6">
        @csrf
        @if($editando)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">DNI</label>
                <input type="text" name="dni" value="{{ old('dni', $promocion->dni ?? '') }}" required maxlength="8" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                @error('dni')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Celular</label>
                <input type="text" name="celular" value="{{ old('celular', $promocion->celular ?? '') }}" maxlength="15" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre', $promocion->nombre ?? '') }}" required maxlength="100" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
            @error('nombre')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Apellido paterno</label>
                <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $promocion->apellido_paterno ?? '') }}" required maxlength="100" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Apellido materno</label>
                <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $promocion->apellido_materno ?? '') }}" required maxlength="100" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección</label>
            <input type="text" name="direccion" value="{{ old('direccion', $promocion->direccion ?? '') }}" maxlength="255" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Referencias</label>
            <textarea name="referencias" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">{{ old('referencias', $promocion->referencias ?? '') }}</textarea>
        </div>

        <label class="flex items-center space-x-2">
            <input type="hidden" name="participa_cuotas" value="0">
            <input type="checkbox" name="participa_cuotas" value="1" {{ old('participa_cuotas', $promocion->participa_cuotas ?? false) ? 'checked' : '' }}>
            <span>Participa en cuotas</span>
        </label>

        @if($editando)
        <label class="flex items-center space-x-2">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" {{ old('activo', $promocion->activo) ? 'checked' : '' }}>
            <span>Activo</span>
        </label>
        @endif

        <div class="flex justify-end space-x-3">
            <a href="{{ route('seccionk.admin.promociones.index') }}" class="px-6 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Guardar</button>
        </div>
    </form>
</div>
@endsection
