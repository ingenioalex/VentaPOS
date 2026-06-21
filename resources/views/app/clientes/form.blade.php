@extends('layouts.app')

@section('titulo', $cliente->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('contenido')
<h1 style="margin-bottom:1rem">{{ $cliente->exists ? 'Editar cliente' : 'Nuevo cliente' }}</h1>
<div class="card" style="max-width:520px">
    <form method="post" action="{{ $cliente->exists ? route('clientes.actualizar', $cliente) : route('clientes.guardar') }}">
        @csrf
        @if($cliente->exists) @method('PUT') @endif

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Tipo documento</label>
            <select name="tipo_documento" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                @foreach(['dni','ruc','ce','otro'] as $t)
                <option value="{{ $t }}" @selected(old('tipo_documento', $cliente->tipo_documento) === $t)>{{ strtoupper($t) }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Número documento *</label>
            <input name="numero_documento" required value="{{ old('numero_documento', $cliente->numero_documento) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre *</label>
            <input name="nombre" required value="{{ old('nombre', $cliente->nombre) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Teléfono</label>
            <input name="telefono" value="{{ old('telefono', $cliente->telefono) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Dirección</label>
            <input name="direccion" value="{{ old('direccion', $cliente->direccion) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        @if($cliente->exists)
        <div style="margin-bottom:1rem">
            <label><input type="checkbox" name="activo" value="1" @checked(old('activo', $cliente->activo))> Activo</label>
        </div>
        @endif
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('clientes.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@endsection
