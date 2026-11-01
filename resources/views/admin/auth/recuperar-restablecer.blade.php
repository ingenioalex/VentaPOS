@extends('layouts.guest')

@section('titulo', 'Nueva clave')

@section('contenido')
<section style="padding:3rem 0">
    <div class="container" style="max-width:400px">
        <h1 style="text-align:center;margin-bottom:.5rem">Nueva clave</h1>
        <p style="text-align:center;color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
            Usuario: <strong>{{ $usuario->usuario }}</strong>
        </p>
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('admin.recuperar.restablecer.guardar', $token) }}" style="background:#fff;padding:1.5rem;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08)">
            @csrf
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Nueva contraseña</label>
                <input type="password" name="password" required minlength="12" autocomplete="new-password"
                       style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <p style="font-size:.8rem;color:#64748b;margin:.35rem 0 0">Mínimo 12 caracteres: mayúsculas, minúsculas, números y símbolo.</p>
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"
                       style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Guardar nueva clave</button>
        </form>
    </div>
</section>
@endsection
