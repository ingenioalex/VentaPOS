<?php

namespace App\Jobs;

use App\Models\Comprobante;
use App\Services\PosTopProductosService;
use App\Services\SunatService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EmitirSunatComprobanteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $comprobanteId) {}

    public function handle(SunatService $sunat, PosTopProductosService $topProductos): void
    {
        $comprobante = Comprobante::query()->find($this->comprobanteId);
        if (! $comprobante || $comprobante->estado !== 'emitido') {
            return;
        }

        try {
            $sunat->completarEmision($comprobante);
        } catch (\Throwable $e) {
            $comprobante->update([
                'sunat_estado' => 'error',
                'sunat_mensaje' => $e->getMessage(),
            ]);
        } finally {
            $topProductos->invalidarCacheEmpresa($comprobante->empresa_id);
        }
    }
}
