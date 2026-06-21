<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'ventapos:backup-db {--programado : Ejecución del programador (madrugada)}';

    protected $description = 'Crea una copia de seguridad comprimida de la base de datos';

    public function handle(DatabaseBackupService $backups): int
    {
        if (! $backups->mysqldumpDisponible()) {
            $this->error('Herramienta de backup no disponible para la conexión actual.');

            return self::FAILURE;
        }

        $programado = (bool) $this->option('programado');
        $resultado = $backups->crear($programado);

        if (! $resultado['ok']) {
            $this->error($resultado['mensaje']);

            return self::FAILURE;
        }

        $this->info($resultado['mensaje']);
        $this->line('Archivo: '.$resultado['archivo']);

        if (($resultado['eliminados'] ?? 0) > 0) {
            $this->line('Copias antiguas eliminadas: '.$resultado['eliminados']);
        }

        return self::SUCCESS;
    }
}
