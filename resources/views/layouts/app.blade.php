<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel') — VentaPOS</title>
    <style>
        :root { --primary: #0f766e; --sidebar: #1e293b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; color: #1e293b; }
        .layout { display: flex; min-height: 100vh; }
        aside { width: 220px; background: var(--sidebar); color: #e2e8f0; padding: 1.25rem; flex-shrink: 0; }
        aside h2 { font-size: 1.1rem; margin-bottom: .5rem; color: #5eead4; }
        aside a { display: block; color: #cbd5e1; text-decoration: none; padding: .55rem 0; font-size: .9rem; }
        aside a:hover, aside a.activo { color: #fff; }
        aside .seccion { font-size: .7rem; text-transform: uppercase; color: #64748b; margin: 1rem 0 .35rem; letter-spacing: .05em; }
        aside .rol-badge { font-size: .72rem; background: #334155; padding: .2rem .5rem; border-radius: .3rem; display: inline-block; margin-bottom: 1rem; }
        main { flex: 1; padding: 1.5rem; }
        .card { background: #fff; border-radius: .75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 1rem; }
        .btn { display: inline-block; padding: .55rem 1rem; border-radius: .5rem; border: none; cursor: pointer; font-weight: 600; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-warning { background: #d97706; color: #fff; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; }
        .stat { background: #fff; padding: 1rem; border-radius: .75rem; border-left: 4px solid var(--primary); }
        .stat strong { display: block; font-size: 1.5rem; }
        .alert-ok { background: #d1fae5; color: #065f46; padding: .75rem; border-radius: .5rem; margin-bottom: 1rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: .75rem; border-radius: .5rem; margin-bottom: 1rem; }
        .alert-warning { background: #fef3c7; color: #92400e; padding: .75rem; border-radius: .5rem; margin-bottom: 1rem; }
        .nav-badge { display: inline-block; min-width: 1.1rem; padding: 0 .35rem; margin-left: .25rem; background: #d97706; color: #fff; font-size: .68rem; font-weight: 700; border-radius: 999px; text-align: center; vertical-align: middle; }
        table.data { width: 100%; border-collapse: collapse; font-size: .9rem; }
        table.data th, table.data td { padding: .55rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .filtros { display: flex; flex-wrap: wrap; gap: .5rem; align-items: end; margin-bottom: 1rem; }
        .filtros label { font-size: .8rem; font-weight: 600; display: block; margin-bottom: .2rem; }
        .filtros input, .filtros select { padding: .45rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
        .cpe-badge {
            display: inline-block; font-size: .72rem; font-weight: 700; letter-spacing: .02em;
            padding: .2rem .45rem; border-radius: .35rem; white-space: nowrap; font-family: ui-monospace, monospace;
        }
        .cpe-sim { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .cpe-pro { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .cpe-pnd { background: #e0e7ff; color: #3730a3; border: 1px solid #a5b4fc; }
        .cpe-err { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .cpe-no { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .cpe-leyenda { font-size: .8rem; color: #64748b; display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: center; }
        .cpe-leyenda span { display: inline-flex; align-items: center; gap: .35rem; }
    </style>
    @stack('head')
</head>
<body>
@php $u = auth()->user(); @endphp
<div class="layout">
    <aside>
        <h2>VentaPOS</h2>
        <p style="font-size:.85rem">{{ $u->nombre }}</p>
        <span class="rol-badge">{{ $u->etiquetaRol() }}</span>
        @if($u->empresa)
        <p style="font-size:.72rem;color:#94a3b8;margin-bottom:.5rem">{{ $u->empresa->nombre }}</p>
        @endif
        @if($u->rol === 'cajero' && $u->en_sesion)
        <span class="rol-badge" style="background:#1d4ed8;margin-bottom:.5rem">● Sesión activa</span>
        @endif

        @if($u->tieneModulo('inicio'))
        <a href="{{ route('app.inicio') }}" class="{{ request()->routeIs('app.inicio') ? 'activo' : '' }}">Inicio</a>
        @endif
        @if($u->tieneModulo('pos'))
        <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.index') ? 'activo' : '' }}">POS Venta</a>
        @endif
        @if($u->tieneModulo('pos_movil'))
        <a href="{{ route('pos.movil') }}" class="{{ request()->routeIs('pos.movil') ? 'activo' : '' }}">POS Móvil</a>
        @endif

        @if($u->tieneModulo('ventas') || $u->tieneModulo('cierres'))
        <div class="seccion">Operación</div>
        @endif
        @if($u->tieneModulo('ventas'))
        <a href="{{ route('ventas.index') }}" class="{{ request()->routeIs('ventas.*') ? 'activo' : '' }}">Ventas</a>
        @endif
        @if($u->tieneModulo('cierres'))
        <a href="{{ route('cierres.index') }}" class="{{ request()->routeIs('cierres.*') ? 'activo' : '' }}">Cierre de caja</a>
        @endif

        @if($u->tieneModulo('productos') || $u->tieneModulo('categorias') || $u->tieneModulo('clientes'))
        <div class="seccion">Catálogo</div>
        @endif
        @if($u->tieneModulo('productos'))
        <a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.*') && !request()->routeIs('flejes.*') ? 'activo' : '' }}">
            Productos
            @if(($stockBajoNav ?? 0) > 0)
            <span class="nav-badge" title="Productos con stock bajo">{{ $stockBajoNav }}</span>
            @endif
        </a>
        @if($u->empresa?->tieneExtra('flejes'))
        <a href="{{ route('flejes.index') }}" class="{{ request()->routeIs('flejes.*') ? 'activo' : '' }}">Flejes / etiquetas</a>
        @endif
        @endif
        @if($u->tieneModulo('inventario'))
        <a href="{{ route('inventario.index') }}" class="{{ request()->routeIs('inventario.*') ? 'activo' : '' }}">
            Inventario
            @if(($vencimientosNav ?? 0) > 0)
            <span class="nav-badge" title="Lotes por vencer (30 días)" style="background:#dc2626">{{ $vencimientosNav }}</span>
            @endif
        </a>
        @endif
        @if($u->tieneModulo('compras') || $u->tieneModulo('proveedores'))
        <div class="seccion">Compras</div>
        @endif
        @if($u->tieneModulo('compras'))
        <a href="{{ route('compras.index') }}" class="{{ request()->routeIs('compras.*') ? 'activo' : '' }}">Compras</a>
        @endif
        @if($u->tieneModulo('proveedores'))
        <a href="{{ route('proveedores.index') }}" class="{{ request()->routeIs('proveedores.*') ? 'activo' : '' }}">Proveedores</a>
        @endif
        @if($u->tieneModulo('categorias'))
        <a href="{{ route('categorias.index') }}" class="{{ request()->routeIs('categorias.*') ? 'activo' : '' }}">Categorías</a>
        @endif
        @if($u->tieneModulo('clientes'))
        <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'activo' : '' }}">Clientes</a>
        @endif

        @if($u->tieneModulo('reportes'))
        <div class="seccion">Gestión</div>
        <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'activo' : '' }}">Reportes</a>
        @endif

        @if($u->tieneModulo('usuarios') || $u->tieneModulo('permisos'))
        <div class="seccion">Administración</div>
        @endif
        @if($u->tieneModulo('usuarios'))
        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">Usuarios</a>
        @endif
        @if($u->tieneModulo('permisos'))
        <a href="{{ route('permisos.index') }}" class="{{ request()->routeIs('permisos.*') ? 'activo' : '' }}">Permisos por rol</a>
        @endif
        @if($u->tieneModulo('configuracion'))
        <a href="{{ route('configuracion.index') }}" class="{{ request()->routeIs('configuracion.*') ? 'activo' : '' }}">Configuración</a>
        @endif
        @if($u->tieneModulo('sunat'))
        <a href="{{ route('sunat.index') }}" class="{{ request()->routeIs('sunat.*') ? 'activo' : '' }}">SUNAT</a>
        @endif
        @if($u->tieneModulo('terminales'))
        <a href="{{ route('terminales.index') }}" class="{{ request()->routeIs('terminales.*') ? 'activo' : '' }}">Terminales</a>
        @endif
        @if($u->tieneModulo('usuario_terminales'))
        <a href="{{ route('usuario-terminales.index') }}" class="{{ request()->routeIs('usuario-terminales.*') ? 'activo' : '' }}">Usuarios y terminales</a>
        @endif

        @php
            $bloquearSalir = $u->rol === 'cajero' && $u->en_sesion && ! session('cierre_x_completado');
        @endphp
        <form method="post" action="{{ route('logout') }}" style="margin-top:2rem"
              onsubmit="{{ $bloquearSalir ? 'return false' : '' }}">
            @csrf
            <button type="submit" class="btn" style="background:{{ $bloquearSalir ? '#cbd5e1' : '#475569' }};color:{{ $bloquearSalir ? '#64748b' : '#fff' }};width:100%;{{ $bloquearSalir ? 'cursor:not-allowed' : '' }}"
                {{ $bloquearSalir ? 'disabled title=El supervisor debe cerrar su sesión en Cierre de caja' : '' }}>Salir</button>
        </form>
        @if($bloquearSalir)
        <p style="font-size:.75rem;color:#94a3b8;margin-top:.35rem">El supervisor realizará el Cierre X y cerrará su sesión.</p>
        @endif
        <a href="{{ route('guia.index') }}" style="display:block;margin-top:1rem;font-size:.82rem;color:#94a3b8">Guía rápida</a>
    </aside>
    <main>
        @if(session('ok'))
            <div class="alert-ok">{{ session('ok') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
            </div>
        @endif
        @yield('contenido')
    </main>
</div>
@stack('scripts')
</body>
</html>
