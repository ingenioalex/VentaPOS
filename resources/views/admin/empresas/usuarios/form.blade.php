@extends('layouts.admin')

@section('titulo', ($usuarioEdit->exists ? 'Editar' : 'Nuevo').' usuario — '.$empresa->nombre)

@section('contenido')
<h1 style="margin-bottom:.25rem">{{ $usuarioEdit->exists ? 'Editar usuario' : 'Nuevo usuario' }}</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">
    Empresa: <strong>{{ $empresa->nombre }}</strong> (<code>{{ $empresa->codigo }}</code>)
</p>

<div class="card" style="max-width:520px">
    <form method="post" action="{{ $usuarioEdit->exists
        ? route('admin.empresas.usuarios.actualizar', [$empresa, $usuarioEdit])
        : route('admin.empresas.usuarios.guardar', $empresa) }}">
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
                   pattern="[A-Za-z0-9_-]+" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
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
            <input type="password" name="password" {{ $usuarioEdit->exists ? '' : 'required' }} minlength="6"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" {{ $usuarioEdit->exists ? '' : 'required' }} minlength="6"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        @if($usuarioEdit->exists)
        <div style="margin-bottom:1rem">
            <label>
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuarioEdit->activo))>
                Usuario activo
            </label>
            @if($usuarioEdit->en_sesion)
            <p style="font-size:.85rem;color:#d97706;margin-top:.5rem">
                Tiene sesión activa. Puede cerrarla desde el listado de usuarios.
            </p>
            @endif
        </div>
        @endif

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('admin.empresas.usuarios.index', $empresa) }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@include('partials.form-submit-once')
@endsection
