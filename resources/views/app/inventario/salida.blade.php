@extends('layouts.app')

@section('titulo', 'Salida de stock')

@section('contenido')
<h1 style="margin-bottom:1rem">Salida de stock</h1>

@include('app.inventario._nav')

<div class="card" style="max-width:520px">
    <p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">Use para mermas, roturas, consumo interno o devoluciones a proveedor.</p>
    <form method="post" action="{{ route('inventario.salida.guardar') }}">
        @csrf
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Producto *</label>
            <select name="producto_id" required style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <option value="">Seleccione...</option>
                @foreach($productos as $p)
                <option value="{{ $p->id }}" @selected(old('producto_id') == $p->id)>
                    {{ $p->nombre }} (stock: {{ $p->stock_disponible }})
                </option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Cantidad a retirar *</label>
            <input type="number" name="cantidad" required min="0.01" step="0.01" value="{{ old('cantidad') }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @error('cantidad')<small style="color:#991b1b">{{ $message }}</small>@enderror
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Motivo / referencia</label>
            <input name="referencia" maxlength="100" value="{{ old('referencia') }}" placeholder="Merma, vencido, etc."
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <button type="submit" class="btn" style="background:#dc2626;color:#fff">Registrar salida</button>
        <a href="{{ route('inventario.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@endsection
