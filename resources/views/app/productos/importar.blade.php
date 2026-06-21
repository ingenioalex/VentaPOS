@extends('layouts.app')

@section('titulo', 'Importar productos')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h1>Importar productos (CSV)</h1>
    <a href="{{ route('productos.index') }}" class="btn" style="background:#64748b;color:#fff">← Volver a productos</a>
</div>

<div class="card" style="max-width:720px">
    <p style="color:#64748b;font-size:.9rem;margin-bottom:1rem">
        Suba un archivo CSV con encabezados. Use punto y coma (<code>;</code>) o coma (<code>,</code>).
        Si el código de barra o SKU ya existe, el producto se actualiza.
    </p>

    <p style="margin-bottom:1rem">
        <a href="{{ route('productos.plantilla') }}" class="btn btn-primary">Descargar plantilla CSV</a>
    </p>

    <form method="post" action="{{ route('productos.importar.procesar') }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom:1rem">
            <label style="font-weight:600;display:block;margin-bottom:.35rem">Archivo CSV *</label>
            <input type="file" name="archivo" accept=".csv,.txt" required
                   style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.5rem;background:#fff">
            @error('archivo')
            <small style="color:#991b1b">{{ $message }}</small>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Importar productos</button>
    </form>
</div>

<div class="card" style="max-width:720px">
    <h3 style="margin-bottom:.75rem">Columnas soportadas</h3>
    <table class="data">
        <thead>
            <tr><th>Columna</th><th>Obligatorio</th><th>Descripción</th></tr>
        </thead>
        <tbody>
            <tr><td><code>nombre</code></td><td>Sí</td><td>Nombre del producto</td></tr>
            <tr><td><code>precio_venta</code> / <code>precio</code></td><td>Sí</td><td>Precio de venta</td></tr>
            <tr><td><code>codigo_barra</code></td><td>No</td><td>Código de barras</td></tr>
            <tr><td><code>sku</code></td><td>No</td><td>Se genera automáticamente si falta</td></tr>
            <tr><td><code>categoria</code></td><td>No</td><td>Se crea si no existe</td></tr>
            <tr><td><code>costo_compra</code></td><td>No</td><td>Costo de compra</td></tr>
            <tr><td><code>stock</code></td><td>No</td><td>Stock disponible</td></tr>
            <tr><td><code>stock_minimo</code></td><td>No</td><td>Alerta de stock bajo</td></tr>
            <tr><td><code>unidad_medida</code></td><td>No</td><td>UND, KG, etc. (defecto UND)</td></tr>
            <tr><td><code>afecto_igv</code></td><td>No</td><td>si / no (defecto si)</td></tr>
        </tbody>
    </table>
</div>

@if(session('import_errores'))
<div class="card" style="max-width:720px">
    <h3 style="margin-bottom:.75rem;color:#991b1b">Errores por fila</h3>
    <ul style="font-size:.9rem;line-height:1.6;color:#7f1d1d">
        @foreach(session('import_errores') as $fila => $error)
        <li><strong>Fila {{ $fila }}:</strong> {{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@endsection
