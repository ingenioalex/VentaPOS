@extends('layouts.admin')

@section('titulo', $superUsuario->exists ? 'Editar super usuario' : 'Nuevo super usuario')

@section('contenido')
<h1 style="margin-bottom:1rem">{{ $superUsuario->exists ? 'Editar super usuario' : 'Nuevo super usuario' }}</h1>

<div class="card" style="max-width:520px">
    <form method="post" action="{{ $superUsuario->exists ? route('admin.super-usuarios.actualizar', $superUsuario) : route('admin.super-usuarios.guardar') }}">
        @csrf
        @if($superUsuario->exists) @method('PUT') @endif

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre *</label>
            <input name="nombre" required value="{{ old('nombre', $superUsuario->nombre) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario *</label>
            <input name="usuario" required value="{{ old('usuario', $superUsuario->usuario) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Email</label>
            <input type="email" name="email" value="{{ old('email', $superUsuario->email) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">
                Contraseña {{ $superUsuario->exists ? '(dejar vacío para no cambiar)' : '*' }}
            </label>
            <input type="password" name="password" {{ $superUsuario->exists ? '' : 'required' }} minlength="12" autocomplete="new-password"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            <p style="font-size:.85rem;color:#64748b;margin:.35rem 0 0">Mínimo 12 caracteres: mayúsculas, minúsculas, números y símbolo.</p>
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" {{ $superUsuario->exists ? '' : 'required' }} minlength="12" autocomplete="new-password"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        @if($superUsuario->exists)
        <div style="margin-bottom:1rem">
            <label>
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $superUsuario->activo))>
                Usuario activo
            </label>
        </div>
        @endif

        <hr style="margin:1.5rem 0;border:none;border-top:1px solid #e2e8f0">
        <h3 style="margin-bottom:.5rem;font-size:1rem">Recuperación de clave</h3>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
            Si olvida su contraseña, responderá la pregunta secreta y verá el correo enmascarado (ej. ing********.com) y el enlace en pantalla.
        </p>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Correo de recuperación</label>
            <input type="email" name="email_recuperacion" value="{{ old('email_recuperacion', $superUsuario->email_recuperacion) }}"
                   placeholder="ej. ingenio_alex@hotmail.com" autocomplete="email"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Pregunta secreta</label>
            <input name="pregunta_secreta" value="{{ old('pregunta_secreta', $superUsuario->pregunta_secreta) }}"
                   placeholder="Algo que solo usted sepa" maxlength="255"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">
                Respuesta secreta {{ $superUsuario->exists && $superUsuario->respuesta_secreta ? '(dejar vacío para no cambiar)' : '' }}
            </label>
            <input name="respuesta_secreta" autocomplete="off"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar respuesta secreta</label>
            <input name="respuesta_secreta_confirmation" autocomplete="off"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('admin.super-usuarios.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@endsection
