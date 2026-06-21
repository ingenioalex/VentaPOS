@extends('layouts.app')

@section('titulo', 'Entrada de stock')

@section('contenido')
<h1 style="margin-bottom:1rem">Entrada de mercadería</h1>

@include('app.inventario._nav')

<div class="card" style="max-width:520px">
    <form method="post" action="{{ route('inventario.entrada.guardar') }}">
        @csrf
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Producto *</label>
            <select name="producto_id" id="producto_id" required style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <option value="">Seleccione...</option>
                @foreach($productos as $p)
                <option value="{{ $p->id }}" data-control-lote="{{ ($lotesActivo && $p->control_lote) ? '1' : '0' }}" @selected(old('producto_id') == $p->id)>
                    {{ $p->nombre }} (stock: {{ $p->stock_disponible }})@if($lotesActivo && $p->control_lote) · lote @endif
                </option>
                @endforeach
            </select>
        </div>
        @if($lotesActivo)
        <div id="bloque-lote" style="display:none;margin-bottom:1rem;padding:.75rem;background:#f0fdfa;border-radius:.5rem;border:1px solid #99f6e4">
            <p style="font-size:.85rem;color:#0f766e;margin-bottom:.65rem;font-weight:600">Control por lote (producto con lote activo)</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Nº lote</label>
                    <input name="lote" maxlength="60" value="{{ old('lote') }}" placeholder="Ej: L-2026-001"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                </div>
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Fecha vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                </div>
            </div>
        </div>
        @endif
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Cantidad a ingresar *</label>
            <input type="number" name="cantidad" required min="0.01" step="0.01" value="{{ old('cantidad') }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @error('cantidad')<small style="color:#991b1b">{{ $message }}</small>@enderror
        </div>
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Referencia (factura, guía, etc.)</label>
            <input name="referencia" maxlength="100" value="{{ old('referencia') }}"
                   style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @if(auth()->user()->tieneModulo('compras'))
            <small style="color:#64748b">Para ingresos con factura de proveedor, use el módulo <a href="{{ route('compras.crear') }}">Compras</a>.</small>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Registrar entrada</button>
        <a href="{{ route('inventario.index') }}" style="margin-left:.5rem">Cancelar</a>
    </form>
</div>
@if($lotesActivo)
@push('scripts')
<script>
(function () {
    const sel = document.getElementById('producto_id');
    const bloque = document.getElementById('bloque-lote');
    function toggle() {
        const opt = sel.options[sel.selectedIndex];
        bloque.style.display = opt && opt.dataset.controlLote === '1' ? 'block' : 'none';
    }
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
@endpush
@endif
@endsection
