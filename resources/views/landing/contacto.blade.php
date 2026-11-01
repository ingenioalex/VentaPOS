@extends('layouts.guest')

@section('titulo', 'Contacto y Beta')

@push('head')
<style>
    .contacto-hero {
        position: relative; overflow: hidden;
        padding: 3.5rem 0 2rem;
        background: linear-gradient(155deg, #ede9fe 0%, #ecfdf5 35%, #fff7ed 70%, #f8fafc 100%);
    }
    .contacto-blob {
        position: absolute; border-radius: 50%; filter: blur(70px); opacity: .5;
        animation: contacto-float 9s ease-in-out infinite;
    }
    .contacto-blob.a { width: 280px; height: 280px; background: #a78bfa; top: -60px; right: -40px; }
    .contacto-blob.b { width: 240px; height: 240px; background: #2dd4bf; bottom: 0; left: -50px; animation-delay: -3s; }
    .contacto-blob.c { width: 200px; height: 200px; background: #fbbf24; top: 30%; left: 40%; animation-delay: -5s; }
    @keyframes contacto-float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(10px, -14px) scale(1.06); }
    }
    .contacto-hero-inner { position: relative; z-index: 1; text-align: center; max-width: 640px; margin: 0 auto; }
    .contacto-hero h1 {
        font-size: clamp(1.9rem, 4.5vw, 2.6rem);
        line-height: 1.15; margin-bottom: .75rem; letter-spacing: -.02em;
    }
    .contacto-hero h1 span {
        background: linear-gradient(90deg, #0f766e, #7c3aed);
        -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .contacto-lead { color: var(--muted); font-size: 1.05rem; }

    .reveal {
        opacity: 0; transform: translateY(28px);
        transition: opacity .65s ease, transform .65s ease;
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    .beta-perks {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;
        max-width: 900px; margin: 2rem auto 0;
    }
    .beta-perk {
        background: rgba(255,255,255,.9); border: 1px solid #e2e8f0;
        border-radius: .85rem; padding: 1rem; text-align: center;
        box-shadow: 0 4px 16px rgba(15,23,42,.05);
    }
    .beta-perk span { font-size: 1.6rem; display: block; margin-bottom: .35rem; }
    .beta-perk strong { display: block; font-size: .9rem; color: #0f172a; }
    .beta-perk small { font-size: .78rem; color: var(--muted); }

    .contacto-grid {
        display: grid; grid-template-columns: 1.15fr .85fr; gap: 1.5rem;
        align-items: start; padding: 2.5rem 0 3.5rem;
    }

    .form-card {
        background: #fff; border-radius: 1.25rem; overflow: hidden;
        border: 2px solid #99f6e4;
        box-shadow: 0 16px 48px rgba(15,118,110,.1);
    }
    .form-card-head {
        background: linear-gradient(135deg, #0f766e 0%, #0891b2 55%, #7c3aed 100%);
        color: #fff; padding: 1.5rem 1.75rem; position: relative; overflow: hidden;
    }
    .form-card-head::before {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 80% 20%, rgba(255,255,255,.18), transparent 45%);
    }
    .form-card-head > * { position: relative; z-index: 1; }
    .form-card-head h2 { font-size: 1.35rem; margin-bottom: .35rem; }
    .form-card-head p { font-size: .92rem; opacity: .92; }
    .form-card-body { padding: 1.75rem; }

    .form-group { margin-bottom: 1.1rem; }
    .form-group label {
        display: block; font-weight: 700; font-size: .88rem;
        margin-bottom: .4rem; color: #334155;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%; padding: .75rem .9rem;
        border: 2px solid #e2e8f0; border-radius: .6rem;
        font-size: .95rem; font-family: inherit;
        transition: border-color .2s, box-shadow .2s;
        background: #f8fafc;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none; border-color: #14b8a6;
        box-shadow: 0 0 0 3px rgba(20,184,166,.15);
        background: #fff;
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .btn-submit {
        width: 100%; padding: .9rem 1.25rem; border: none; border-radius: .65rem;
        font-weight: 800; font-size: 1rem; cursor: pointer; color: #fff;
        background: linear-gradient(135deg, #0f766e, #7c3aed);
        box-shadow: 0 8px 24px rgba(15,118,110,.3);
        transition: transform .15s, box-shadow .15s, filter .15s;
    }
    .btn-submit:hover { filter: brightness(1.05); box-shadow: 0 12px 32px rgba(124,58,237,.25); }
    .btn-submit:active { transform: scale(.98); }

    .info-stack { display: flex; flex-direction: column; gap: 1rem; }
    .info-card {
        background: #fff; border-radius: 1rem; padding: 1.35rem 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(15,23,42,.05);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(15,23,42,.08);
    }
    .info-card.wa {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border-color: #6ee7b7;
    }
    .info-card.wa h3 { color: #047857; }
    .info-card.purple {
        background: linear-gradient(135deg, #f5f3ff, #ede9fe);
        border-color: #c4b5fd;
    }
    .info-card.purple h3 { color: #6d28d9; }
    .info-card.amber {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border-color: #fcd34d;
    }
    .info-card.amber h3 { color: #b45309; }
    .info-card.teal {
        background: linear-gradient(135deg, #ecfdf5, #ccfbf1);
        border-color: #5eead4;
    }
    .info-card.teal h3 { color: #0f766e; }
    .info-card.teal a { color: #0f766e; }
    .fundador-card {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #f0fdfa 0%, #fff 50%, #f5f3ff 100%);
        border: 2px solid #99f6e4;
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
        margin-top: .5rem;
        box-shadow: 0 8px 28px rgba(15,118,110,.08);
    }
    .fundador-card h3 { margin: 0 0 .5rem; font-size: 1.1rem; color: #0f766e; }
    .fundador-card .fundador-tags {
        display: flex; flex-wrap: wrap; gap: .4rem; margin: .75rem 0 1rem;
    }
    .fundador-card .fundador-tags span {
        font-size: .75rem; font-weight: 700; padding: .2rem .55rem; border-radius: 999px;
        background: #fff; border: 1px solid #99f6e4; color: #0f766e;
    }
    .fundador-card p { font-size: .88rem; color: #475569; line-height: 1.55; margin: 0 0 .65rem; }
    .fundador-card .fundador-links { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: .85rem; }
    .fundador-card .fundador-links a {
        font-weight: 700; font-size: .88rem; color: #0f766e; text-decoration: none;
    }
    .info-card.wa a.btn-whatsapp {
        margin-top: .85rem; padding: .55rem 1.1rem; border-radius: .55rem;
        color: #fff !important; display: inline-flex; align-items: center; gap: .35rem;
    }
    .fundador-card .btn-wa-fundador {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .45rem .95rem; border-radius: .55rem; font-weight: 700; font-size: .88rem;
        text-decoration: none; color: #fff; background: #25d366;
        box-shadow: 0 4px 14px rgba(37,211,102,.35);
    }
    .fundador-card .btn-wa-fundador:hover { filter: brightness(1.05); text-decoration: none; }
    .info-card h3 {
        font-size: 1rem; margin-bottom: .5rem;
        display: flex; align-items: center; gap: .5rem;
    }
    .info-card p { font-size: .88rem; color: var(--muted); margin: 0; line-height: 1.55; }
    .info-card a {
        display: inline-flex; align-items: center; gap: .35rem;
        margin-top: .75rem; font-weight: 700; font-size: .9rem;
        text-decoration: none; color: inherit;
    }
    .info-card.wa a { color: #047857; }
    .info-card.purple a { color: #6d28d9; }
    .info-card.amber a { color: #b45309; }

    .contacto-cta {
        text-align: center; padding: 0 0 4rem;
    }
    .contacto-cta-inner {
        max-width: 720px; margin: 0 auto;
        background: linear-gradient(135deg, #1e1b4b, #312e81 50%, #4c1d95);
        color: #fff; border-radius: 1.25rem; padding: 2.25rem 2rem;
        box-shadow: 0 24px 60px rgba(49,46,129,.3);
    }
    .contacto-cta h2 { font-size: 1.45rem; margin-bottom: .6rem; }
    .contacto-cta p { opacity: .9; margin-bottom: 1.25rem; font-size: .95rem; }
    .contacto-cta .btn { background: #fff; color: #4c1d95; margin: 0 .35rem; }
    .contacto-cta .btn-outline { background: transparent; border: 2px solid rgba(255,255,255,.6); color: #fff; }

    @media (max-width: 860px) {
        .contacto-grid { grid-template-columns: 1fr; }
        .beta-perks { grid-template-columns: 1fr; max-width: 360px; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('contenido')
@php
    $beta = config('planes.beta');
    $wa = config('ventapos.whatsapp');
@endphp

<section class="contacto-hero" id="beta">
    <div class="contacto-blob a" aria-hidden="true"></div>
    <div class="contacto-blob b" aria-hidden="true"></div>
    <div class="contacto-blob c" aria-hidden="true"></div>
    <div class="container contacto-hero-inner reveal">
        <h1>Hablemos de tu <span>negocio</span></h1>
        <p class="contacto-lead">
            Solicita un cupo en la beta de Chiclayo o escríbenos para conocer planes y arranque.
        </p>
    </div>

    <div class="container">
        <div class="beta-perks reveal">
            <div class="beta-perk">
                <span>🎁</span>
                <strong>1 mes gratis</strong>
                <small>Beta Chiclayo · 3 cupos</small>
            </div>
            <div class="beta-perk">
                <span>⚡</span>
                <strong>Arranque rápido</strong>
                <small>Instalación y capacitación</small>
            </div>
            <div class="beta-perk">
                <span>💬</span>
                <strong>Feedback semanal</strong>
                <small>Ayudas a mejorar el producto</small>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container contacto-grid">
        <div class="form-card reveal" id="formulario-beta">
            <div class="form-card-head">
                <h2>🚀 Solicitar cupo beta</h2>
                <p>{{ $beta['nota'] }}</p>
            </div>
            <div class="form-card-body">
                <form method="post" action="{{ route('beta.store') }}" id="form-beta">
                    @csrf
                    <input type="text" name="empresa_web" value="" tabindex="-1" autocomplete="off"
                           style="position:absolute;left:-9999px;width:1px;height:1px" aria-hidden="true">
                    <input type="hidden" name="formulario_inicio" id="formulario_inicio" value="">
                    <div class="form-group">
                        <label for="nombre_negocio">Nombre del negocio *</label>
                        <input id="nombre_negocio" name="nombre_negocio" required
                               placeholder="Ej. Bodega El Sol" value="{{ old('nombre_negocio') }}">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre_contacto">Tu nombre</label>
                            <input id="nombre_contacto" name="nombre_contacto"
                                   placeholder="Tu nombre" value="{{ old('nombre_contacto') }}">
                        </div>
                        <div class="form-group">
                            <label for="telefono">WhatsApp *</label>
                            <input id="telefono" name="telefono" required
                                   placeholder="999 888 777" value="{{ old('telefono') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipo_negocio">Tipo de negocio *</label>
                        <select id="tipo_negocio" name="tipo_negocio" required>
                            <option value="">Selecciona tu rubro...</option>
                            @foreach(['bodega' => 'Bodega', 'ferreteria' => 'Ferretería', 'farmacia' => 'Farmacia', 'minimarket' => 'Minimarket', 'otro' => 'Otro'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('tipo_negocio') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mensaje">Cuéntanos más (opcional)</label>
                        <textarea id="mensaje" name="mensaje" rows="3"
                                  placeholder="¿Cuántos productos manejas? ¿Cuántas cajas?">{{ old('mensaje') }}</textarea>
                    </div>
                    <button type="submit" class="btn-submit">Enviar y abrir WhatsApp →</button>
                </form>
            </div>
        </div>

        <aside class="info-stack reveal">
            <div class="info-card wa">
                <h3>💬 WhatsApp directo</h3>
                <p>Respuesta rápida para dudas sobre planes, beta o implementación en tu negocio.</p>
                <a href="https://wa.me/{{ $wa }}" class="btn btn-whatsapp" target="_blank" rel="noopener">
                    Escríbenos por WhatsApp
                </a>
            </div>
            <div class="info-card purple">
                <h3>📍 Chiclayo y todo el Perú</h3>
                <p>
                    La beta está enfocada en negocios de Chiclayo. Para otras ciudades,
                    consulta planes y arranque remoto.
                </p>
                <a href="{{ route('precios') }}">Ver planes y precios →</a>
            </div>
            <div class="info-card amber">
                <h3>✅ Qué incluye la beta</h3>
                <p>
                    POS completo, inventario, reportes y hasta {{ $beta['limites']['usuarios_max'] }} usuarios.
                    Sin cargo de arranque durante el periodo beta.
                </p>
            </div>
            @php
                $linkedinFundador = config('ventapos.linkedin_fundador', '');
            @endphp
            <div class="fundador-card reveal">
                <h3>👤 Quién está detrás de VentasÁgil</h3>
                <div class="fundador-tags">
                    <span>Gerencia retail</span>
                    <span>Ventas comerciales</span>
                    <span>Liderazgo de equipos</span>
                    <span>Ingeniería de sistemas</span>
                </div>
                <p>
                    <strong>Yuri Alexander Vargas Lazo</strong> — Ingeniero de Sistemas (UNPRG),
                    con más de <strong>20 años en retail</strong> en Chiclayo: desde piso de venta en
                    El Súper hasta <strong>Gerente de Tienda</strong> en Hiperbodega Precio Uno (Falabella),
                    pasando por jefaturas comerciales y control de existencias en Tottus.
                </p>
                <p>
                    VentasÁgil nace de esa experiencia: un POS pensado para bodegas y negocios locales,
                    con inventario, cierres de caja, reportes y acompañamiento cercano en la implementación.
                </p>
                <div class="fundador-links">
                    @if($linkedinFundador)
                    <a href="{{ $linkedinFundador }}" target="_blank" rel="noopener">Perfil en LinkedIn →</a>
                    @endif
                    <a href="mailto:ingeniovargas@gmail.com">ingeniovargas@gmail.com</a>
                    <a href="https://wa.me/{{ $wa }}" class="btn-wa-fundador" target="_blank" rel="noopener">WhatsApp</a>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="contacto-cta">
    <div class="container">
        <div class="contacto-cta-inner reveal">
            <h2>¿Prefieres ver precios primero?</h2>
            <p>Compara planes Básico, Negocio y Pro con el mismo arranque para todos.</p>
            <a href="{{ route('precios') }}" class="btn">Ver precios</a>
            <a href="{{ route('inicio') }}" class="btn btn-outline">Volver al inicio</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const inp = document.getElementById('formulario_inicio');
    if (inp) inp.value = Math.floor(Date.now() / 1000);

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
