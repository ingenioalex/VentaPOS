<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = App\Models\Prospecto::query()
    ->where(function ($q) {
        $q->where('nombre_contacto', 'like', '%Otis%')
            ->orWhere('nombre_contacto', 'like', '%Jaylan%')
            ->orWhere('nombre_contacto', 'like', '%DavidBof%')
            ->orWhere('nombre_negocio', 'like', '%DavidBof%');
    })
    ->update(['estado' => 'descartado']);

echo "Prospectos marcados descartados: {$n}\n";
