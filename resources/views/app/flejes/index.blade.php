@extends('layouts.app')

@section('titulo', 'Flejes / etiquetas')

@section('contenido')
<h1 style="margin-bottom:.5rem">Flejes y etiquetas de precio</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">Seleccione productos e imprima etiquetas con código de barras.</p>

<form class="filtros card" method="get">
    <div style="flex:1">
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, SKU o código de barra">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<form method="post" action="{{ route('flejes.imprimir') }}" target="_blank" id="form-flejes">
    @csrf
    <div class="card" style="margin-bottom:1rem;display:flex;gap:1rem;align-items:end;flex-wrap:wrap">
        <div>
            <label style="font-weight:600;font-size:.85rem">Etiquetas por producto</label>
            <input type="number" name="cantidad" value="1" min="1" max="50" style="padding:.45rem;border:1px solid #cbd5e1;border-radius:.4rem;width:80px">
        </div>
        <button type="submit" class="btn btn-primary">Imprimir seleccionados</button>
        <label style="font-size:.85rem"><input type="checkbox" id="sel-todos"> Seleccionar todos</label>
    </div>

    <div class="card" style="overflow:auto">
        <table class="data">
            <thead>
                <tr>
                    <th><input type="checkbox" id="chk-all"></th>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th>Código barra</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $p)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $p->id }}" class="chk-prod"></td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ $p->sku }}</td>
                    <td style="font-family:monospace">{{ $p->codigo_barra ?: '—' }}</td>
                    <td>S/ {{ number_format($p->precio_venta, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="color:#64748b">Sin productos</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $productos->links() }}
    </div>
</form>

@push('scripts')
<script>
document.getElementById('chk-all')?.addEventListener('change', e => {
    document.querySelectorAll('.chk-prod').forEach(c => c.checked = e.target.checked);
});
document.getElementById('sel-todos')?.addEventListener('change', e => {
    document.getElementById('chk-all').checked = e.target.checked;
    document.getElementById('chk-all').dispatchEvent(new Event('change'));
});
</script>
@endpush
@endsection
