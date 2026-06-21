@extends('layouts.app')

@section('titulo', 'Categorías')

@section('contenido')
<h1 style="margin-bottom:1rem">Categorías</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">
    Gestión dinámica por empresa. Los productos se agrupan por categoría en el POS.
    @if($jerarquicas)
    <strong>Modo jerárquico:</strong> categorías padre y subcategorías (un nivel).
    @endif
</p>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1rem;align-items:start">
    <div class="card" style="margin:0">
        <table class="data">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    @if($jerarquicas)<th>Padre</th>@endif
                    <th>Productos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorias as $cat)
                @include('app.categorias._fila', ['cat' => $cat, 'indent' => 0])
                @if($jerarquicas)
                    @foreach($cat->children as $hijo)
                    @include('app.categorias._fila', ['cat' => $hijo, 'indent' => 1])
                    @endforeach
                @endif
                @empty
                <tr><td colspan="{{ $jerarquicas ? 5 : 4 }}" style="color:#64748b">Sin categorías</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card" style="margin:0">
        <h3 style="margin-bottom:.75rem">Nueva categoría</h3>
        <form method="post" action="{{ route('categorias.guardar') }}">
            @csrf
            @if($jerarquicas)
            <label style="font-weight:600;font-size:.85rem">Categoría padre (opcional)</label>
            <select name="parent_id" style="width:100%;padding:.6rem;margin:.35rem 0 .75rem;border:1px solid #cbd5e1;border-radius:.4rem">
                <option value="">— Raíz (categoría principal) —</option>
                @foreach($todas->whereNull('parent_id') as $padre)
                <option value="{{ $padre->id }}" @selected(old('parent_id') == $padre->id)>{{ $padre->nombre }}</option>
                @endforeach
            </select>
            @endif
            <label style="font-weight:600;font-size:.85rem">Nombre</label>
            <input name="nombre" required placeholder="Ej: BEBIDAS" style="width:100%;padding:.6rem;margin:.35rem 0 .75rem;border:1px solid #cbd5e1;border-radius:.4rem;text-transform:uppercase">
            <label style="font-weight:600;font-size:.85rem">Orden</label>
            <input type="number" name="orden" value="0" min="0" style="width:100%;padding:.6rem;margin:.35rem 0 .75rem;border:1px solid #cbd5e1;border-radius:.4rem">
            <button type="submit" class="btn btn-primary" style="width:100%">Agregar</button>
        </form>
    </div>
</div>
@endsection
