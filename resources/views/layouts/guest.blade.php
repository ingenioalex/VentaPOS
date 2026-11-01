<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoCfg = $seoConfig ?? [];
        $seoTitle = $seoCfg['title'] ?? trim(strip_tags((string) $__env->yieldContent('titulo', 'Inicio')));
        $seoDescription = $seoCfg['description'] ?? config('seo.default_description');
        $seoPath = $seoCfg['path'] ?? request()->path();
        $seoPath = $seoPath === '' || $seoPath === '/' ? '/' : '/'.ltrim((string) $seoPath, '/');
    @endphp
    <title>{{ $seoTitle }} — {{ config('seo.site_name', 'VentasÁgil') }}</title>
    @include('partials.seo-head', [
        'seoTitle' => $seoTitle,
        'seoDescription' => $seoDescription,
        'seoPath' => $seoPath,
        'jsonLd' => $jsonLd ?? null,
    ])
    @include('partials.brand-icons')
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
            gap: .75rem; padding: .85rem 0;
        }
        .brand { display: flex; align-items: center; gap: .6rem; text-decoration: none; flex-shrink: 0; }
        .brand img { height: 38px; width: auto; }
        .brand-icon { height: 36px; width: 36px; }
        nav.nav-desktop { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; justify-content: flex-end; }
        nav a {
            text-decoration: none; color: var(--text); font-weight: 600; font-size: .92rem;
            transition: color .2s;
        }
        nav a:hover { color: var(--primary); }
        .btn-menu-landing {
            display: none;
            width: 42px; height: 42px;
            border: 1px solid #e2e8f0; border-radius: .5rem;
            background: #fff; color: var(--text);
            font-size: 1.25rem; cursor: pointer; flex-shrink: 0;
        }
        .nav-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,.45); z-index: 150;
        }
        .nav-mobile-panel {
            display: none; position: fixed; top: 0; right: 0; bottom: 0;
            width: min(300px, 88vw); z-index: 160;
            background: #fff; padding: 1.25rem;
            box-shadow: -4px 0 24px rgba(0,0,0,.12);
            transform: translateX(105%);
            transition: transform .25s ease;
            overflow-y: auto;
        }
        body.nav-open .nav-backdrop { display: block; }
        body.nav-open .nav-mobile-panel { display: block; transform: translateX(0); }
        body.nav-open { overflow: hidden; }
        .nav-mobile-panel a {
            display: block; padding: .75rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 1rem;
        }
        .nav-mobile-panel .btn { display: block; text-align: center; margin-top: 1rem; width: 100%; }
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
            .btn-menu-landing { display: block; }
            nav.nav-desktop { display: none; }
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
        <button type="button" class="btn-menu-landing" id="btn-nav-landing" aria-label="Abrir menú" aria-expanded="false">☰</button>
        <nav class="nav-desktop" aria-label="Navegación principal">
            <a href="{{ route('precios') }}">Precios</a>
            <a href="{{ route('faq') }}">Preguntas</a>
            <a href="{{ route('contacto') }}">Contacto</a>
            <a href="{{ route('login') }}">Acceso</a>
            <a href="{{ route('contacto') }}#beta" class="btn btn-primary btn-sm">Probar gratis</a>
        </nav>
    </div>
</header>
<div class="nav-backdrop" id="nav-backdrop" aria-hidden="true"></div>
<nav class="nav-mobile-panel" id="nav-mobile-panel" aria-label="Menú móvil">
    <p style="font-weight:700;margin-bottom:.5rem;color:var(--primary)">VentasÁgil</p>
    <a href="{{ route('precios') }}">Precios</a>
    <a href="{{ route('faq') }}">Preguntas</a>
    <a href="{{ route('contacto') }}">Contacto</a>
    <a href="{{ route('login') }}">Acceso</a>
    <a href="{{ route('contacto') }}#beta" class="btn btn-primary">Probar gratis</a>
</nav>
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
            <p style="margin-top:.35rem"><a href="{{ route('faq') }}">Preguntas frecuentes</a></p>
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
<script>
(function () {
    const btn = document.getElementById('btn-nav-landing');
    const backdrop = document.getElementById('nav-backdrop');
    const panel = document.getElementById('nav-mobile-panel');
    if (!btn || !backdrop || !panel) return;
    function setOpen(open) {
        document.body.classList.toggle('nav-open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    btn.addEventListener('click', () => setOpen(!document.body.classList.contains('nav-open')));
    backdrop.addEventListener('click', () => setOpen(false));
    panel.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setOpen(false)));
    window.addEventListener('keydown', e => { if (e.key === 'Escape') setOpen(false); });
})();
</script>

@include('partials.chatbox')
@stack('scripts')
</body>
</html>
