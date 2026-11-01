<nav style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem">
    <a href="{{ route('reportes.index', request()->only(['desde','hasta'])) }}" class="btn {{ request()->routeIs('reportes.index') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.index') ? '' : 'background:#e2e8f0;color:#334155' }}">R. Ventas</a>
    @if(auth()->user()->tieneModulo('inventario'))
    <a href="{{ route('reportes.inventario', request()->only(['desde','hasta','q','stock_bajo'])) }}" class="btn {{ request()->routeIs('reportes.inventario') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.inventario') ? '' : 'background:#e2e8f0;color:#334155' }}">R. Inventario</a>
    @endif
    @if(auth()->user()->tieneModulo('compras'))
    <a href="{{ route('reportes.compras', request()->only(['desde','hasta','estado'])) }}" class="btn {{ request()->routeIs('reportes.compras') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.compras') ? '' : 'background:#e2e8f0;color:#334155' }}">R. Compras</a>
    @endif
    @if(auth()->user()->tieneModulo('cierres'))
    <a href="{{ route('reportes.cierres', request()->only(['desde','hasta','tipo'])) }}" class="btn {{ request()->routeIs('reportes.cierres') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.cierres') ? '' : 'background:#e2e8f0;color:#334155' }}">R. Cierres</a>
    @endif
    <a href="{{ route('reportes.productos', request()->only(['desde','hasta','limite'])) }}" class="btn {{ request()->routeIs('reportes.productos') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('reportes.productos') ? '' : 'background:#e2e8f0;color:#334155' }}">R. Productos</a>
</nav>
