@php
    $soloReferencia = $evento->esSoloReferencia();
    $activado = $evento->fechaYaPaso() && $evento->permiteEditar();
@endphp
@if($soloReferencia || $activado)
    <div class="mt-2 rounded-lg px-3 py-2 {{ $soloReferencia ? 'bg-slate-50 border border-slate-200' : 'bg-amber-50 border border-amber-200' }}">
        @if($soloReferencia)
            <p class="text-xs font-semibold text-slate-700">Solo referencia · evento ya realizado</p>
            <p class="text-xs text-slate-500">La fecha {{ $evento->fecha->format('d/m/Y') }} ya pasó. Se muestra el registro y no se modifica.</p>
        @else
            <p class="text-xs font-semibold text-amber-800">Activado para modificar</p>
            <p class="text-xs text-amber-700">Este evento ya pasó. Recuerda volverlo a referencia al terminar.</p>
        @endif
        @if(!empty($admin))
            <form method="POST" action="{{ route('seccionk.admin.eventos.edicion', $evento) }}" class="mt-2">
                @csrf
                <input type="hidden" name="abierto" value="{{ $soloReferencia ? 1 : 0 }}">
                @if(!empty($volver))
                    <input type="hidden" name="volver" value="{{ $volver }}">
                @endif
                @if(!empty($promocion))
                    <input type="hidden" name="promocion" value="{{ $promocion->getKey() }}">
                @endif
                <button type="submit" class="text-xs font-semibold {{ $soloReferencia ? 'text-purple-700 hover:text-purple-900' : 'text-slate-600 hover:text-slate-800' }}">
                    {{ $soloReferencia ? 'Activar para modificar' : 'Volver a referencia' }}
                </button>
            </form>
        @endif
    </div>
@endif
