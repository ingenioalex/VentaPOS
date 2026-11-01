@extends('layouts.app')

@section('titulo', 'Importar productos')

@section('contenido')
@include('app.productos._styles')

<div class="productos-page">
    <div class="import-hero">
        <div class="form-hero-top">
            <div>
                <h1>Importar productos (CSV)</h1>
                <p>Carga masiva desde plantilla · actualiza si el SKU o código de barra ya existe</p>
            </div>
            <a href="{{ route('productos.index') }}" class="btn btn-back">← Volver a productos</a>
        </div>
    </div>

    <div class="panel" style="max-width:760px">
        <div class="panel-title">Subir archivo</div>
        <p class="hint" style="margin-bottom:1rem;line-height:1.5">
            Use punto y coma (<code>;</code>) o coma (<code>,</code>) como separador.
            Descargue la plantilla, complete sus productos y súbala aquí.
        </p>

        <p style="margin-bottom:1.25rem">
            <a href="{{ route('productos.plantilla') }}" class="btn btn-import">Descargar plantilla CSV</a>
        </p>

        <form method="post" action="{{ route('productos.importar.procesar') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Archivo CSV *</label>
                <div class="import-drop">
                    <input type="file" name="archivo" accept=".csv,.txt" required style="width:100%;border:none;background:transparent">
                </div>
                @error('archivo')
                <small style="color:#991b1b">{{ $message }}</small>
                @enderror
            </div>
            <button type="submit" class="btn btn-new">Importar productos</button>
        </form>
    </div>

    <div class="panel" style="max-width:760px">
        <div class="panel-title">Columnas soportadas</div>
        <div class="tabla-wrap">
            <table class="productos-table">
                <thead>
                    <tr><th>Columna</th><th>Obligatorio</th><th>Descripción</th></tr>
                </thead>
                <tbody>
                    <tr><td><span class="mono">nombre</span></td><td class="col-center txt-estado activo">Sí</td><td>Nombre del producto</td></tr>
                    <tr><td><span class="mono">precio_venta</span> / <span class="mono">precio</span></td><td class="col-center txt-estado activo">Sí</td><td>Precio de venta</td></tr>
                    <tr><td><span class="mono">codigo_barra</span></td><td class="col-center txt-estado inactivo">No</td><td>Código de barras</td></tr>
                    <tr><td><span class="mono">sku</span></td><td class="col-center txt-estado inactivo">No</td><td>Se genera automáticamente si falta</td></tr>
                    <tr><td><span class="mono">categoria</span></td><td class="col-center txt-estado inactivo">No</td><td>Se crea si no existe</td></tr>
                    <tr><td><span class="mono">costo_compra</span></td><td class="col-center txt-estado inactivo">No</td><td>Costo de compra</td></tr>
                    <tr><td><span class="mono">stock</span></td><td class="col-center txt-estado inactivo">No</td><td>Stock disponible</td></tr>
                    <tr><td><span class="mono">stock_minimo</span></td><td class="col-center txt-estado inactivo">No</td><td>Alerta de stock bajo</td></tr>
                    <tr><td><span class="mono">unidad_medida</span></td><td class="col-center txt-estado inactivo">No</td><td>UND, KG, etc. (defecto UND)</td></tr>
                    <tr><td><span class="mono">afecto_igv</span></td><td class="col-center txt-estado inactivo">No</td><td>si / no (defecto si)</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    @if(session('import_errores'))
    <div class="panel" style="max-width:760px;border-color:#fecaca">
        <div class="panel-title" style="color:#991b1b">Errores por fila</div>
        <ul style="font-size:.9rem;line-height:1.7;color:#7f1d1d;margin:0;padding-left:1.25rem">
            @foreach(session('import_errores') as $fila => $error)
            <li><strong>Fila {{ $fila }}:</strong> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
