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
    .hero-inner { position: relative; z-index: 1; text-align: center; max-width: 920px; margin: 0 auto; }
    .hero-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        background: linear-gradient(135deg, #fff 0%, #ecfdf5 100%);
        border: 2px solid #5eead4; color: #0f766e;
        padding: .55rem 1.35rem; border-radius: 999px; font-size: .95rem; font-weight: 800;
        margin-bottom: 1.5rem; box-shadow: 0 4px 20px rgba(15,118,110,.15);
    }
    .hero h1 {
        font-size: clamp(2.35rem, 6vw, 3.65rem); line-height: 1.1;
        margin-bottom: 1.15rem; letter-spacing: -.03em;
    }
    .hero h1 em { font-style: normal; color: var(--primary); }
    .hero-lead {
        font-size: clamp(1.08rem, 2.2vw, 1.28rem); color: var(--muted);
        margin-bottom: 2.25rem; max-width: 640px; margin-left: auto; margin-right: auto; line-height: 1.55;
    }
    .hero-cta { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2.75rem; }
    .hero-cta .btn { padding: .85rem 1.65rem; font-size: 1.05rem; }
    .hero-cta .btn-primary { box-shadow: 0 8px 28px rgba(15,118,110,.35); }

    .hero-offers {
        display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;
        max-width: 820px; margin: 0 auto; text-align: left;
    }
    .offer-card {
        border-radius: 1.1rem; padding: 1.5rem 1.35rem;
        position: relative; overflow: hidden;
        min-height: 200px; display: flex; flex-direction: column;
    }
    .offer-card::before {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 120px; height: 120px; border-radius: 50%; opacity: .35;
    }
    .offer-beta {
        background: linear-gradient(145deg, #0f766e 0%, #14b8a6 55%, #5eead4 100%);
        color: #fff; border: 2px solid #0d9488;
        box-shadow: 0 16px 40px rgba(15,118,110,.28);
    }
    .offer-beta::before { background: #fff; }
    .offer-arranque {
        background: linear-gradient(145deg, #fffbeb 0%, #fff 45%, #fef3c7 100%);
        color: #78350f; border: 2px solid #fbbf24;
        box-shadow: 0 16px 40px rgba(245,158,11,.18);
    }
    .offer-arranque::before { background: #f59e0b; }
    .offer-tag {
        display: inline-block; font-size: .72rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .08em;
        padding: .3rem .65rem; border-radius: .35rem; margin-bottom: .75rem; width: fit-content;
    }
    .offer-beta .offer-tag { background: rgba(255,255,255,.22); color: #ecfdf5; }
    .offer-arranque .offer-tag { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .offer-card h3 { font-size: 1.65rem; margin-bottom: .5rem; line-height: 1.15; }
    .offer-beta h3 { color: #fff; }
    .offer-arranque h3 { color: #b45309; font-size: 2rem; }
    .offer-price { font-size: 2.35rem; font-weight: 900; line-height: 1; color: #b45309; }
    .offer-card p { font-size: .92rem; line-height: 1.45; margin-bottom: .85rem; flex: 1; }
    .offer-beta p { color: rgba(255,255,255,.92); }
    .offer-arranque p { color: #92400e; }
    .offer-list { list-style: none; margin: 0 0 1rem; padding: 0; }
    .offer-list li {
        padding: .35rem 0 .35rem 1.35rem; position: relative; font-size: .95rem; font-weight: 600;
    }
    .offer-beta .offer-list li { color: #f0fdfa; }
    .offer-beta .offer-list li::before {
        content: '✓'; position: absolute; left: 0; font-weight: 900; color: #fef08a;
    }
    .offer-link {
        display: inline-block; font-weight: 800; font-size: .92rem;
        text-decoration: none; margin-top: auto; padding: .55rem 1rem; border-radius: .5rem;
        transition: transform .15s, box-shadow .15s;
    }
    .offer-link:hover { transform: translateY(-1px); }
    .offer-beta .offer-link {
        background: #fff; color: #0f766e; box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .offer-arranque .offer-link {
        background: #f59e0b; color: #fff; box-shadow: 0 4px 12px rgba(245,158,11,.35);
    }

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
        .hero-offers { grid-template-columns: 1fr; }
        .offer-card { min-height: auto; }
        .hero-cta { flex-direction: column; align-items: stretch; margin-bottom: 2rem; }
        .hero-cta .btn { width: 100%; text-align: center; padding: .75rem 1rem; font-size: 1rem; }
        .hero-lead { font-size: 1.05rem; }
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
            <strong>Punto de venta (POS) e inventario en la nube</strong> para bodegas, minimarkets, ferreterías y farmacias en Chiclayo y todo el Perú.
            Controla stock, cobra al instante y emite boleta electrónica SUNAT con el plan Pro.
        </p>
        <div class="hero-cta">
            <a href="{{ route('contacto') }}#beta" class="btn btn-primary">Quiero la beta</a>
            <a href="{{ route('precios') }}" class="btn btn-outline">Ver planes</a>
            <a href="https://wa.me/{{ config('ventapos.whatsapp') }}" class="btn btn-whatsapp" target="_blank" rel="noopener">WhatsApp</a>
        </div>

        @php
            $beta = config('planes.beta');
            $arranque = config('planes.arranque');
        @endphp
        <div class="hero-offers">
            <div class="offer-card offer-beta">
                <span class="offer-tag">🚀 Beta Chiclayo</span>
                <h3>Prueba sin costo</h3>
                <ul class="offer-list">
                    <li><strong>3 cupos</strong> disponibles</li>
                    <li><strong>1 mes gratis</strong> de uso</li>
                    <li>Sin cargo de arranque</li>
                    <li>Hasta {{ $beta['limites']['usuarios_max'] }} usuarios</li>
                </ul>
                <a href="{{ route('contacto') }}#beta" class="offer-link">Solicitar cupo beta →</a>
            </div>
            <div class="offer-card offer-arranque">
                <span class="offer-tag">Plan de inicio</span>
                <p class="offer-price">S/ {{ $arranque['monto'] }}</p>
                <h3 style="font-size:1.15rem;margin-bottom:.65rem">{{ $arranque['etiqueta'] }}</h3>
                <p>{{ $arranque['detalle'] }}</p>
                <p style="font-size:.85rem;margin-bottom:0">Luego desde <strong>S/ {{ config('planes.basico.mensual') }}/mes</strong> según plan.</p>
                <a href="{{ route('precios') }}" class="offer-link">Ver planes y precios →</a>
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
})();
</script>
@endpush
