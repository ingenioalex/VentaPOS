@extends('seccionk.admin.layout')

@section('title', 'Pagos - ' . $promocion->nombreCompleto())
@section('page-title', 'Pagos de ' . $promocion->nombreCompleto())

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="text-gray-600">
        DNI {{ $promocion->dni }}
        @if($promocion->celular) · {{ $promocion->celular }} @endif
        @if($promocion->referencias)
            <div class="mt-2 text-sm text-gray-700">
                <span class="font-semibold">Referencias:</span> {{ $promocion->referencias }}
            </div>
        @endif
    </div>
    <div class="flex gap-3">
        <a href="{{ url('seccionk/perfil/'.$promocion->getKey()) }}" target="_blank" class="text-purple-600 font-semibold">Ver perfil público</a>
        <a href="{{ route('seccionk.admin.promociones.index') }}" class="text-gray-600 font-semibold">Volver</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <p class="text-sm text-gray-500 font-semibold mb-1">Cuotas mensuales</p>
        <p class="text-3xl font-bold text-purple-600">{{ $cuotasPagadas }}/{{ $cuotas->count() }} pagadas</p>
        <p class="text-sm mt-1 {{ $cuotas->count() - $cuotasPagadas === 0 ? 'text-green-600' : 'text-amber-600' }}">
            {{ $cuotas->count() - $cuotasPagadas }} por pagar
            · S/ {{ number_format($cuotas->where('pago', true)->sum('monto_aportado'), 2) }} aportado
        </p>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <p class="text-sm text-gray-500 font-semibold mb-1">Eventos</p>
        <p class="text-3xl font-bold text-blue-600">{{ $eventosPagados }}/{{ $eventos->count() }} pagados</p>
        <p class="text-sm mt-1 {{ $eventos->count() - $eventosPagados === 0 ? 'text-green-600' : 'text-amber-600' }}">
            {{ $eventos->count() - $eventosPagados }} por pagar
            · S/ {{ number_format($eventos->where('pago', true)->sum('monto_aportado'), 2) }} aportado
        </p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="font-bold text-gray-900"><i class="fas fa-calendar-check text-purple-600 mr-2"></i>Cuotas mensuales</h2>
        </div>
        <div class="divide-y">
            @forelse($cuotas as $cuota)
                <div class="px-6 py-4">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <p class="font-semibold">{{ $cuota['evento']->mes_cuota ?: $cuota['evento']->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ $cuota['evento']->fecha->format('d/m/Y') }} · Sugerido S/ {{ number_format($cuota['evento']->monto, 2) }}</p>
                        </div>
                        @if($cuota['pago'])
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Pagó</span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">Falta pagar</span>
                        @endif
                    </div>
                    @if($cuota['pago'])
                        <p class="text-sm text-green-700 mt-2">S/ {{ number_format($cuota['monto_aportado'], 2) }}
                            @if($cuota['fecha_pago']) · {{ $cuota['fecha_pago']->format('d/m/Y') }} @endif
                            @if($cuota['numero_transaccion']) · {{ $cuota['numero_transaccion'] }} @endif
                        </p>
                    @endif
                    @if($cuota['comentario'])
                        <p class="text-sm text-gray-600 mt-1">{{ $cuota['comentario'] }}</p>
                    @endif
                    @if($cuota['imagen'])
                        <a href="{{ asset('uploads/seccionk/'.$cuota['imagen']) }}" target="_blank" class="text-xs text-purple-600">Ver imagen</a>
                    @endif

                    <form action="{{ route('seccionk.admin.promociones.pagos.store', ['promocion' => $promocion->getKey(), 'evento' => $cuota['evento']->getKey()]) }}" method="POST" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @csrf
                        <input type="number" name="monto_aportado" step="0.01" min="0" required value="{{ old('monto_aportado', $cuota['pago'] ? $cuota['monto_aportado'] : $cuota['evento']->monto) }}" class="px-3 py-2 border rounded-lg text-sm" placeholder="Monto">
                        <input type="text" name="numero_transaccion" value="{{ old('numero_transaccion', $cuota['numero_transaccion']) }}" class="px-3 py-2 border rounded-lg text-sm" placeholder="N° transacción (opcional)">
                        <input type="date" name="fecha_pago" value="{{ old('fecha_pago', optional($cuota['fecha_pago'])->format('Y-m-d') ?: date('Y-m-d')) }}" class="px-3 py-2 border rounded-lg text-sm">
                        <input type="text" name="observaciones" value="{{ old('observaciones', $cuota['comentario']) }}" class="px-3 py-2 border rounded-lg text-sm" placeholder="Comentario (opcional)">
                        <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="text-xs sm:col-span-2">
                        <button type="submit" class="sm:col-span-2 bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700">
                            {{ $cuota['pago'] ? 'Actualizar pago' : 'Registrar pago' }}
                        </button>
                    </form>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-gray-500">No hay cuotas registradas</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="font-bold text-gray-900"><i class="fas fa-star text-blue-600 mr-2"></i>Eventos</h2>
        </div>
        <div class="divide-y">
            @forelse($eventos as $evento)
                <div class="px-6 py-4">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <p class="font-semibold">{{ $evento['evento']->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ $evento['evento']->fecha->format('d/m/Y') }} · Sugerido S/ {{ number_format($evento['evento']->monto, 2) }}</p>
                        </div>
                        @if($evento['pago'])
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Pagó</span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">Falta pagar</span>
                        @endif
                    </div>
                    <p class="text-xs mt-1 {{ $evento['participo'] ? 'text-green-700' : 'text-gray-500' }}">
                        {{ $evento['participo'] ? 'Participó' : 'No participó' }}
                    </p>
                    @if($evento['pago'])
                        <p class="text-sm text-green-700 mt-1">S/ {{ number_format($evento['monto_aportado'], 2) }}
                            @if($evento['fecha_pago']) · {{ $evento['fecha_pago']->format('d/m/Y') }} @endif
                            @if($evento['numero_transaccion']) · {{ $evento['numero_transaccion'] }} @endif
                        </p>
                    @endif
                    @if($evento['comentario'])
                        <p class="text-sm text-gray-600 mt-1">{{ $evento['comentario'] }}</p>
                    @endif
                    @if($evento['imagen'])
                        <a href="{{ asset('uploads/seccionk/'.$evento['imagen']) }}" target="_blank" class="text-xs text-purple-600">Ver imagen</a>
                    @endif

                    <form action="{{ route('seccionk.admin.promociones.pagos.store', ['promocion' => $promocion->getKey(), 'evento' => $evento['evento']->getKey()]) }}" method="POST" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @csrf
                        <input type="number" name="monto_aportado" step="0.01" min="0" required value="{{ old('monto_aportado', $evento['pago'] ? $evento['monto_aportado'] : $evento['evento']->monto) }}" class="px-3 py-2 border rounded-lg text-sm" placeholder="Monto">
                        <input type="text" name="numero_transaccion" value="{{ old('numero_transaccion', $evento['numero_transaccion']) }}" class="px-3 py-2 border rounded-lg text-sm" placeholder="N° transacción (opcional)">
                        <input type="date" name="fecha_pago" value="{{ old('fecha_pago', optional($evento['fecha_pago'])->format('Y-m-d') ?: date('Y-m-d')) }}" class="px-3 py-2 border rounded-lg text-sm">
                        <input type="text" name="observaciones" value="{{ old('observaciones', $evento['comentario']) }}" class="px-3 py-2 border rounded-lg text-sm" placeholder="Comentario (opcional)">
                        <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="text-xs sm:col-span-2">
                        <button type="submit" class="sm:col-span-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                            {{ $evento['pago'] ? 'Actualizar pago' : 'Registrar pago' }}
                        </button>
                    </form>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-gray-500">No hay eventos registrados</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
