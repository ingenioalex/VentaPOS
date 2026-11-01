@extends('layouts.guest')

@section('titulo', 'Enlace de recuperación')

@section('contenido')
<section style="padding:3rem 0">
    <div class="container" style="max-width:480px">
        <h1 style="text-align:center;margin-bottom:.5rem">Identidad verificada</h1>
        <p style="text-align:center;color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
            Usuario <strong>{{ $usuario->usuario }}</strong> — correo de recuperación:
        </p>

        <div class="card" style="background:#fff;padding:1.5rem;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08);margin-bottom:1rem">
            <p style="text-align:center;margin:0 0 1rem">
                <span style="display:inline-block;font-family:monospace;font-size:1.15rem;font-weight:700;color:#0f766e;letter-spacing:.03em">{{ $emailEnmascarado }}</span>
            </p>
            <p style="font-size:.88rem;color:#64748b;text-align:center;margin:0 0 1.25rem;line-height:1.5">
                Use el botón siguiente para crear una nueva contraseña. El enlace es de un solo uso y expira en <strong>60 minutos</strong>.
            </p>
            <a href="{{ $urlRestablecer }}" class="btn btn-primary" style="display:block;text-align:center;width:100%;text-decoration:none">
                Crear nueva contraseña
            </a>
        </div>

        <p style="font-size:.8rem;color:#94a3b8;text-align:center;line-height:1.45">
            No cierre esta ventana hasta abrir el enlace. Si expira, repita el proceso desde «¿Olvidó su contraseña?».
        </p>
        <p style="text-align:center;margin-top:1rem;font-size:.85rem">
            <a href="{{ route('admin.login') }}" style="color:#64748b">← Volver al acceso</a>
        </p>
    </div>
</section>
@endsection
