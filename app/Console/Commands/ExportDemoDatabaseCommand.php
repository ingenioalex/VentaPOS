<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use App\Services\DemoDatasetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ExportDemoDatabaseCommand extends Command
{
    protected $signature = 'ventapos:export-demo
        {dataset=both : minimal, showcase o both}
        {--force : Sin confirmación (borra toda la BD)}';

    protected $description = 'Genera copias .sql.gz de demostración (mínima y/o showcase Pro)';

    public function handle(DemoDatasetService $demo, DatabaseBackupService $backup): int
    {
        $dataset = strtolower((string) $this->argument('dataset'));
        if (! in_array($dataset, ['minimal', 'showcase', 'both'], true)) {
            $this->error('Dataset debe ser: minimal, showcase o both');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Esto ejecutará migrate:fresh y borrará TODA la base de datos actual. ¿Continuar?')) {
            $this->warn('Cancelado.');

            return self::SUCCESS;
        }

        $destino = database_path('exports');
        File::ensureDirectoryExists($destino);

        $tareas = $dataset === 'both' ? ['minimal', 'showcase'] : [$dataset];
        $archivos = [];

        foreach ($tareas as $tipo) {
            $this->info("=== Generando dataset: {$tipo} ===");
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->line(Artisan::output());

            if ($tipo === 'minimal') {
                $demo->seedMinimal();
                $nombre = 'ventapos-minimal.sql.gz';
            } else {
                $this->info('Sembrando empresa showcase (3600 ventas, puede tardar 1-3 min)...');
                $demo->seedShowcase();
                $nombre = 'ventapos-showcase-pro.sql.gz';
            }

            if (! $backup->mysqldumpDisponible()) {
                $this->error('Backup no disponible. Revise mysqldump o conexión MySQL.');

                return self::FAILURE;
            }

            $resultado = $backup->crear(false);
            if (! $resultado['ok']) {
                $this->error($resultado['mensaje']);

                return self::FAILURE;
            }

            $origen = $backup->rutaDescarga($resultado['archivo']);
            $final = $destino.DIRECTORY_SEPARATOR.$nombre;
            File::copy($origen, $final);
            $archivos[] = $final;

            $this->info("Exportado: {$final}");
            $this->line('Método: '.($resultado['metodo'] ?? 'mysqldump'));
            $this->line('Tamaño: '.$backup->formatearTamano((int) filesize($final)));
            $this->newLine();
        }

        $this->info('Listo. Archivos en database/exports/');
        $this->table(['Archivo'], array_map(fn ($f) => [$f], $archivos));

        if ($dataset === 'minimal' || $dataset === 'both') {
            $this->line('Minimal → super: super / superadmin123 · empresa demo: admin / admin123');
        }
        if ($dataset === 'showcase' || $dataset === 'both') {
            $this->line('Showcase → super: super / superadmin123 · San Martín: admin / admin123');
            $this->line('Login empresa código: san-martin · Plan Pro · Ene-Jun 2026 · ~3600 ventas');
        }
        $this->line('Restaurar en VPS: gunzip -c archivo.sql.gz | mysql -u USER -p BASE');

        return self::SUCCESS;
    }
}
