@extends('seccionk.layout')

@section('title', 'Mi Perfil - ' . $promocion->nombreCompleto())

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <div class="flex">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center space-x-6">
            <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                <span class="text-white text-3xl font-bold">
                    {{ substr($promocion->nombre, 0, 1) }}{{ substr($promocion->apellido_paterno, 0, 1) }}
                </span>
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $promocion->nombreCompleto() }}</h1>
                <p class="text-gray-600">
                    <i class="fas fa-id-card mr-2"></i>DNI: {{ $promocion->dni }}
                </p>
                @if($promocion->celular)
                    <p class="text-gray-600">
                        <i class="fas fa-phone mr-2"></i>{{ $promocion->celular }}
                    </p>
                @endif
            </div>
            <div class="text-right">
                @if($promocion->participa_cuotas)
                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 text-green-800 text-sm font-semibold">
                        <i class="fas fa-check-circle mr-2"></i>Participa en Cuotas
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-gray-100 text-gray-800 text-sm font-semibold">
                        No participa en cuotas
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Cuotas Pagadas</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $cuotasPagadas }}/{{ $totalCuotas }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <i class="fas fa-money-bill-wave text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Total Aportado</p>
                    <p class="text-3xl font-bold text-green-600">S/ {{ number_format($totalAportadoCuotas, 2) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-coins text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Eventos</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $eventosData->count() }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-calendar-alt text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Participaciones</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $eventosData->where('participo', true)->count() }}</p>
                </div>
                <div class="bg-indigo-100 rounded-full p-3">
                    <i class="fas fa-check-double text-indigo-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Timeline de Cuotas Mensuales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-calendar-check text-purple-600 mr-3"></i>
                    Línea de Tiempo - Cuotas Mensuales
                </h2>

                <div class="space-y-6 relative">
                    @foreach($cuotasData as $index => $cuota)
                        <div id="cuota-{{ $cuota['evento']->id }}" class="relative pl-12 timeline-item">
                            <div class="absolute left-0 top-0 w-10 h-10 rounded-full flex items-center justify-center {{ $cuota['pago'] ? 'bg-green-500' : 'bg-gray-300' }}">
                                <i class="fas {{ $cuota['pago'] ? 'fa-check' : 'fa-clock' }} text-white"></i>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 {{ $cuota['pago'] ? 'border-l-4 border-green-500' : '' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-gray-900">{{ $cuota['evento']->mes_cuota ?: $cuota['evento']->nombre }}</h3>
                                    @if($cuota['pago'])
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                            Pagado
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-calendar mr-2"></i>{{ $cuota['evento']->fecha->format('d/m/Y') }}
                                </p>
                                @if($cuota['pago'])
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">
                                            <i class="fas fa-money-bill mr-2"></i>Monto:
                                            <span class="font-semibold text-green-600">S/ {{ number_format($cuota['monto_aportado'], 2) }}</span>
                                        </span>
                                        @if($cuota['fecha_pago'])
                                            <span class="text-gray-500">
                                                Pagado: {{ $cuota['fecha_pago']->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">
                                        Monto sugerido: S/ {{ number_format($cuota['evento']->monto, 2) }}
                                    </p>
                                @endif

                                @if($cuota['comentario'] || $cuota['imagen'])
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        @if($cuota['comentario'])
                                            <p class="text-sm text-gray-700 whitespace-pre-line">
                                                <i class="fas fa-comment text-purple-500 mr-2"></i>{{ $cuota['comentario'] }}
                                            </p>
                                        @endif
                                        @if($cuota['imagen'])
                                            <a href="{{ asset('uploads/seccionk/'.$cuota['imagen']) }}" target="_blank" class="inline-block mt-2">
                                                <img src="{{ asset('uploads/seccionk/'.$cuota['imagen']) }}" alt="Sustento {{ $cuota['evento']->mes_cuota }}" class="h-24 w-auto rounded-lg border border-gray-200 object-cover">
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                <form action="{{ route('seccionk.perfil.cuota.comentario', [$promocion->id, $cuota['evento']->id]) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                                    @csrf
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Comentario</label>
                                    <textarea name="comentario" rows="2" maxlength="1000" placeholder="Escribe un comentario sobre esta cuota..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('comentario', $cuota['comentario']) }}</textarea>
                                    @error('comentario_'.$cuota['evento']->id)
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('comentario')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('imagen')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <label class="inline-flex items-center text-sm text-purple-700 cursor-pointer">
                                            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="sr-only" onchange="this.nextElementSibling.querySelector('.nombre-archivo').textContent = this.files[0] ? this.files[0].name : 'Cargar imagen (opcional)'">
                                            <span class="inline-flex items-center px-3 py-2 rounded-lg bg-white border border-purple-200 hover:bg-purple-50">
                                                <i class="fas fa-image mr-2"></i>
                                                <span class="nombre-archivo">Cargar imagen (opcional)</span>
                                            </span>
                                        </label>
                                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700">
                                            <i class="fas fa-save mr-2"></i>Guardar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Eventos -->
        <div>
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-star text-yellow-500 mr-3"></i>
                    Eventos
                </h2>

                <div class="space-y-4">
                    @forelse($eventosData as $evento)
                        <div class="border-2 {{ $evento['participo'] ? 'border-purple-200 bg-purple-50' : 'border-gray-200' }} rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $evento['evento']->nombre }}</h3>
                            <p class="text-sm text-gray-600 mb-3">
                                <i class="fas fa-calendar mr-2"></i>{{ $evento['evento']->fecha->format('d/m/Y') }}
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    @if($evento['participo'])
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            <span class="text-green-700 font-semibold">Participó</span>
                                        </div>
                                    @else
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-times-circle text-gray-400 mr-2"></i>
                                            <span class="text-gray-500">No participó</span>
                                        </div>
                                    @endif
                                    
                                    @if($evento['pago'])
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-money-bill text-green-500 mr-2"></i>
                                            <span class="text-green-700">S/ {{ number_format($evento['monto_aportado'], 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-8">
                            No hay eventos registrados
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
