@extends('layouts.app')

@section('titulo', $terminal->exists ? 'Editar terminal' : 'Nueva terminal')

@section('contenido')
<h1 style="margin-bottom:1rem">{{ $terminal->exists ? 'Editar terminal' : 'Nueva terminal' }}</h1>

<div class="card" style="max-width:520px">
    <form method="post" action="{{ $terminal->exists ? route('terminales.actualizar', $terminal) : route('terminales.guardar') }}">
        @csrf
        @if($terminal->exists) @method('PUT') @endif

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Código *</label>
            <input name="codigo" required maxlength="10" value="{{ old('codigo', $terminal->codigo) }}"
                   placeholder="01, CAJA01"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            <small style="color:#64748b">Identificador corto que aparece en tickets y POS.</small>
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre *</label>
            <input name="nombre" required maxlength="100" value="{{ old('nombre', $terminal->nombre) }}"
                   placeholder="Caja principal"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Local / sucursal</label>
            <input name="local" maxlength="100" value="{{ old('local', $terminal->local) }}"
                   placeholder="Chiclayo Centro"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Caja *</label>
            <input name="caja" required maxlength="10" value="{{ old('caja', $terminal->caja ?? '01') }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>

        @if($terminal->exists)
        <div style="margin-bottom:1rem">
            <label>
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $terminal->activo))>
                Terminal activa
            </label>
        </div>
        @endif

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('terminales.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@endsection
