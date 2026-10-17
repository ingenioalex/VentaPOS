@extends('seccionk.admin.layout')

@php $editando = isset($evento); @endphp

@section('title', ($editando ? 'Editar' : 'Nuevo') . ' Evento - Admin Sección K')
@section('page-title', $editando ? 'Editar Evento' : 'Nuevo Evento')

@section('content')
<div class="max-w-3xl">
    <form action="{{ $editando ? route('seccionk.admin.eventos.update', $evento) : route('seccionk.admin.eventos.store') }}" method="POST" class="bg-white rounded-xl shadow-lg p-4 sm:p-8 space-y-6">
        @csrf
        @if($editando)
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre', $evento->nombre ?? '') }}" required maxlength="200" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
            @error('nombre')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
            <textarea name="descripcion" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">{{ old('descripcion', $evento->descripcion ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha</label>
                <input type="date" name="fecha" value="{{ old('fecha', isset($evento) ? $evento->fecha->format('Y-m-d') : '') }}" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                @error('fecha')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Monto (S/)</label>
                <input type="number" name="monto" step="0.01" min="0" value="{{ old('monto', $evento->monto ?? '0') }}" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                @error('monto')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo</label>
                <select name="tipo" id="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="evento" {{ old('tipo', $evento->tipo ?? '') === 'evento' ? 'selected' : '' }}>Evento</option>
                    <option value="cuota_mensual" {{ old('tipo', $evento->tipo ?? '') === 'cuota_mensual' ? 'selected' : '' }}>Cuota mensual</option>
                </select>
            </div>
            <div id="campo-mes" class="text-sm text-gray-600 bg-purple-50 rounded-lg p-3">
                El mes de la cuota se toma de la fecha (OCT26, NOV26). Solo puede haber una cuota por mes.
            </div>
        </div>

        @if($editando)
        <label class="flex items-center space-x-2">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" {{ old('activo', $evento->activo) ? 'checked' : '' }}>
            <span>Activo</span>
        </label>
        @endif

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
            <a href="{{ route('seccionk.admin.eventos.index') }}" class="px-6 py-3 sm:py-2 rounded-lg border text-gray-700 hover:bg-gray-50 text-center">Cancelar</a>
            <button type="submit" class="bg-purple-600 text-white px-6 py-3 sm:py-2 rounded-lg hover:bg-purple-700">Guardar</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function toggleMes() {
        document.getElementById('campo-mes').style.display =
            document.getElementById('tipo').value === 'cuota_mensual' ? 'block' : 'none';
    }
    document.getElementById('tipo').addEventListener('change', toggleMes);
    toggleMes();
</script>
@endsection
