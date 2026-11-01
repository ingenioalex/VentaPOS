@extends('layouts.guest')

@section('titulo', 'Acceso')

@section('contenido')
<section style="padding:3rem 0">
    <div class="container" style="max-width:400px">
        <h1 style="text-align:center;margin-bottom:1.5rem">Acceso VentasÁgil</h1>
        @if($empresa ?? null)
        <p style="text-align:center;color:#64748b;font-size:.9rem;margin-bottom:1rem">
            Empresa: <strong>{{ $empresa->nombre }}</strong>
        </p>
        @endif
        @if($modoBloqueo ?? false)
        <div style="background:#fff7ed;border:1px solid #fdba74;color:#92400e;padding:.85rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.88rem;line-height:1.45">
            <strong>Modo bloqueo.</strong>
            @if($nombreBloqueo)
            {{ $nombreBloqueo }}, su turno sigue abierto
            @else
            Su turno sigue abierto
            @endif
            (otra pestaña, cierre del navegador o inactividad).
            Ingrese su <strong>contraseña</strong> para desbloquear y continuar en el POS.
        </div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('login') }}" style="background:#fff;padding:1.5rem;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08)">
            @csrf
            @if($empresa ?? null)
                <input type="hidden" name="empresa_codigo" value="{{ $empresa->codigo }}">
            @else
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Código empresa</label>
                <input name="empresa_codigo" value="{{ old('empresa_codigo') }}" required
                       placeholder="bodega-el-sol o minimarket-luna" style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <small style="color:#64748b">Código exacto de su empresa. No es el nombre comercial.</small>
                @error('empresa_codigo')
                <p style="color:#b91c1c;font-size:.85rem;margin-top:.35rem">{{ $message }}</p>
                @enderror
            </div>
            @endif
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario</label>
                <input name="usuario" value="{{ old('usuario') }}" required autofocus style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Contraseña</label>
                <input type="password" name="password" required style="width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;font-size:.9rem">
                <input type="checkbox" name="recordar"> Recordarme
            </label>
            <button type="submit" class="btn btn-primary" style="width:100%">
                {{ ($modoBloqueo ?? false) ? 'Desbloquear e ingresar' : 'Ingresar' }}
            </button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:.85rem;color:#64748b">
            <a href="{{ route('inicio') }}" style="color:#0f766e">← Volver al inicio</a>
        </p>
    </div>
</section>
@endsection
