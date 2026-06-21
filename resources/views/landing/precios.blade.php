@extends('layouts.guest')

@section('titulo', 'Precios')

@section('contenido')
@php
    $arranque = config('planes.arranque');
@endphp
<section style="padding:3rem 0">
    <div class="container">
        <h1 style="text-align:center;margin-bottom:.5rem">Planes y precios</h1>
        <p style="text-align:center;color:#64748b;margin-bottom:1.5rem;max-width:560px;margin-left:auto;margin-right:auto">
            Un solo precio de arranque para todos los planes. El primer mes ya va incluido; desde el segundo mes pagas solo tu plan.
        </p>

        <div style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;border-radius:.75rem;padding:1.5rem 1.75rem;text-align:center;margin-bottom:2rem;max-width:640px;margin-left:auto;margin-right:auto">
            <p style="font-size:.85rem;opacity:.9;margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.04em">{{ $arranque['etiqueta'] }}</p>
            <p style="font-size:2.5rem;font-weight:800;margin:.25rem 0">S/ {{ $arranque['monto'] }}</p>
            <p style="font-size:.95rem;opacity:.95;max-width:480px;margin:0 auto">{{ $arranque['detalle'] }}</p>
            <p style="font-size:.8rem;opacity:.8;margin-top:.75rem">Mismo monto para Básico, Negocio y Pro</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem">
            @foreach(['basico', 'negocio', 'pro'] as $clave)
            @php $plan = config('planes.'.$clave); @endphp
            <div style="background:#fff;border:2px solid {{ $clave === 'negocio' ? '#0f766e' : '#e2e8f0' }};border-radius:.75rem;padding:1.5rem;text-align:center">
                <h3>{{ $plan['nombre'] }}</h3>
                <p style="font-size:.85rem;color:#64748b;margin:.75rem 0 .25rem">Desde el 2.º mes</p>
                <p style="font-size:2.2rem;font-weight:800;color:#0f766e;margin:0 0 1rem">
                    S/ {{ $plan['mensual'] }}<small style="font-size:1rem;color:#64748b">/mes</small>
                </p>
                <p style="background:#f0fdfa;color:#065f46;font-size:.88rem;padding:.5rem .75rem;border-radius:.5rem;margin-bottom:1rem">
                    1.º mes: incluido en el arranque (S/ {{ $arranque['monto'] }})
                </p>
                <ul style="list-style:none;text-align:left;margin-bottom:1.5rem">
                    @foreach($plan['incluye'] as $item)
                    <li style="padding:.35rem 0;border-bottom:1px solid #f1f5f9">✓ {{ $item }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('contacto') }}" class="btn {{ $clave === 'negocio' ? 'btn-primary' : 'btn-outline' }}">Consultar</a>
            </div>
            @endforeach
        </div>

        <div style="max-width:640px;margin:2rem auto 0;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem">
            <h3 style="font-size:1rem;margin-bottom:.75rem;color:#0f766e">Ejemplo — Plan Negocio</h3>
            <table style="width:100%;font-size:.9rem;border-collapse:collapse">
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:.5rem 0">Día del alta</td>
                    <td style="padding:.5rem 0;text-align:right"><strong>S/ {{ $arranque['monto'] }}</strong> (arranque + mes 1)</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:.5rem 0">Mes 2 en adelante</td>
                    <td style="padding:.5rem 0;text-align:right"><strong>S/ {{ config('planes.negocio.mensual') }}/mes</strong></td>
                </tr>
            </table>
        </div>

        <p style="text-align:center;margin-top:2rem;font-size:.9rem;color:#64748b">
            <strong>Beta:</strong> {{ config('planes.beta.nota') }}
        </p>
    </div>
</section>
@endsection
