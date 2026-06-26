@extends('layouts.guest')

@section('titulo', 'Punto de venta en la nube')

@push('head')
<style>
    .hero {
        position: relative; overflow: hidden;
        padding: 4.5rem 0 4rem;
        background: linear-gradient(160deg, #ecfdf5 0%, #f8fafc 45%, #fff 100%);
    }
    .hero-blob {
        position: absolute; border-radius: 50%; filter: blur(60px); opacity: .45;
        animation: float 8s ease-in-out infinite;
    }
    .hero-blob.a { width: 320px; height: 320px; background: #5eead4; top: -80px; right: -60px; }
    .hero-blob.b { width: 260px; height: 260px; background: #fcd34d; bottom: -40px; left: -40px; animation-delay: -3s; }
    @keyframes float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(12px, -18px) scale(1.05); }
    }
    .hero-inner { position: relative; z-index: 1; text-align: center; max-width: 720px; margin: 0 auto; }
    .hero-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #fff; border: 1px solid #99f6e4; color: #0f766e;
        padding: .4rem 1rem; border-radius: 999px; font-size: .82rem; font-weight: 700;
        margin-bottom: 1.25rem; box-shadow: 0 2px 12px rgba(15,118,110,.1);
    }
    .hero h1 {
        font-size: clamp(2rem, 5vw, 3rem); line-height: 1.15;
        margin-bottom: 1rem; letter-spacing: -.02em;
    }
    .hero h1 em { font-style: normal; color: var(--primary); }
    .hero-lead { font-size: 1.12rem; color: var(--muted); margin-bottom: 2rem; }
    .hero-cta { display: flex; gap: .85rem; justify-content: center; flex-wrap: wrap; }
    .hero-stats {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;
        max-width: 560px; margin: 2.5rem auto 0;
    }
    .hero-stat {
        background: rgba(255,255,255,.85); border: 1px solid #e2e8f0;
        border-radius: .75rem; padding: 1rem .75rem;
    }
    .hero-stat strong { display: block; font-size: 1.5rem; color: var(--primary); }
    .hero-stat span { font-size: .78rem; color: var(--muted); }

    .porque { padding: 4rem 0 5rem; background: #fff; }
    .porque-header { text-align: center; max-width: 640px; margin: 0 auto 3rem; }
    .porque-header h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); margin-bottom: .75rem; }
    .porque-header p { color: var(--muted); font-size: 1.05rem; }
    .reveal {
        opacity: 0; transform: translateY(24px);
        transition: opacity .6s ease, transform .6s ease;
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    .razon {
        display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem;
        align-items: center; margin-bottom: 4rem; opacity: 0;
        transform: translateY(32px);
        transition: opacity .7s ease, transform .7s ease;
    }
    .razon.visible { opacity: 1; transform: translateY(0); }
    .razon:nth-child(even) .razon-visual { order: 2; }
    .razon:nth-child(even) .razon-text { order: 1; }
    .razon-icon {
        width: 72px; height: 72px; border-radius: 1rem;
        background: linear-gradient(135deg, #ccfbf1, #99f6e4);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; margin-bottom: 1rem;
        box-shadow: 0 8px 24px rgba(15,118,110,.15);
    }
    .razon-text h3 { font-size: 1.45rem; margin-bottom: .5rem; }
    .razon-text .resumen { color: var(--primary); font-weight: 600; margin-bottom: .75rem; }
    .razon-text p { color: var(--muted); margin-bottom: 1rem; }
    .razon-text ul { list-style: none; }
    .razon-text li {
        padding: .4rem 0 .4rem 1.5rem; position: relative; font-size: .95rem;
    }
    .razon-text li::before {
        content: '✓'; position: absolute; left: 0; color: var(--primary); font-weight: 800;
    }
    .razon-visual {
        background: linear-gradient(145deg, #f0fdfa, #fff);
        border: 1px solid #ccfbf1; border-radius: 1.25rem;
        padding: 2rem; min-height: 200px;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 20px 40px rgba(15,118,110,.08);
        transition: transform .4s ease;
    }
    .razon.visible .razon-visual { animation: pop .6s ease .2s both; }
    @keyframes pop {
        from { transform: scale(.92); opacity: .5; }
        to { transform: scale(1); opacity: 1; }
    }
    .razon-visual span { font-size: 4.5rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,.08)); }

    .cta-final {
        text-align: center; padding: 3.5rem 1.5rem;
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        color: #fff; border-radius: 1.25rem; margin: 0 1.25rem 3rem;
        max-width: 900px; margin-left: auto; margin-right: auto;
    }
    .cta-final h2 { font-size: 1.75rem; margin-bottom: .75rem; }
    .cta-final p { opacity: .92; margin-bottom: 1.5rem; max-width: 480px; margin-left: auto; margin-right: auto; }
    .cta-final .btn { background: #fff; color: var(--primary); }
    .cta-final .btn-outline { background: transparent; border-color: #fff; color: #fff; }

    @media (max-width: 768px) {
        .hero { padding: 3rem 0 2.5rem; }
        .hero-stats { grid-template-columns: 1fr; }
        .razon { grid-template-columns: 1fr; gap: 1.25rem; margin-bottom: 3rem; }
        .razon:nth-child(even) .razon-visual { order: 0; }
        .razon:nth-child(even) .razon-text { order: 0; }
        .razon-visual { min-height: 140px; }
        .razon-visual span { font-size: 3rem; }
    }
</style>
@endpush

@section('contenido')
<section class="hero">
    <div class="hero-blob a" aria-hidden="true"></div>
    <div class="hero-blob b" aria-hidden="true"></div>
    <div class="container hero-inner">
        <span class="hero-badge">🚀 Beta Chiclayo — 3 cupos gratis</span>
        <h1>Vende más rápido con <em>VentasÁgil</em></h1>
        <p class="hero-lead">
            Punto de venta e inventario en la nube para bodegas, ferreterías y farmacias.
            Controla stock, cobra al instante y olvídate del desorden.
        </p>
        <div class="hero-cta">
            <a href="{{ route('contacto') }}#beta" class="btn btn-primary">Quiero la beta gratis</a>
            <a href="{{ route('precios') }}" class="btn btn-outline">Ver planes</a>
            <a href="https://wa.me/{{ config('ventapos.whatsapp') }}" class="btn btn-whatsapp" target="_blank" rel="noopener">WhatsApp</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong data-count="3">0</strong>
                <span>cupos beta Chiclayo</span>
            </div>
            <div class="hero-stat">
                <strong data-count="1">0</strong>
                <span>mes gratis en beta</span>
            </div>
            <div class="hero-stat">
                <strong>S/ {{ config('planes.arranque.monto') }}</strong>
                <span>arranque todo incluido</span>
            </div>
        </div>
    </div>
</section>

<section class="porque" id="porque">
    <div class="container">
        <div class="porque-header reveal">
            <h2>¿Por qué VentasÁgil?</h2>
            <p>Todo lo que tu bodega necesita para vender, controlar y crecer — sin complicaciones ni sistemas viejos.</p>
        </div>

        @foreach($razones as $i => $r)
        <article class="razon reveal" style="transition-delay: {{ ($i % 3) * 0.08 }}s">
            <div class="razon-text">
                <div class="razon-icon">{{ $r['icono'] }}</div>
                <h3>{{ $r['titulo'] }}</h3>
                <p class="resumen">{{ $r['resumen'] }}</p>
                <p>{{ $r['detalle'] }}</p>
                <ul>
                    @foreach($r['puntos'] as $punto)
                    <li>{{ $punto }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="razon-visual" aria-hidden="true">
                <span>{{ $r['icono'] }}</span>
            </div>
        </article>
        @endforeach
    </div>
</section>

<section class="cta-final reveal">
    <h2>¿Listo para modernizar tu bodega?</h2>
    <p>Únete a la beta en Chiclayo o consulta planes para tu negocio en todo el Perú.</p>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
        <a href="{{ route('contacto') }}#beta" class="btn">Solicitar beta</a>
        <a href="{{ route('precios') }}" class="btn btn-outline">Ver precios</a>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.razon, .reveal').forEach(el => io.observe(el));

    document.querySelectorAll('[data-count]').forEach(el => {
        const target = +el.dataset.count;
        if (!target) return;
        const obs = new IntersectionObserver(([e]) => {
            if (!e.isIntersecting) return;
            obs.disconnect();
            let n = 0;
            const step = () => {
                n++;
                el.textContent = n;
                if (n < target) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }, { threshold: 0.5 });
        obs.observe(el);
    });
})();
</script>
@endpush
