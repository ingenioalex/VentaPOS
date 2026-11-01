<style>
    body {
        font-family: 'Courier New', monospace;
        font-size: {{ $estilos['body'] ?? '13px' }};
        max-width: {{ $estilos['max'] ?? '360px' }};
        margin: 1rem auto;
        padding: 1rem;
        color: #000;
    }
    .centro { text-align: center; }
    hr { border: none; border-top: 1px dashed #000; margin: .5rem 0; }
    .fila { display: flex; justify-content: space-between; margin: .2rem 0; gap: .5rem; }
    .fila span:last-child { text-align: right; }
    .grande { font-size: calc({{ $estilos['body'] ?? '13px' }} + 3px); font-weight: bold; }
    table { width: 100%; font-size: {{ $estilos['table'] ?? '11px' }}; border-collapse: collapse; margin: .35rem 0; }
    th, td { text-align: left; padding: .15rem 0; vertical-align: top; }
    th { border-bottom: 1px solid #000; }
    td.num, th.num { text-align: right; }
    .ticket-logo { max-height: 56px; max-width: 90%; margin-bottom: .35rem; }
    .qr-wrap { margin: .5rem auto; padding: 6px; background: #fff; display: inline-block; border: 1px solid #000; }
    .qr-wrap img {
        display: block;
        width: {{ $estilos['qr'] ?? '168px' }};
        height: {{ $estilos['qr'] ?? '168px' }};
        image-rendering: pixelated;
        image-rendering: crisp-edges;
    }
    .notas-pie { font-size: calc({{ $estilos['body'] ?? '13px' }} - 1px); margin-top: .35rem; }
    .pie-legal { font-size: calc({{ $estilos['body'] ?? '13px' }} - 2px); margin-top: .5rem; }
    @media print {
        .no-print { display: none; }
        body { margin: 0; padding: .25rem; max-width: {{ (int) ($impresora['ancho_mm'] ?? 80) }}mm; width: {{ (int) ($impresora['ancho_mm'] ?? 80) }}mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .qr-wrap img { width: 42mm; height: 42mm; }
        @page { margin: 2mm; }
    }
</style>
