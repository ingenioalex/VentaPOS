@extends('layouts.guest')

@section('titulo', 'Recuperar acceso')

@section('contenido')
<section style="padding:3rem 0">
    <div class="container" style="max-width:400px">
        <h1 style="text-align:center;margin-bottom:.5rem">Recuperar acceso</h1>
        <p style="text-align:center;color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
            Ingrese su usuario de administrador
        </p>
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('admin.recuperar.solicitar') }}" style="background:#fff;padding:1.5rem;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08)">
            @csrf
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario</label>
                <input name="usuario" value="{{ old('usuario') }}" required autofocus autocomplete="username"
                       style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Continuar</button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:.85rem">
            <a href="{{ route('admin.login') }}" style="color:#64748b">← Volver al acceso</a>
        </p>
    </div>
</section>
@endsection
