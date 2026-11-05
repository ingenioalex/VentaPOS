@php
    $volver = $volver ?? 'fondo';
    $eventoDestino = $eventoDestino ?? null;
    $mostrarHistorial = $mostrarHistorial ?? true;
    $prestamosPendientes = $prestamos_pendientes ?? collect();
    $movimientos = $movimientos ?? collect();
@endphp

<div class="bg-white rounded-xl shadow p-5 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="font-bold text-gray-900">
                <i class="fas fa-exchange-alt text-teal-600 mr-2"></i>
                Ingresos / egresos del fondo
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Registra un préstamo (sale del fondo) y su devolución (vuelve al fondo), con comentario.
                El dashboard solo muestra el préstamo mientras esté pendiente.
            </p>
        </div>
        @if($volver !== 'fondo')
            <a href="{{ route('seccionk.admin.fondo') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">
                Ver módulo completo
            </a>
        @endif
    </div>

    @if($prestamosPendientes->isNotEmpty())
        <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3">
            <p class="text-sm font-semibold text-rose-800">Préstamos pendientes · S/ {{ number_format($total_prestamos_pendientes ?? 0, 2) }}</p>
            <ul class="mt-2 space-y-1 text-sm text-rose-700">
                @foreach($prestamosPendientes as $prestamo)
                    <li>
                        {{ $prestamo->persona ?: 'Sin nombre' }}
                        · {{ $prestamo->fecha->format('d/m/Y') }}
                        · Prestó S/ {{ number_format($prestamo->monto, 2) }}
                        · Debe S/ {{ number_format($prestamo->saldoPendiente(), 2) }}
                        @if($prestamo->comentario)
                            · {{ $prestamo->comentario }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('seccionk.admin.fondo.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @csrf
        <input type="hidden" name="volver" value="{{ $volver }}">
        @if($eventoDestino)
            <input type="hidden" name="evento" value="{{ $eventoDestino }}">
        @endif

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo</label>
            <select name="tipo" id="fondo-tipo" class="w-full px-3 py-2 border rounded-lg" required>
                @foreach(\App\Models\SeccionKMovimiento::tipos() as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ old('tipo', 'prestamo') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Persona</label>
            <input type="text" name="persona" value="{{ old('persona') }}" maxlength="150" class="w-full px-3 py-2 border rounded-lg" placeholder="Ej: Juan">
            @error('persona')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div id="campo-prestamo" class="sm:col-span-2 {{ old('tipo') === 'devolucion' ? '' : 'hidden' }}">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Préstamo que se devuelve</label>
            <select name="prestamo_id" class="w-full px-3 py-2 border rounded-lg">
                <option value="">Selecciona el préstamo pendiente</option>
                @foreach($prestamosPendientes as $prestamo)
                    <option value="{{ $prestamo->id }}" {{ (string) old('prestamo_id') === (string) $prestamo->id ? 'selected' : '' }}>
                        {{ $prestamo->persona }} · {{ $prestamo->fecha->format('d/m/Y') }} · pendiente S/ {{ number_format($prestamo->saldoPendiente(), 2) }}
                    </option>
                @endforeach
            </select>
            @error('prestamo_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Monto (S/)</label>
            <input type="number" name="monto" step="0.01" min="0.01" required value="{{ old('monto') }}" class="w-full px-3 py-2 border rounded-lg">
            @error('monto')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha</label>
            <input type="date" name="fecha" required value="{{ old('fecha', now()->timezone('America/Lima')->toDateString()) }}" class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Comentario</label>
            <input type="text" name="comentario" maxlength="1000" value="{{ old('comentario') }}" class="w-full px-3 py-2 border rounded-lg" placeholder="Ej: se sacó para prestar a Juan / devolvió el préstamo">
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-teal-700">
                Registrar movimiento
            </button>
        </div>
    </form>
</div>

@if($mostrarHistorial)
<div class="bg-white rounded-xl shadow overflow-hidden mb-6">
    <div class="px-5 py-4 border-b">
        <h3 class="font-bold text-gray-900">Historial del fondo</h3>
        <p class="text-xs text-gray-500 mt-1">
            Ingresos S/ {{ number_format($total_ingresos ?? 0, 2) }}
            · Egresos S/ {{ number_format($total_egresos ?? 0, 2) }}
        </p>
    </div>
    <div class="divide-y">
        @forelse($movimientos as $mov)
            <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                <div>
                    <p class="font-semibold text-gray-900">
                        {{ $mov->etiquetaTipo() }}
                        @if($mov->persona)
                            · {{ $mov->persona }}
                        @endif
                    </p>
                    <p class="text-xs text-gray-500">{{ $mov->fecha->format('d/m/Y') }}</p>
                    @if($mov->comentario)
                        <p class="text-sm text-gray-600 mt-1">{{ $mov->comentario }}</p>
                    @endif
                    @if($mov->esPrestamo())
                        <p class="text-xs mt-1 {{ $mov->estaPendiente() ? 'text-rose-700' : 'text-green-700' }}">
                            {{ $mov->estaPendiente() ? 'Pendiente S/ '.number_format($mov->saldoPendiente(), 2) : 'Ya devolvió el préstamo' }}
                        </p>
                    @endif
                </div>
                <p class="font-bold {{ $mov->esEgreso() ? 'text-rose-700' : 'text-green-700' }}">
                    {{ $mov->esEgreso() ? '−' : '+' }} S/ {{ number_format($mov->monto, 2) }}
                </p>
            </div>
        @empty
            <p class="px-5 py-8 text-center text-gray-500">Aún no hay ingresos ni egresos registrados</p>
        @endforelse
    </div>
</div>
@endif

<script>
(function () {
    var tipo = document.getElementById('fondo-tipo');
    var campo = document.getElementById('campo-prestamo');
    if (!tipo || !campo) return;
    function toggle() {
        campo.classList.toggle('hidden', tipo.value !== 'devolucion');
    }
    tipo.addEventListener('change', toggle);
    toggle();
})();
</script>
