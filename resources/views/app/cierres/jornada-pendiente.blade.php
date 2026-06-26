@extends('layouts.app')

@section('titulo', 'Día sin cerrar')

@section('contenido')
@php $r = $resumen; @endphp
<div class="cierres-page">
    <p style="margin-bottom:.75rem">
        <a href="{{ route('cierres.index') }}#jornadas-pendientes">← Volver a días sin cerrar</a>
    </p>

    <h1 style="margin-bottom:.35rem">{{ $r['etiqueta'] }}</h1>

    @if(session('ok'))
    <p style="background:#ecfdf5;color:#065f46;padding:.75rem;border-radius:.5rem;margin-bottom:1rem">{{ session('ok') }}</p>
    @endif
    @error('jornada_directa')<p style="color:#b91c1c;margin-bottom:.75rem">{{ $message }}</p>@enderror
    @error('cierre_z')<p style="color:#b91c1c;margin-bottom:.75rem">{{ $message }}</p>@enderror

    @if($r['cerrado'])
    <div class="card" style="background:#ecfdf5;border-color:#6ee7b7;color:#065f46">
        Este día ya está cerrado con Cierre Z.
    </div>
    @else
    <div class="card" style="margin-bottom:1rem">
        <p style="font-size:.9rem;color:#64748b;margin:0 0 .75rem">
            {{ $r['ventas_dia_calendario']['cantidad_ventas'] }} ventas
            · S/ {{ number_format($r['ventas_dia_calendario']['total_ventas'], 2) }}
            · {{ $r['fecha_desde'] }} — {{ $r['fecha_hasta'] }}
        </p>
        @include('app.cierres._resumen-dual', [
            'titulo' => 'Resumen del día',
            'subtitulo' => $r['fecha'],
            'tickets' => $r['totales_comprobantes']['cantidad_ventas'],
            'efectivo' => $r['totales_comprobantes']['total_efectivo'],
            'tarjeta' => $r['totales_comprobantes']['total_tarjeta'],
            'otros' => $r['totales_comprobantes']['total_otros'],
            'efectivoSistema' => $r['efectivo_sistema_arqueo'] ?? $r['totales_x']['total_efectivo'],
            'efectivoContado' => $r['total_contado'],
            'diferencia' => $r['total_diferencia'],
        ])
    </div>

    @if(! $esSiguiente)
    <p style="background:#fff7ed;border:1px solid #fdba74;color:#92400e;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;font-size:.85rem">
        Primero debe cerrar el día más antiguo pendiente. Vuelva al listado y use el día marcado como <strong>Siguiente</strong>.
    </p>
    @endif

    <form method="post" action="{{ route('cierres.jornada.directo', $r['fecha']) }}"
        onsubmit="return confirm('¿Cerrar el día {{ $r['etiqueta'] }}?')">
        @csrf
        <button type="submit" class="btn btn-danger" style="width:100%;padding:.65rem"
            @if(! $esSiguiente) disabled @endif>
            Cerrar este día (X + Z)
        </button>
    </form>
    <p style="font-size:.78rem;color:#64748b;margin-top:.5rem;text-align:center">
        Registra los Cierre X con efectivo del sistema y el Cierre Z del día.
    </p>
    @endif
</div>
@endsection
