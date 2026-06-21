<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ventapos:whatsapp-resumen')->dailyAt('20:00');

Schedule::command('ventapos:backup-db --programado')
    ->dailyAt(config('backup.hora_automatica', '03:00'))
    ->when(fn () => (bool) config('backup.automatico_activo', true));
