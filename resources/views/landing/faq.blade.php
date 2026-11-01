@extends('layouts.guest')

@section('titulo', 'Preguntas frecuentes')

@push('head')
<style>
    .faq-hero {
        padding: 3rem 0 2rem;
        background: linear-gradient(160deg, #ecfdf5 0%, #f8fafc 60%, #fff 100%);
        text-align: center;
    }
    .faq-hero h1 { font-size: clamp(1.85rem, 4vw, 2.5rem); margin-bottom: .75rem; }
    .faq-hero p { color: var(--muted); max-width: 560px; margin: 0 auto 1.5rem; }
    .faq-list { max-width: 760px; margin: 0 auto 3rem; }
    .faq-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        margin-bottom: .75rem;
        overflow: hidden;
    }
    .faq-item summary {
        padding: 1rem 1.15rem;
        font-weight: 700;
        cursor: pointer;
        list-style: none;
        color: var(--text);
    }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-item summary::after { content: '+'; float: right; color: var(--primary); font-weight: 800; }
    .faq-item[open] summary::after { content: '−'; }
    .faq-item p {
        padding: 0 1.15rem 1rem;
        color: #475569;
        line-height: 1.55;
        font-size: .95rem;
    }
    .faq-cta { text-align: center; margin-bottom: 3rem; }
</style>
@endpush

@section('contenido')
<section class="faq-hero">
    <div class="container">
        <h1>Preguntas frecuentes sobre VentasÁgil</h1>
        <p>POS, inventario y facturación electrónica para bodegas y negocios en Chiclayo y todo el Perú.</p>
        <a href="{{ route('contacto') }}#beta" class="btn btn-primary">Probar gratis</a>
    </div>
</section>

<section class="container faq-list" aria-label="Preguntas frecuentes">
    @foreach($preguntas as $item)
    <details class="faq-item">
        <summary>{{ $item['q'] }}</summary>
        <p>{{ $item['a'] }}</p>
    </details>
    @endforeach
</section>

<div class="container faq-cta">
    <p style="color:var(--muted);margin-bottom:1rem">¿No encontraste tu respuesta?</p>
    <a href="{{ route('contacto') }}" class="btn btn-outline">Escríbenos</a>
    <a href="{{ route('precios') }}" class="btn btn-primary" style="margin-left:.5rem">Ver precios</a>
</div>
@endsection
