@extends('seccionk.admin.layout')

@section('title', 'Participantes - Admin Sección K')
@section('page-title', 'Participantes: ' . $evento->nombre)

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <p class="text-gray-600">
            <i class="fas fa-calendar mr-2"></i>{{ $evento->fecha->format('d/m/Y') }}
            · Sugerido: <span class="font-semibold">S/ {{ number_format($evento->monto, 2) }}</span>
        </p>
        <p class="text-sm text-gray-500 mt-1">
            {{ $participantes }}/{{ $total_promociones }} participaron ·
            {{ $aportantes }} aportaron ·
            Total recaudado <span class="font-semibold text-green-700">S/ {{ number_format($recaudado, 2) }}</span>
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('seccionk.admin.eventos.detalle', $evento) }}" class="text-green-700 hover:text-green-900 font-semibold">
            <i class="fas fa-list-ol mr-2"></i>Ver detalle y total
        </a>
        <a href="{{ route('seccionk.admin.eventos.index') }}" class="text-purple-600 hover:text-purple-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Volver a eventos
        </a>
    </div>
</div>

<form action="{{ route('seccionk.admin.eventos.participantes.store', $evento) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        @forelse($promociones as $promo)
            @php $part = $participaciones->get($promo->id); @endphp
            <div class="bg-white rounded-xl shadow p-4 space-y-3">
                <div>
                    <p class="font-semibold text-gray-900 break-words">{{ $promo->nombreCompleto() }}</p>
                    <p class="text-xs text-gray-500">DNI {{ $promo->dni }}</p>
                </div>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="hidden" name="participantes[{{ $promo->id }}][participo]" value="0">
                        <input type="checkbox" class="w-5 h-5" name="participantes[{{ $promo->id }}][participo]" value="1" {{ old("participantes.{$promo->id}.participo", $part?->participo) ? 'checked' : '' }}>
                        Participó
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="hidden" name="participantes[{{ $promo->id }}][pago]" value="0">
                        <input type="checkbox" class="w-5 h-5" name="participantes[{{ $promo->id }}][pago]" value="1" {{ old("participantes.{$promo->id}.pago", $part?->pago) ? 'checked' : '' }}>
                        Pagó
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <input type="number" step="0.01" min="0" name="participantes[{{ $promo->id }}][monto_aportado]" value="{{ old("participantes.{$promo->id}.monto_aportado", $part->monto_aportado ?? '') }}" class="w-full px-3 py-2 border rounded-lg" placeholder="Monto S/">
                    <input type="text" name="participantes[{{ $promo->id }}][numero_transaccion]" value="{{ old("participantes.{$promo->id}.numero_transaccion", $part->numero_transaccion ?? '') }}" class="w-full px-3 py-2 border rounded-lg" placeholder="N° transacción">
                    <input type="date" name="participantes[{{ $promo->id }}][fecha_pago]" value="{{ old("participantes.{$promo->id}.fecha_pago", $part?->fecha_pago?->format('Y-m-d')) }}" class="w-full px-3 py-2 border rounded-lg">
                    <input type="text" name="participantes[{{ $promo->id }}][observaciones]" value="{{ old("participantes.{$promo->id}.observaciones", $part->observaciones ?? '') }}" class="w-full px-3 py-2 border rounded-lg" placeholder="Comentario">
                    <div class="sm:col-span-2">
                        @if($part?->imagen_sustento)
                            <a href="{{ asset('uploads/seccionk/'.$part->imagen_sustento) }}" target="_blank" class="text-purple-600 text-xs block mb-1">Ver imagen</a>
                        @endif
                        <input type="file" name="participantes[{{ $promo->id }}][imagen_sustento]" accept="image/jpeg,image/png,image/webp,image/*" class="text-xs w-full">
                        <p class="nombre-archivo text-xs text-gray-500 mt-1">Foto opcional. Si pesa mucho, se omite y el resto se guarda.</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-12 col-span-full">No hay miembros activos</p>
        @endforelse
    </div>

    @if($promociones->isNotEmpty())
        <div class="mt-4 bg-white rounded-xl shadow px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <p class="text-sm text-gray-600">
                {{ $participantes }}/{{ $total_promociones }} participaron · {{ $aportantes }} aportaron
            </p>
            <p class="text-lg font-bold text-green-700">Total recaudado: S/ {{ number_format($recaudado, 2) }}</p>
        </div>
        <div class="mt-6">
            <button type="submit" class="w-full sm:w-auto bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700">
                <i class="fas fa-save mr-2"></i>Guardar participantes
            </button>
        </div>
    @endif
</form>
@endsection
