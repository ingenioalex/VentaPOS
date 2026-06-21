<nav style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem">
    @if(auth()->user()->tieneModulo('compras'))
    <a href="{{ route('compras.index') }}" class="btn {{ request()->routeIs('compras.*') && !request()->routeIs('proveedores.*') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('compras.*') && !request()->routeIs('proveedores.*') ? '' : 'background:#e2e8f0;color:#334155' }}">Compras</a>
    <a href="{{ route('compras.crear') }}" class="btn" style="background:#2563eb;color:#fff">+ Nueva compra</a>
    @endif
    @if(auth()->user()->tieneModulo('proveedores'))
    <a href="{{ route('proveedores.index') }}" class="btn {{ request()->routeIs('proveedores.*') ? 'btn-primary' : '' }}" style="{{ request()->routeIs('proveedores.*') ? '' : 'background:#e2e8f0;color:#334155' }}">Proveedores</a>
    @endif
</nav>
