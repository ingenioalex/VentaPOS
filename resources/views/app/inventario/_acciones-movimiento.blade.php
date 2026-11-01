@php
    $productoId = $productoId ?? null;
    $query = $productoId ? ['producto_id' => $productoId] : [];
    $simbolos = $simbolos ?? ($compacto ?? false);
    $bloque = $bloque ?? false;
    $mostrarKardex = $mostrarKardex ?? false;
@endphp
<div class="inv-acciones {{ $bloque ? 'inv-acciones--block' : '' }} {{ $simbolos ? 'inv-acciones--simbolos' : '' }}" aria-label="Acciones de inventario">
    <a href="{{ route('inventario.entrada', $query) }}" class="inv-btn inv-btn--entrada{{ $simbolos ? ' inv-btn--icon' : '' }}" title="Entrada" aria-label="Entrada">{{ $simbolos ? '+' : 'Entrada' }}</a>
    <a href="{{ route('inventario.salida', $query) }}" class="inv-btn inv-btn--salida{{ $simbolos ? ' inv-btn--icon' : '' }}" title="Salida" aria-label="Salida">{{ $simbolos ? '−' : 'Salida' }}</a>
    <a href="{{ route('inventario.ajuste', $query) }}" class="inv-btn inv-btn--ajuste{{ $simbolos ? ' inv-btn--icon' : '' }}" title="Ajuste" aria-label="Ajuste">{{ $simbolos ? 'A' : 'Ajuste' }}</a>
    @if($mostrarKardex && $productoId)
    <a href="{{ route('inventario.kardex', $productoId) }}" class="inv-btn inv-btn--kardex inv-btn--icon" title="Kardex" aria-label="Kardex">K</a>
    @endif
</div>
