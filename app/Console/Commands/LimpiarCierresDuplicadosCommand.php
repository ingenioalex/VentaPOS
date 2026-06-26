<?php

namespace App\Console\Commands;

use App\Services\CierreCajaService;
use Illuminate\Console\Command;

class LimpiarCierresDuplicadosCommand extends Command
{
    protected $signature = 'ventapos:limpiar-cierres-duplicados {--empresa= : ID de empresa (opcional)}';

    protected $description = 'Elimina de la base de datos los cierres X/Z duplicados del mismo turno';

    public function handle(CierreCajaService $cierres): int
    {
        $empresaId = $this->option('empresa');
        $empresaId = $empresaId !== null && $empresaId !== '' ? (int) $empresaId : null;

        $resultado = $cierres->eliminarDuplicadosEnBd($empresaId);
        $fechas = $cierres->repararFechasInconsistentes($empresaId);
        $totales = $cierres->repararPeriodosYTotales($empresaId);

        $this->info("Cierres conservados: {$resultado['conservados']}");
        $this->info("Cierres eliminados: {$resultado['eliminados']}");
        $this->line("Grupos con duplicados: {$resultado['grupos_duplicados']}");
        $this->line("Fechas reparadas: {$fechas['reparados']}");
        $this->line("Períodos/totales recalculados: {$totales['reparados']}");

        return self::SUCCESS;
    }
}
