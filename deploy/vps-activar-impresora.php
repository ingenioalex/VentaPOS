<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Empresa;

$actualizadas = 0;

foreach (Empresa::where('activo', true)->get() as $empresa) {
    $config = $empresa->configuracion ?? [];
    $mods = $config['modulos_contratados'] ?? null;

    if (! is_array($mods)) {
        continue;
    }

    if (in_array('impresora', $mods, true)) {
        continue;
    }

    if (! in_array('pos', $mods, true)) {
        continue;
    }

    $mods[] = 'impresora';
    $config['modulos_contratados'] = array_values(array_unique($mods));
    $empresa->update(['configuracion' => $config]);
    echo "OK {$empresa->codigo}\n";
    $actualizadas++;
}

echo "Empresas actualizadas: {$actualizadas}\n";
