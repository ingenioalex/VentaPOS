<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Inicio') — VentasÁgil</title>
    <meta name="description" content="VentasÁgil — punto de venta e inventario en la nube para bodegas y negocios en Perú.">
    <link rel="icon" href="{{ asset('brand/icon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('brand/icon.svg') }}">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5c56;
            --primary-light: #14b8a6;
            --accent: #f59e0b;
            --bg: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
        }
        .container { max-width: 1120px; margin: 0 auto; padding: 0 1.25rem; }
        .site-header {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
        }
        .header-inner {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: .75rem; padding: .85rem 0;
        }
        .brand { display: flex; align-items: center; gap: .6rem; text-decoration: none; }
        .brand img { height: 38px; width: auto; }
        .brand-icon { height: 36px; width: 36px; }
        nav { display: flex; align-items: center; gap: 1.25rem; }
        nav a {
            text-decoration: none; color: var(--text); font-weight: 600; font-size: .92rem;
            transition: color .2s;
        }
        nav a:hover { color: var(--primary); }
        .btn {
            display: inline-block; padding: .7rem 1.25rem; border-radius: .55rem;
            font-weight: 700; text-decoration: none; border: none; cursor: pointer;
            font-size: .95rem; transition: transform .15s, box-shadow .15s, background .15s;
        }
        .btn:active { transform: scale(.98); }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 4px 14px rgba(15,118,110,.25); }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: #fff; border: 2px solid var(--primary); color: var(--primary); }
        .btn-whatsapp { background: #25d366; color: #fff; }
        .btn-sm { padding: .5rem .9rem; font-size: .85rem; }
        footer {
            background: #0f172a; color: #94a3b8; padding: 2.5rem 0 2rem;
            margin-top: 0; font-size: .9rem;
        }
        footer a { color: #5eead4; text-decoration: none; }
        footer a:hover { text-decoration: underline; }
        .footer-grid {
            display: grid; grid-template-columns: 1fr auto; gap: 1.5rem;
            align-items: start;
        }
        .alert { padding: .75rem 1rem; border-radius: .5rem; margin: 1rem 0; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-ok { background: #d1fae5; color: #065f46; }

        @media (max-width: 720px) {
            nav .nav-link { display: none; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
        }
    </style>
    @stack('head')
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="{{ route('inicio') }}" class="brand" aria-label="VentasÁgil inicio">
            <img src="{{ asset('brand/logo.svg') }}" alt="VentasÁgil" class="brand-logo-full">
            <img src="{{ asset('brand/icon.svg') }}" alt="" class="brand-icon brand-logo-icon" style="display:none">
        </a>
        <nav>
            <a href="{{ route('precios') }}" class="nav-link">Precios</a>
            <a href="{{ route('contacto') }}" class="nav-link">Contacto</a>
            <a href="{{ route('login') }}" class="nav-link">Acceso clientes</a>
            <a href="{{ route('contacto') }}#beta" class="btn btn-primary btn-sm">Probar gratis</a>
        </nav>
    </div>
</header>
<main>
    @yield('contenido')
</main>
<footer>
    <div class="container footer-grid">
        <div>
            <p style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                <img src="{{ asset('brand/icon.svg') }}" alt="" width="28" height="28">
                <strong style="color:#e2e8f0">VentasÁgil</strong>
            </p>
            <p>Punto de venta e inventario en la nube — Chiclayo y todo el Perú.</p>
            <p style="margin-top:.75rem;font-size:.85rem">&copy; {{ date('Y') }} VentasÁgil</p>
        </div>
        <div style="text-align:right">
            <p><a href="{{ route('precios') }}">Planes</a></p>
            <p style="margin-top:.35rem"><a href="{{ route('contacto') }}">Contacto</a></p>
            <p style="margin-top:.35rem"><a href="https://wa.me/{{ config('ventapos.whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a></p>
        </div>
    </div>
</footer>
<style>
@media (max-width: 480px) {
    .brand-logo-full { display: none; }
    .brand-logo-icon { display: block !important; }
}
</style>
@stack('scripts')
</body>
</html>
