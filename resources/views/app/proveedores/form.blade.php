@extends('layouts.app')

@section('titulo', $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor')

@section('contenido')
@include('app.compras._nav')

<h1 style="margin-bottom:1rem">{{ $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor' }}</h1>
<div class="card" style="max-width:560px">
    <form method="post" action="{{ $proveedor->exists ? route('proveedores.actualizar', $proveedor) : route('proveedores.guardar') }}">
        @csrf
        @if($proveedor->exists) @method('PUT') @endif

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Tipo documento</label>
            <select name="tipo_documento" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                @foreach(['ruc','dni','ce','otro'] as $t)
                <option value="{{ $t }}" @selected(old('tipo_documento', $proveedor->tipo_documento ?? 'ruc') === $t)>{{ strtoupper($t) }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Número documento *</label>
            <input name="numero_documento" required value="{{ old('numero_documento', $proveedor->numero_documento) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @error('numero_documento')<small style="color:#991b1b">{{ $message }}</small>@enderror
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Razón social *</label>
            <input name="razon_social" required value="{{ old('razon_social', $proveedor->razon_social) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre contacto</label>
            <input name="nombre_contacto" value="{{ old('nombre_contacto', $proveedor->nombre_contacto) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Teléfono</label>
            <input name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Correo</label>
            <input type="email" name="email" value="{{ old('email', $proveedor->email) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Dirección</label>
            <input name="direccion" value="{{ old('direccion', $proveedor->direccion) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        @if($proveedor->exists)
        <div style="margin-bottom:1rem">
            <label><input type="checkbox" name="activo" value="1" @checked(old('activo', $proveedor->activo))> Activo</label>
        </div>
        @endif
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('proveedores.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@endsection
