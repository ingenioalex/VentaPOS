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
    <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Miembro</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Participó</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Pagó</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Monto</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">N° transacción</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha pago</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Comentario</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Imagen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($promociones as $promo)
                    @php $part = $participaciones->get($promo->id); @endphp
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $promo->nombreCompleto() }}</div>
                            <div class="text-xs text-gray-500">{{ $promo->dni }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="hidden" name="participantes[{{ $promo->id }}][participo]" value="0">
                            <input type="checkbox" name="participantes[{{ $promo->id }}][participo]" value="1" {{ old("participantes.{$promo->id}.participo", $part?->participo) ? 'checked' : '' }}>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="hidden" name="participantes[{{ $promo->id }}][pago]" value="0">
                            <input type="checkbox" name="participantes[{{ $promo->id }}][pago]" value="1" {{ old("participantes.{$promo->id}.pago", $part?->pago) ? 'checked' : '' }}>
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" min="0" name="participantes[{{ $promo->id }}][monto_aportado]" value="{{ old("participantes.{$promo->id}.monto_aportado", $part->monto_aportado ?? '') }}" class="w-24 px-2 py-1 border rounded">
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" name="participantes[{{ $promo->id }}][numero_transaccion]" value="{{ old("participantes.{$promo->id}.numero_transaccion", $part->numero_transaccion ?? '') }}" class="w-32 px-2 py-1 border rounded">
                        </td>
                        <td class="px-4 py-3">
                            <input type="date" name="participantes[{{ $promo->id }}][fecha_pago]" value="{{ old("participantes.{$promo->id}.fecha_pago", $part?->fecha_pago?->format('Y-m-d')) }}" class="px-2 py-1 border rounded">
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" name="participantes[{{ $promo->id }}][observaciones]" value="{{ old("participantes.{$promo->id}.observaciones", $part->observaciones ?? '') }}" class="w-40 px-2 py-1 border rounded">
                        </td>
                        <td class="px-4 py-3">
                            @if($part?->imagen_sustento)
                                <a href="{{ asset('uploads/seccionk/'.$part->imagen_sustento) }}" target="_blank" class="text-purple-600 text-xs block mb-1">Ver imagen</a>
                            @endif
                            <input type="file" name="participantes[{{ $promo->id }}][imagen_sustento]" accept="image/jpeg,image/png,image/webp" class="text-xs w-40">
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">No hay miembros activos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($promociones->isNotEmpty())
        <div class="mt-4 bg-gray-50 rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <p class="text-sm text-gray-600">
                {{ $participantes }}/{{ $total_promociones }} participaron · {{ $aportantes }} aportaron
            </p>
            <p class="text-lg font-bold text-green-700">Total recaudado: S/ {{ number_format($recaudado, 2) }}</p>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">
                <i class="fas fa-save mr-2"></i>Guardar participantes
            </button>
        </div>
    @endif
</form>
@endsection
