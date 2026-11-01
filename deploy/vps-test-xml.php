<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

$id = (int) ($argv[1] ?? 3622);
$c = App\Models\Comprobante::withoutGlobalScopes()->find($id);
$path = $c->sunat_xml;
echo 'exists_before='.(Storage::disk('local')->exists($path) ? 'yes' : 'no')."\n";
$xml = app(App\Services\SunatService::class)->obtenerORegenerarXml($c);
echo 'exists_after='.(Storage::disk('local')->exists($path) ? 'yes' : 'no')."\n";
echo 'bytes='.strlen($xml)."\n";
