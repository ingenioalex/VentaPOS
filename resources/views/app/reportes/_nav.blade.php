<nav style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem">
    <a href="{{ route('reportes.index', request()->only(['desde','hasta'])) }}" class="btn {{ request()->routeIs('reportes.index') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.index') ? '' : 'background:#e2e8f0;color:#334155' }}">Ventas</a>
    @if(auth()->user()->tieneModulo('inventario'))
    <a href="{{ route('reportes.inventario', request()->only(['desde','hasta','q','stock_bajo'])) }}" class="btn {{ request()->routeIs('reportes.inventario') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.inventario') ? '' : 'background:#e2e8f0;color:#334155' }}">Inventario</a>
    @endif
    @if(auth()->user()->tieneModulo('compras'))
    <a href="{{ route('reportes.compras', request()->only(['desde','hasta','estado'])) }}" class="btn {{ request()->routeIs('reportes.compras') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.compras') ? '' : 'background:#e2e8f0;color:#334155' }}">Compras</a>
    @endif
</nav>
