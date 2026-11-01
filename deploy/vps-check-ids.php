<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\Comprobante;
foreach ([3575, 3595, 3556] as $id) {
    $c = Comprobante::find($id);
    if (! $c) { echo "$id not found\n"; continue; }
    echo "$id emitido_en={$c->emitido_en} estado={$c->estado}\n";
}
