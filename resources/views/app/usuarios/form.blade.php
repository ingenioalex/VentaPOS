@extends('layouts.app')

@section('titulo', $usuarioEdit->exists ? 'Editar usuario' : 'Nuevo usuario')

@section('contenido')
<h1 style="margin-bottom:1rem">{{ $usuarioEdit->exists ? 'Editar usuario' : 'Nuevo usuario' }}</h1>

<div class="card" style="max-width:520px">
    <form method="post" action="{{ $usuarioEdit->exists ? route('usuarios.actualizar', $usuarioEdit) : route('usuarios.guardar') }}">
        @csrf
        @if($usuarioEdit->exists) @method('PUT') @endif

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre completo *</label>
            <input name="nombre" required value="{{ old('nombre', $usuarioEdit->nombre) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario de acceso *</label>
            <input name="usuario" required value="{{ old('usuario', $usuarioEdit->usuario) }}"
                   pattern="[A-Za-z0-9_-]+" title="Solo letras, números, guion y guion bajo"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Rol *</label>
            <select name="rol" required style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                @foreach($roles as $rol)
                <option value="{{ $rol }}" @selected(old('rol', $usuarioEdit->rol) === $rol)>
                    {{ config('permisos.etiquetas_rol.'.$rol, $rol) }}
                </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Email</label>
            <input type="email" name="email" value="{{ old('email', $usuarioEdit->email) }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">
                Contraseña {{ $usuarioEdit->exists ? '(dejar vacío para no cambiar)' : '*' }}
            </label>
            <input type="password" name="password" {{ $usuarioEdit->exists ? '' : 'required' }}
                   minlength="6" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" {{ $usuarioEdit->exists ? '' : 'required' }}
                   minlength="6" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        @if($usuarioEdit->exists)
        <div style="margin-bottom:1rem">
            <label>
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuarioEdit->activo))>
                Usuario activo
            </label>
        </div>
        @endif

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('usuarios.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@include('partials.form-submit-once')
@endsection
