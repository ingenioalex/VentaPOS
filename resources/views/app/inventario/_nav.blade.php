@once('inventario-styles')
@include('app.inventario._styles')
@endonce
<nav class="inv-nav" aria-label="Inventario">
    @include('app.inventario._leyenda-acciones')
    <div class="inv-nav-row">
        <a href="{{ route('inventario.index') }}"
           class="btn-tab {{ request()->routeIs('inventario.index') ? 'btn-tab--active' : 'btn-tab--muted' }}">Movimientos</a>
        <a href="{{ route('inventario.stock') }}"
           class="btn-tab {{ request()->routeIs('inventario.stock') ? 'btn-tab--active' : 'btn-tab--muted' }}">Stock actual</a>
        @if(auth()->user()->empresa?->tieneExtra('lotes_vencimiento'))
        <a href="{{ route('inventario.vencimientos') }}"
           class="btn-tab {{ request()->routeIs('inventario.vencimientos') ? 'btn-tab--warn' : 'btn-tab--muted' }}">Vencimientos</a>
        @endif
        <span class="inv-nav-spacer"></span>
        @include('app.inventario._acciones-movimiento', ['simbolos' => true])
    </div>
</nav>
