@php
    $recaudadoForm = $recaudado ?? 0;
    $utilizadoForm = $evento->montoUtilizado();
    $cerradoForm = $evento->estaCerrado();
    $volverForm = $volver ?? 'detalle';
@endphp
<form action="{{ route('seccionk.admin.eventos.cerrar', $evento) }}" method="POST" class="space-y-3">
    @csrf
    <input type="hidden" name="volver" value="{{ $volverForm }}">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Monto utilizado (S/)</label>
        <input
            type="number"
            step="0.01"
            min="0"
            name="monto_utilizado"
            required
            value="{{ old('monto_utilizado', $cerradoForm ? $utilizadoForm : $recaudadoForm) }}"
            class="w-full px-3 py-2 border rounded-lg"
        >
        <p class="text-xs text-gray-500 mt-1">Recaudado: S/ {{ number_format($recaudadoForm, 2) }}</p>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Observación (opcional)</label>
        <input type="text" name="observaciones" maxlength="1000" value="{{ old('observaciones', $evento->declaracion?->observaciones) }}" class="w-full px-3 py-2 border rounded-lg" placeholder="Ej: alquiler, refrigerio, material">
    </div>
    <button type="submit" class="w-full sm:w-auto bg-amber-600 text-white px-5 py-2 rounded-lg hover:bg-amber-700 font-semibold">
        <i class="fas fa-lock mr-2"></i>{{ $cerradoForm ? 'Actualizar declaración' : 'Cerrar y declarar uso' }}
    </button>
</form>
