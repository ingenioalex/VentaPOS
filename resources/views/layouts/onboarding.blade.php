<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Configuración') — VentaPOS</title>
    <style>
        :root { --primary: #0f766e; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: linear-gradient(160deg, #f0fdfa 0%, #f1f5f9 45%); color: #1e293b; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: .85rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .topbar h2 { font-size: 1.05rem; color: var(--primary); }
        .topbar span { font-size: .85rem; color: #64748b; }
        main { padding: 2rem 1.5rem; }
        .card { background: #fff; border-radius: .75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .btn { display: inline-block; padding: .55rem 1rem; border-radius: .5rem; border: none; cursor: pointer; font-weight: 600; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .alert-ok { background: #d1fae5; color: #065f46; padding: .75rem; border-radius: .5rem; margin-bottom: 1rem; max-width: 720px; margin-left: auto; margin-right: auto; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: .75rem; border-radius: .5rem; margin-bottom: 1rem; max-width: 720px; margin-left: auto; margin-right: auto; }
    </style>
    @stack('head')
</head>
<body>
@php $u = auth()->user(); @endphp
<header class="topbar">
    <h2>VentaPOS</h2>
    <span>{{ $u->empresa?->nombre }} · Configuración inicial</span>
</header>
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
@stack('scripts')
</body>
</html>
