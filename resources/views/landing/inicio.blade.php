@extends('layouts.guest')

@section('titulo', 'Inicio')

@section('contenido')
<section style="padding:4rem 0 3rem;text-align:center">
    <div class="container">
        <span style="display:inline-block;background:#fef3c7;color:#92400e;padding:.35rem .9rem;border-radius:999px;font-size:.85rem;font-weight:600;margin-bottom:1rem">3 cupos beta — Chiclayo</span>
        <h1 style="font-size:clamp(1.8rem,4vw,2.6rem);margin-bottom:1rem">Deja el cuaderno.<br>Controla ventas e inventario desde el celular.</h1>
        <p style="font-size:1.1rem;color:#64748b;max-width:560px;margin:0 auto 2rem">Sistema en la nube para bodegas, ferreterías y farmacias. Vende más rápido y evita faltantes.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="{{ route('contacto') }}#beta" class="btn btn-primary">Quiero la beta gratis</a>
            <a href="https://wa.me/{{ config('ventapos.whatsapp') }}" class="btn btn-whatsapp" target="_blank">WhatsApp</a>
        </div>
    </div>
</section>
<section style="padding:3rem 0;background:#fff">
    <div class="container">
        <h2 style="text-align:center;margin-bottom:2rem">¿Por qué VentaPOS?</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem">
            @foreach([
                ['⚡', 'Venta en segundos', 'Busca o escanea código. El stock se actualiza solo.'],
                ['📊', 'Reportes claros', 'Ventas del día y productos con stock bajo.'],
                ['🏪', 'Hecho para tu rubro', 'Bodega, ferretería o farmacia.'],
                ['☁️', 'En la nube', 'Celular o PC. Datos respaldados.'],
                ['🤝', 'Soporte local', 'Instalación en Chiclayo.'],
                ['💰', 'Arranque S/'.config('planes.arranque.monto'), 'Instalación + 1er mes. Luego desde S/'.config('planes.basico.mensual').'/mes.'],
            ] as [$icono, $titulo, $texto])
            <div style="background:#f8fafc;padding:1.25rem;border-radius:.75rem">
                <h3 style="color:#0f766e;margin-bottom:.5rem">{{ $icono }} {{ $titulo }}</h3>
                <p style="color:#64748b;font-size:.95rem">{{ $texto }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
