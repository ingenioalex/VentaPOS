<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Comprobante;

$id = (int) ($argv[1] ?? 3636);
$c = Comprobante::with(['empresa', 'cliente'])->find($id);
if (! $c) {
    echo "not found\n";
    exit(1);
}

echo json_encode([
    'id' => $c->id,
    'tipo' => $c->tipo_documento,
    'medio' => $c->medio_pago,
    'sunat_estado' => $c->sunat_estado,
    'tiene_hash' => (bool) $c->sunat_hash,
    'hash_len' => strlen((string) $c->sunat_hash),
    'mensaje' => $c->sunat_mensaje,
    'qr_texto' => $c->textoQrSunat() ? 'ok' : 'null',
    'electronico' => $c->esComprobanteElectronico(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
