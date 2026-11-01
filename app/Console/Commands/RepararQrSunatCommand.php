<?php

namespace App\Console\Commands;

use App\Models\Comprobante;
use App\Services\SunatService;
use Illuminate\Console\Command;

class RepararQrSunatCommand extends Command
{
    protected $signature = 'ventapos:reparar-qr-sunat {--empresa= : ID empresa} {--limite=500}';

    protected $description = 'Genera hash/XML SUNAT faltante para boletas y facturas (QR en ticket)';

    public function handle(SunatService $sunat): int
    {
        $query = Comprobante::query()
            ->where('estado', 'emitido')
            ->whereIn('tipo_documento', ['boleta', 'factura'])
            ->where(function ($q) {
                $q->whereNull('sunat_hash')->orWhereNull('sunat_xml');
            });

        if ($empresaId = $this->option('empresa')) {
            $query->where('empresa_id', (int) $empresaId);
        }

        $comprobantes = $query->orderBy('id')->limit((int) $this->option('limite'))->get();
        $ok = 0;
        $fallos = 0;

        foreach ($comprobantes as $comprobante) {
            try {
                $sunat->asegurarHashQr($comprobante);
                $ok++;
                $this->line("OK #{$comprobante->id} {$comprobante->numero_interno} ({$comprobante->medio_pago})");
            } catch (\Throwable $e) {
                $fallos++;
                $this->error("ERR #{$comprobante->id}: {$e->getMessage()}");
            }
        }

        $this->info("Reparados: {$ok} · Errores: {$fallos}");

        return $fallos > 0 ? self::FAILURE : self::SUCCESS;
    }
}
