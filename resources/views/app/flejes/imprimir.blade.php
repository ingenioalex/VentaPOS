<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Flejes — {{ $empresa->nombre }}</title>
    <style>
        @page { margin: 5mm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .grid { display: flex; flex-wrap: wrap; gap: 4mm; }
        .etiqueta {
            width: 50mm; height: 30mm; border: 1px dashed #999;
            padding: 2mm; box-sizing: border-box; text-align: center;
            page-break-inside: avoid; overflow: hidden;
        }
        .etiqueta .nombre { font-size: 8pt; font-weight: bold; line-height: 1.1; max-height: 9mm; overflow: hidden; }
        .etiqueta .precio { font-size: 14pt; font-weight: bold; margin: 1mm 0; }
        .etiqueta .codigo { font-size: 7pt; color: #333; }
        svg.barcode { max-width: 100%; height: 10mm; }
        @media print { .no-print { display: none; } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
</head>
<body onload="generarCodigos()">
    <p class="no-print" style="text-align:center;padding:1rem">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </p>
    <div class="grid">
        @foreach($productos as $p)
            @for($i = 0; $i < $cantidad; $i++)
            <div class="etiqueta">
                <div class="nombre">{{ $p->nombre }}</div>
                <div class="precio">S/ {{ number_format($p->precio_venta, 2) }}</div>
                @php $barcode = $p->codigo_barra ?: $p->sku; @endphp
                @if($barcode)
                <svg class="barcode" data-code="{{ $barcode }}"></svg>
                @endif
                <div class="codigo">{{ $barcode }}</div>
            </div>
            @endfor
        @endforeach
    </div>
    <script>
    function generarCodigos() {
        document.querySelectorAll('svg.barcode').forEach(el => {
            try {
                JsBarcode(el, el.dataset.code, { format: 'CODE128', displayValue: false, margin: 0, height: 28, width: 1.2 });
            } catch (_) {}
        });
        setTimeout(() => window.print(), 400);
    }
    </script>
</body>
</html>
