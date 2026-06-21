@extends('layouts.app')

@section('titulo', 'Ajuste de stock')

@section('contenido')
<h1 style="margin-bottom:1rem">Ajuste de inventario</h1>

@include('app.inventario._nav')

<div class="card" style="max-width:520px">
    <p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">Indique el stock real contado. El sistema calculará la diferencia.</p>
    <form method="post" action="{{ route('inventario.ajuste.guardar') }}">
        @csrf
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Producto *</label>
            <select name="producto_id" id="producto_id" required style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <option value="">Seleccione...</option>
                @foreach($productos as $p)
                <option value="{{ $p->id }}" data-stock="{{ $p->stock_disponible }}" @selected(old('producto_id') == $p->id)>
                    {{ $p->nombre }} (actual: {{ $p->stock_disponible }})
                </option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Stock nuevo (real) *</label>
            <input type="number" name="stock_nuevo" id="stock_nuevo" required min="0" step="0.01" value="{{ old('stock_nuevo') }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @error('stock_nuevo')<small style="color:#991b1b">{{ $message }}</small>@enderror
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Motivo</label>
            <input name="referencia" maxlength="100" value="{{ old('referencia', 'Conteo físico') }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
        </div>
        <button type="submit" class="btn btn-warning">Registrar ajuste</button>
        <a href="{{ route('inventario.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('producto_id')?.addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    if (opt?.dataset.stock) {
        document.getElementById('stock_nuevo').value = opt.dataset.stock;
    }
});
</script>
@endpush
