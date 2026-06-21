<nav style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem">

    <a href="{{ route('inventario.index') }}" class="btn {{ request()->routeIs('inventario.index') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('inventario.index') ? '' : 'background:#e2e8f0;color:#334155' }}">Movimientos</a>

    <a href="{{ route('inventario.stock') }}" class="btn {{ request()->routeIs('inventario.stock') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('inventario.stock') ? '' : 'background:#e2e8f0;color:#334155' }}">Stock actual</a>

    @if(auth()->user()->empresa?->tieneExtra('lotes_vencimiento'))

    <a href="{{ route('inventario.vencimientos') }}" class="btn {{ request()->routeIs('inventario.vencimientos') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('inventario.vencimientos') ? '' : 'background:#fef3c7;color:#92400e' }}">Vencimientos</a>

    @endif

    <a href="{{ route('inventario.entrada') }}" class="btn" style="background:#2563eb;color:#fff">+ Entrada</a>

    <a href="{{ route('inventario.salida') }}" class="btn" style="background:#dc2626;color:#fff">− Salida</a>

    <a href="{{ route('inventario.ajuste') }}" class="btn btn-warning">Ajuste</a>

</nav>

