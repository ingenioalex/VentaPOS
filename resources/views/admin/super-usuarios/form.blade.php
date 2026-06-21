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
            <input type="password" name="password" {{ $superUsuario->exists ? '' : 'required' }} minlength="6"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" {{ $superUsuario->exists ? '' : 'required' }} minlength="6"
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

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('admin.super-usuarios.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@endsection
