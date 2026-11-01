@extends('layouts.guest')

@section('titulo', 'Verificación de identidad')

@section('contenido')
<section style="padding:3rem 0">
    <div class="container" style="max-width:440px">
        <h1 style="text-align:center;margin-bottom:.5rem">Verificación</h1>
        <p style="text-align:center;color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
            @if($correoVerificado)
                Correo confirmado. Responda su pregunta secreta para obtener el enlace.
            @else
                Complete su correo de recuperación registrado para continuar.
            @endif
        </p>

        @if(session('ok'))
            <div class="alert alert-ok">{{ session('ok') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if($usuario && $emailEnmascarado)
        <div style="margin-bottom:1rem;padding:.85rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:.5rem;text-align:center">
            <small style="color:#047857;display:block;margin-bottom:.25rem">Correo registrado (referencia)</small>
            <strong style="font-family:monospace;font-size:1.05rem;letter-spacing:.02em">{{ $emailEnmascarado }}</strong>
        </div>
        @endif

        @if(! $correoVerificado)
        <form method="post" action="{{ route('admin.recuperar.verificar-correo') }}" style="background:#fff;padding:1.5rem;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08)">
            @csrf
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Correo de recuperación completo</label>
                <input type="email" name="email_recuperacion" value="{{ old('email_recuperacion') }}" required autofocus
                       autocomplete="email" placeholder="ej. ingenio_alex@hotmail.com"
                       style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <p style="font-size:.8rem;color:#64748b;margin:.35rem 0 0">Debe coincidir exactamente con el correo configurado en su cuenta.</p>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Verificar correo</button>
        </form>
        @else
        <form method="post" action="{{ route('admin.recuperar.verificar') }}" style="background:#fff;padding:1.5rem;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08)">
            @csrf
            @if($usuario)
            <div style="margin-bottom:1rem;padding:.85rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem">
                <small style="color:#64748b;display:block;margin-bottom:.25rem">Pregunta secreta</small>
                <strong style="line-height:1.4">{{ $usuario->pregunta_secreta }}</strong>
            </div>
            @endif
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Su respuesta</label>
                <input name="respuesta_secreta" required autofocus autocomplete="off"
                       style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <p style="font-size:.8rem;color:#64748b;margin:.35rem 0 0">No distingue mayúsculas ni espacios al inicio/final.</p>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Verificar y obtener enlace</button>
        </form>
        @endif

        <p style="text-align:center;margin-top:1rem;font-size:.85rem">
            <a href="{{ route('admin.recuperar.solicitud') }}" style="color:#64748b">← Cambiar usuario</a>
        </p>
    </div>
</section>
@endsection
