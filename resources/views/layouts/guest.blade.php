<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'VentaPOS') — Punto de venta e inventario</title>
    <meta name="description" content="Sistema de ventas e inventario para bodegas y negocios en Perú.">
    <style>
        :root { --primary: #0f766e; --primary-dark: #0d5c56; --accent: #f59e0b; --bg: #f8fafc; --text: #1e293b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; color: var(--text); background: var(--bg); line-height: 1.6; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 1.25rem; }
        header { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 1rem 0; }
        .header-inner { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .logo { font-size: 1.4rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { color: var(--accent); }
        nav a { margin-left: 1.25rem; text-decoration: none; color: var(--text); font-weight: 500; }
        nav a:hover { color: var(--primary); }
        .btn { display: inline-block; padding: .7rem 1.25rem; border-radius: .5rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; font-size: 1rem; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: transparent; border: 2px solid var(--primary); color: var(--primary); }
        .btn-whatsapp { background: #25d366; color: #fff; }
        footer { background: #1e293b; color: #94a3b8; padding: 2rem 0; text-align: center; margin-top: 3rem; font-size: .9rem; }
        footer a { color: #5eead4; }
        .alert { padding: .75rem 1rem; border-radius: .5rem; margin: 1rem 0; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-ok { background: #d1fae5; color: #065f46; }
    </style>
    @stack('head')
</head>
<body>
<header>
    <div class="container header-inner">
        <a href="{{ route('inicio') }}" class="logo">Venta<span>POS</span></a>
        <nav>
            <a href="{{ route('precios') }}">Precios</a>
            <a href="{{ route('contacto') }}">Contacto</a>
            <a href="{{ route('login') }}">Acceso</a>
        </nav>
        <a href="{{ route('contacto') }}#beta" class="btn btn-primary">Probar gratis</a>
    </div>
</header>
<main>
    @yield('contenido')
</main>
<footer>
    <div class="container">
        <p>&copy; {{ date('Y') }} VentaPOS — Chiclayo, Perú</p>
        <p style="margin-top:.5rem"><a href="{{ route('login') }}">Acceso clientes</a></p>
    </div>
</footer>
@stack('scripts')
</body>
</html>
