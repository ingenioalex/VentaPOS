<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Admin') — VentaPOS</title>
    <style>
        :root { --primary: #7c3aed; --sidebar: #1e1b4b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #1e293b; }
        .layout { display: flex; min-height: 100vh; }
        aside { width: 230px; background: var(--sidebar); color: #e2e8f0; padding: 1.25rem; flex-shrink: 0; }
        aside h2 { font-size: 1.1rem; margin-bottom: .5rem; color: #c4b5fd; }
        aside a { display: block; color: #cbd5e1; text-decoration: none; padding: .55rem 0; font-size: .9rem; }
        aside a:hover, aside a.activo { color: #fff; }
        aside .badge { font-size: .72rem; background: #4c1d95; padding: .2rem .5rem; border-radius: .3rem; display: inline-block; margin-bottom: 1rem; }
        main { flex: 1; padding: 1.5rem; }
        .card { background: #fff; border-radius: .75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 1rem; }
        .btn { display: inline-block; padding: .55rem 1rem; border-radius: .5rem; border: none; cursor: pointer; font-weight: 600; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .alert-ok { background: #d1fae5; color: #065f46; padding: .75rem; border-radius: .5rem; margin-bottom: 1rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: .75rem; border-radius: .5rem; margin-bottom: 1rem; }
        table.data { width: 100%; border-collapse: collapse; font-size: .9rem; }
        table.data th, table.data td { padding: .55rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .filtros { display: flex; flex-wrap: wrap; gap: .5rem; align-items: end; margin-bottom: 1rem; }
        .filtros label { font-size: .8rem; font-weight: 600; display: block; margin-bottom: .2rem; }
        .filtros input, .filtros select { padding: .45rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
        .stats { display:flex; flex-wrap:wrap; gap:1rem; }
        .stats .card strong { color:#1e293b; }
    </style>
    @stack('head')
</head>
<body>
@php $u = auth()->user(); @endphp
<div class="layout">
    <aside>
        <h2>VentaPOS Admin</h2>
        <p style="font-size:.85rem">{{ $u->nombre }}</p>
        <span class="badge">Super Admin</span>

        <div style="font-size:.7rem;text-transform:uppercase;color:#64748b;margin:1.25rem 0 .35rem;letter-spacing:.05em">Operación</div>
        <a href="{{ route('admin.empresas.index') }}" class="{{ request()->routeIs('admin.empresas.*') && !request()->routeIs('admin.contratacion.*') ? 'activo' : '' }}">Empresas</a>

        <div style="font-size:.7rem;text-transform:uppercase;color:#64748b;margin:1rem 0 .35rem;letter-spacing:.05em">Comercial</div>
        <a href="{{ route('admin.contratacion.index') }}" class="{{ request()->routeIs('admin.contratacion.*') ? 'activo' : '' }}">Contratación</a>
        <a href="{{ route('admin.tarifas.index') }}" class="{{ request()->routeIs('admin.tarifas.*') ? 'activo' : '' }}">Tarifas y precios</a>

        <div style="font-size:.7rem;text-transform:uppercase;color:#64748b;margin:1rem 0 .35rem;letter-spacing:.05em">Sistema</div>
        <a href="{{ route('admin.super-usuarios.index') }}" class="{{ request()->routeIs('admin.super-usuarios.*') ? 'activo' : '' }}">Super usuarios</a>
        <a href="{{ route('admin.backups.index') }}" class="{{ request()->routeIs('admin.backups.*') ? 'activo' : '' }}">Backups</a>

        <form method="post" action="{{ route('admin.logout') }}" style="margin-top:2rem">
            @csrf
            <button type="submit" class="btn" style="background:#475569;color:#fff;width:100%">Salir</button>
        </form>
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
