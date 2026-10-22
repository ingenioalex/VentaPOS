<?php

namespace App\Console\Commands;

use App\Support\SeccionKLimpiarCuotas;
use Illuminate\Console\Command;

class SeccionKLimpiarCuotasCommand extends Command
{
    protected $signature = 'seccionk:limpiar-cuotas-duplicadas';

    protected $description = 'Deja una sola cuota por mes y junta los pagos de los duplicados';

    public function handle(): int
    {
        $resultado = SeccionKLimpiarCuotas::ejecutar();

        $this->info('Meses con duplicados: '.$resultado['meses']);
        $this->info('Cuotas eliminadas: '.$resultado['eliminados']);
        $this->info('Pagos movidos o fusionados: '.$resultado['pagos_movidos']);

        return self::SUCCESS;
    }
}
