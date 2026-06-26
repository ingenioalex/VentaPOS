@extends('layouts.guest')

@section('titulo', 'Precios')

@push('head')
<style>
    .precios-hero {
        position: relative; overflow: hidden;
        padding: 3.5rem 0 2.5rem;
        background: linear-gradient(155deg, #ede9fe 0%, #ecfdf5 35%, #fff7ed 70%, #f8fafc 100%);
    }
    .precios-blob {
        position: absolute; border-radius: 50%; filter: blur(70px); opacity: .5;
        animation: precios-float 9s ease-in-out infinite;
    }
    .precios-blob.a { width: 280px; height: 280px; background: #a78bfa; top: -60px; left: -40px; }
    .precios-blob.b { width: 240px; height: 240px; background: #2dd4bf; top: 20px; right: -50px; animation-delay: -2s; }
    .precios-blob.c { width: 200px; height: 200px; background: #fbbf24; bottom: -30px; left: 35%; animation-delay: -5s; }
    @keyframes precios-float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(10px, -14px) scale(1.06); }
    }
    .precios-hero-inner { position: relative; z-index: 1; text-align: center; max-width: 680px; margin: 0 auto; }
    .precios-hero h1 {
        font-size: clamp(1.9rem, 4.5vw, 2.75rem);
        line-height: 1.15; margin-bottom: .85rem; letter-spacing: -.02em;
    }
    .precios-hero h1 span {
        background: linear-gradient(90deg, #0f766e, #7c3aed);
        -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .precios-lead { color: var(--muted); font-size: 1.05rem; max-width: 540px; margin: 0 auto; }

    .reveal {
        opacity: 0; transform: translateY(28px);
        transition: opacity .65s ease, transform .65s ease;
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    .arranque-banner {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #0891b2 50%, #7c3aed 100%);
        color: #fff; border-radius: 1.25rem;
        padding: 2rem 1.75rem; text-align: center;
        max-width: 720px; margin: 2.5rem auto 0;
        box-shadow: 0 20px 50px rgba(15,118,110,.25);
    }
    .arranque-banner::before {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 20% 50%, rgba(255,255,255,.15), transparent 50%);
        pointer-events: none;
    }
    .arranque-banner > * { position: relative; z-index: 1; }
    .arranque-tag {
        display: inline-block; background: rgba(255,255,255,.2);
        padding: .35rem .9rem; border-radius: 999px;
        font-size: .78rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; margin-bottom: .75rem;
    }
    .arranque-precio {
        font-size: clamp(2.5rem, 6vw, 3.25rem); font-weight: 800;
        line-height: 1; margin: .25rem 0 .75rem;
    }
    .arranque-precio small { font-size: .35em; font-weight: 600; opacity: .85; }
    .arranque-detalle { font-size: .95rem; opacity: .95; max-width: 500px; margin: 0 auto; }
    .arranque-nota { font-size: .82rem; opacity: .8; margin-top: .85rem; }

    .beta-strip {
        display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;
        background: linear-gradient(90deg, #fef3c7, #fde68a);
        border: 2px solid #f59e0b; border-radius: 1rem;
        padding: 1.1rem 1.5rem; margin: 2rem auto 0; max-width: 720px;
        box-shadow: 0 8px 24px rgba(245,158,11,.15);
    }
    .beta-strip strong { color: #b45309; font-size: 1.05rem; }
    .beta-strip span { color: #92400e; font-size: .92rem; }
    .beta-strip .btn { background: #f59e0b; color: #fff; box-shadow: 0 4px 12px rgba(245,158,11,.35); }
    .beta-strip .btn:hover { background: #d97706; }

    .planes-section { padding: 3rem 0 4rem; }
    .planes-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem;
        align-items: stretch; margin-top: 2.5rem;
    }

    .plan-card {
        position: relative; display: flex; flex-direction: column;
        background: #fff; border-radius: 1.15rem; padding: 0;
        border: 2px solid var(--plan-border, #e2e8f0);
        box-shadow: 0 4px 20px rgba(15,23,42,.06);
        transition: transform .3s ease, box-shadow .3s ease;
        overflow: hidden;
    }
    .plan-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px rgba(15,23,42,.12);
    }
    .plan-card.featured {
        border-color: var(--plan-accent);
        box-shadow: 0 12px 40px color-mix(in srgb, var(--plan-accent) 25%, transparent);
        transform: scale(1.03);
    }
    .plan-card.featured:hover { transform: scale(1.03) translateY(-6px); }

    .plan-badge {
        position: absolute; top: 1rem; right: 1rem;
        background: var(--plan-accent); color: #fff;
        font-size: .72rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .04em; padding: .3rem .65rem; border-radius: 999px;
    }
    .plan-header {
        padding: 1.75rem 1.5rem 1.25rem;
        background: var(--plan-gradient);
        border-bottom: 1px solid var(--plan-border);
    }
    .plan-icon {
        width: 48px; height: 48px; border-radius: .75rem;
        background: var(--plan-accent); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: .75rem;
        box-shadow: 0 6px 16px color-mix(in srgb, var(--plan-accent) 40%, transparent);
    }
    .plan-header h3 { font-size: 1.35rem; margin-bottom: .15rem; }
    .plan-header .plan-sub { font-size: .82rem; color: var(--muted); }
    .plan-body { padding: 1.25rem 1.5rem 1.5rem; flex: 1; display: flex; flex-direction: column; }
    .plan-precio-wrap { margin-bottom: 1rem; }
    .plan-precio-label { font-size: .78rem; color: var(--muted); text-transform: uppercase; letter-spacing: .03em; }
    .plan-precio {
        font-size: 2.4rem; font-weight: 800; color: var(--plan-accent);
        line-height: 1.1; margin-top: .15rem;
    }
    .plan-precio small { font-size: .95rem; font-weight: 600; color: var(--muted); }
    .plan-arranque-note {
        background: color-mix(in srgb, var(--plan-accent) 10%, #fff);
        color: color-mix(in srgb, var(--plan-accent) 80%, #0f172a);
        font-size: .82rem; padding: .55rem .75rem; border-radius: .5rem;
        margin-bottom: 1rem; border-left: 3px solid var(--plan-accent);
    }
    .plan-list { list-style: none; margin-bottom: 1.5rem; flex: 1; }
    .plan-list li {
        padding: .45rem 0 .45rem 1.4rem; position: relative;
        font-size: .9rem; border-bottom: 1px solid #f1f5f9;
    }
    .plan-list li::before {
        content: '✓'; position: absolute; left: 0;
        color: var(--plan-accent); font-weight: 800;
    }
    .plan-card .btn { width: 100%; text-align: center; }
    .plan-card .btn-plan {
        background: var(--plan-accent); color: #fff;
        box-shadow: 0 4px 14px color-mix(in srgb, var(--plan-accent) 35%, transparent);
    }
    .plan-card .btn-plan:hover { filter: brightness(.92); }
    .plan-card .btn-plan-outline {
        background: #fff; border: 2px solid var(--plan-accent); color: var(--plan-accent);
    }

    .extras-row {
        display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;
        max-width: 900px; margin: 2.5rem auto 0;
    }
    .extra-box {
        background: #fff; border-radius: 1rem; padding: 1.35rem 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(15,23,42,.04);
    }
    .extra-box h4 { font-size: 1rem; margin-bottom: .5rem; color: #0f172a; }
    .extra-box p { font-size: .88rem; color: var(--muted); margin: 0; }
    .extra-box.highlight {
        background: linear-gradient(135deg, #f5f3ff, #ede9fe);
        border-color: #c4b5fd;
    }
    .extra-box.highlight h4 { color: #6d28d9; }

    .ejemplo-box {
        max-width: 640px; margin: 2.5rem auto 0;
        background: #fff; border-radius: 1rem; overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 30px rgba(15,23,42,.06);
    }
    .ejemplo-head {
        background: linear-gradient(90deg, #0f766e, #14b8a6);
        color: #fff; padding: 1rem 1.5rem; font-weight: 700;
    }
    .ejemplo-table { width: 100%; font-size: .92rem; border-collapse: collapse; }
    .ejemplo-table td { padding: .85rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
    .ejemplo-table tr:last-child td { border-bottom: none; }
    .ejemplo-table td:last-child { text-align: right; font-weight: 700; color: #0f766e; }

    .precios-cta {
        text-align: center; padding: 3rem 1.5rem 4rem;
    }
    .precios-cta-inner {
        max-width: 720px; margin: 0 auto;
        background: linear-gradient(135deg, #1e1b4b, #312e81 50%, #4c1d95);
        color: #fff; border-radius: 1.25rem; padding: 2.5rem 2rem;
        box-shadow: 0 24px 60px rgba(49,46,129,.3);
    }
    .precios-cta h2 { font-size: 1.6rem; margin-bottom: .65rem; }
    .precios-cta p { opacity: .9; margin-bottom: 1.35rem; font-size: .98rem; }
    .precios-cta .btn { background: #fff; color: #4c1d95; margin: 0 .35rem; }
    .precios-cta .btn-outline { background: transparent; border: 2px solid rgba(255,255,255,.6); color: #fff; }

    @media (max-width: 900px) {
        .planes-grid { grid-template-columns: 1fr; max-width: 400px; margin-left: auto; margin-right: auto; }
        .plan-card.featured { transform: none; }
        .plan-card.featured:hover { transform: translateY(-6px); }
        .extras-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('contenido')
@php
    $arranque = config('planes.arranque');
    $beta = config('planes.beta');
    $usuarioExtra = config('planes.usuario_extra');
    $temas = [
        'basico' => [
            'icono' => '🏪',
            'accent' => '#0284c7',
            'border' => '#bae6fd',
            'gradient' => 'linear-gradient(160deg, #f0f9ff 0%, #fff 100%)',
        ],
        'negocio' => [
            'icono' => '📈',
            'accent' => '#0f766e',
            'border' => '#99f6e4',
            'gradient' => 'linear-gradient(160deg, #ecfdf5 0%, #fff 100%)',
            'featured' => true,
        ],
        'pro' => [
            'icono' => '⚡',
            'accent' => '#7c3aed',
            'border' => '#ddd6fe',
            'gradient' => 'linear-gradient(160deg, #f5f3ff 0%, #fff 100%)',
        ],
    ];
@endphp

<section class="precios-hero">
    <div class="precios-blob a" aria-hidden="true"></div>
    <div class="precios-blob b" aria-hidden="true"></div>
    <div class="precios-blob c" aria-hidden="true"></div>
    <div class="container precios-hero-inner reveal">
        <h1>Planes claros para tu <span>negocio</span></h1>
        <p class="precios-lead">
            Un solo precio de arranque para todos los planes. El primer mes ya va incluido;
            desde el segundo mes pagas solo tu plan mensual.
        </p>
    </div>

    <div class="container">
        <div class="arranque-banner reveal">
            <span class="arranque-tag">{{ $arranque['etiqueta'] }}</span>
            <p class="arranque-precio">S/ {{ $arranque['monto'] }} <small>único</small></p>
            <p class="arranque-detalle">{{ $arranque['detalle'] }}</p>
            <p class="arranque-nota">Mismo monto para Básico, Negocio y Pro</p>
        </div>

        <div class="beta-strip reveal">
            <div>
                <strong>🚀 Beta Chiclayo</strong>
                <span> — {{ $beta['nota'] }}</span>
            </div>
            <a href="{{ route('contacto') }}#beta" class="btn btn-sm">Solicitar cupo</a>
        </div>
    </div>
</section>

<section class="planes-section">
    <div class="container">
        <div class="planes-grid">
            @foreach(['basico', 'negocio', 'pro'] as $i => $clave)
            @php
                $plan = config('planes.'.$clave);
                $t = $temas[$clave];
            @endphp
            <article class="plan-card reveal {{ !empty($t['featured']) ? 'featured' : '' }}"
                     style="--plan-accent: {{ $t['accent'] }}; --plan-border: {{ $t['border'] }}; --plan-gradient: {{ $t['gradient'] }}; transition-delay: {{ $i * 0.1 }}s">
                @if(!empty($t['featured']))
                <span class="plan-badge">Más popular</span>
                @endif
                <div class="plan-header">
                    <div class="plan-icon">{{ $t['icono'] }}</div>
                    <h3>{{ $plan['nombre'] }}</h3>
                    <p class="plan-sub">{{ $plan['equipo_sugerido'] }}</p>
                </div>
                <div class="plan-body">
                    <div class="plan-precio-wrap">
                        <div class="plan-precio-label">Desde el 2.º mes</div>
                        <div class="plan-precio">
                            S/ {{ $plan['mensual'] }}<small>/mes</small>
                        </div>
                    </div>
                    <p class="plan-arranque-note">
                        1.º mes incluido en el arranque (S/ {{ $arranque['monto'] }})
                    </p>
                    <ul class="plan-list">
                        @foreach($plan['incluye'] as $item)
                        <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('contacto') }}"
                       class="btn {{ !empty($t['featured']) ? 'btn-plan' : 'btn-plan-outline' }}">
                        Consultar {{ $plan['nombre'] }}
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        <div class="extras-row reveal">
            <div class="extra-box">
                <h4>👤 {{ $usuarioExtra['nombre'] }}</h4>
                <p>S/ {{ $usuarioExtra['precio_mensual'] }}/mes por cada usuario activo adicional. {{ $usuarioExtra['descripcion'] }}</p>
            </div>
            <div class="extra-box highlight">
                <h4>✨ Módulos extra</h4>
                <p>WhatsApp, lotes, flejes y más — activables según tu rubro. Consulta al contratar.</p>
            </div>
        </div>

        <div class="ejemplo-box reveal">
            <div class="ejemplo-head">Ejemplo — Plan Negocio</div>
            <table class="ejemplo-table">
                <tr>
                    <td>Día del alta</td>
                    <td>S/ {{ $arranque['monto'] }} <span style="font-weight:400;color:#64748b;font-size:.85rem">(arranque + mes 1)</span></td>
                </tr>
                <tr>
                    <td>Mes 2 en adelante</td>
                    <td>S/ {{ config('planes.negocio.mensual') }}/mes</td>
                </tr>
                <tr>
                    <td>Total año 1 (sin extras)</td>
                    <td>S/ {{ $arranque['monto'] + config('planes.negocio.mensual') * 11 }}</td>
                </tr>
            </table>
        </div>
    </div>
</section>

<section class="precios-cta">
    <div class="container">
        <div class="precios-cta-inner reveal">
            <h2>¿No sabes qué plan elegir?</h2>
            <p>Cuéntanos tu negocio y te recomendamos el plan ideal — sin compromiso.</p>
            <a href="{{ route('contacto') }}" class="btn">Hablar con nosotros</a>
            <a href="https://wa.me/{{ config('ventapos.whatsapp') }}" class="btn btn-outline" target="_blank" rel="noopener">WhatsApp</a>
        </div>
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
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
})();
</script>
@endpush
