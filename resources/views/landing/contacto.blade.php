@extends('layouts.guest')

@section('titulo', 'Contacto y Beta')

@section('contenido')
<section style="padding:3rem 0" id="beta">
    <div class="container">
        <div style="background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;border-radius:1rem;padding:2rem;text-align:center;margin-bottom:2rem">
            <h2>Beta gratuita — 3 negocios</h2>
            <p>2 meses gratis a cambio de feedback semanal.</p>
        </div>
        <form method="post" action="{{ route('beta.store') }}" style="max-width:500px;margin:0 auto">
            @csrf
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre del negocio *</label>
                <input name="nombre_negocio" required style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem" value="{{ old('nombre_negocio') }}">
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Tu nombre</label>
                <input name="nombre_contacto" style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem" value="{{ old('nombre_contacto') }}">
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">WhatsApp *</label>
                <input name="telefono" required style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem" value="{{ old('telefono') }}">
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Tipo de negocio *</label>
                <select name="tipo_negocio" required style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    <option value="">Selecciona...</option>
                    @foreach(['bodega' => 'Bodega', 'ferreteria' => 'Ferretería', 'farmacia' => 'Farmacia', 'otro' => 'Otro'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('tipo_negocio') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Mensaje</label>
                <textarea name="mensaje" rows="3" style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">{{ old('mensaje') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Solicitar cupo beta</button>
        </form>
        <p style="text-align:center;margin-top:1.5rem;color:#64748b">
            WhatsApp directo: <a href="https://wa.me/{{ config('ventapos.whatsapp') }}">+{{ config('ventapos.whatsapp') }}</a>
        </p>
    </div>
</section>
@endsection
