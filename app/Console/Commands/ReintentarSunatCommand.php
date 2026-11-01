<?php

namespace App\Console\Commands;

use App\Models\Comprobante;
use App\Services\SunatService;
use Illuminate\Console\Command;

class ReintentarSunatCommand extends Command
{
    protected $signature = 'ventapos:reintentar-sunat {--empresa= : ID empresa} {--limite=20 : Máximo comprobantes}';

    protected $description = 'Reintenta envío SUNAT de comprobantes en error o pendiente';

    public function handle(SunatService $sunat): int
    {
        $query = Comprobante::query()
            ->whereIn('sunat_estado', ['error', 'pendiente'])
            ->whereIn('tipo_documento', ['boleta', 'factura'])
            ->where('estado', 'emitido')
            ->orderBy('emitido_en');

        if ($empresaId = $this->option('empresa')) {
            $query->where('empresa_id', (int) $empresaId);
        }

        $comprobantes = $query->limit((int) $this->option('limite'))->get();

        if ($comprobantes->isEmpty()) {
            $this->info('No hay comprobantes pendientes de reintento.');

            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;

        foreach ($comprobantes as $comprobante) {
            try {
                $resultado = $sunat->reenviar($comprobante);
                if (in_array($resultado->sunat_estado, ['aceptado', 'simulado'], true)) {
                    $ok++;
                    $this->line("✓ {$resultado->numero_interno} → {$resultado->sunat_estado}");
                } else {
                    $fail++;
                    $this->warn("✗ {$resultado->numero_interno} → {$resultado->sunat_estado}: {$resultado->sunat_mensaje}");
                }
            } catch (\Throwable $e) {
                $fail++;
                $this->error("✗ {$comprobante->numero_interno}: {$e->getMessage()}");
            }
        }

        $this->info("Listo: {$ok} aceptados, {$fail} con problemas.");

        return self::SUCCESS;
    }
}
