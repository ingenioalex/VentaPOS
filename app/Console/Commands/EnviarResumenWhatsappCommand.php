<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Services\ExtrasEmpresaService;
use App\Services\WhatsappResumenService;
use Illuminate\Console\Command;

class EnviarResumenWhatsappCommand extends Command
{
    protected $signature = 'ventapos:whatsapp-resumen {--fecha= : YYYY-MM-DD (default ayer)}';

    protected $description = 'Genera enlaces wa.me con resumen diario de ventas por empresa';

    public function handle(ExtrasEmpresaService $extras, WhatsappResumenService $whatsapp): int
    {
        $fecha = $this->option('fecha')
            ? \Carbon\Carbon::parse($this->option('fecha'))
            : today()->subDay();

        $count = 0;

        Empresa::where('activo', true)->chunk(50, function ($empresas) use ($extras, $whatsapp, $fecha, &$count) {
            foreach ($empresas as $empresa) {
                if (! $extras->activo($empresa, 'whatsapp_resumen')) {
                    continue;
                }

                $url = $whatsapp->urlEnviar($empresa, $fecha);
                if ($url === '#') {
                    $this->warn("{$empresa->nombre}: sin teléfono configurado.");

                    continue;
                }

                $this->line("{$empresa->nombre} ({$fecha->format('d/m/Y')}):");
                $this->line($url);
                $this->newLine();
                $count++;
            }
        });

        $this->info("Empresas con resumen: {$count}");

        return self::SUCCESS;
    }
}
