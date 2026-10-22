<?php

namespace App\Console\Commands;

use App\Support\SeccionKEsquema;
use Illuminate\Console\Command;

class SeccionKAsegurarEsquemaCommand extends Command
{
    protected $signature = 'seccionk:asegurar-esquema';

    protected $description = 'Crea la tabla de declaraciones de Sección K si falta, sin tocar la data existente';

    public function handle(): int
    {
        SeccionKEsquema::asegurarDeclaraciones();
        $this->info('Tabla seccionk_declaraciones lista. No se modificó la data existente.');

        return self::SUCCESS;
    }
}
