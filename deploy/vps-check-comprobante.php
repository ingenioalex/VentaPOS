<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = (int) ($argv[1] ?? 3622);
$c = App\Models\Comprobante::withoutGlobalScopes()->find($id);
if (! $c) {
    echo "Comprobante {$id} no existe\n";
    exit(1);
}
echo "id={$c->id} empresa={$c->empresa_id} sunat_estado={$c->sunat_estado}\n";
echo "sunat_xml={$c->sunat_xml}\n";
$path = $c->sunat_xml ? storage_path('app/'.$c->sunat_xml) : null;
echo "file_exists=".($path && file_exists($path) ? 'yes' : 'no')."\n";
if ($path) {
    echo "path={$path}\n";
}
