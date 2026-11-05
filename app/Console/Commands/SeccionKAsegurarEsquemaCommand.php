<?php

namespace App\Console\Commands;

use App\Support\SeccionKEsquema;
use Illuminate\Console\Command;

class SeccionKAsegurarEsquemaCommand extends Command
{
    protected $signature = 'seccionk:asegurar-esquema';

    protected $description = 'Crea tablas nuevas de Sección K si faltan, sin tocar la data existente';

    public function handle(): int
    {
        SeccionKEsquema::asegurarDeclaraciones();
        SeccionKEsquema::asegurarEdicionEventos();
        $this->info('Tabla seccionk_declaraciones lista. No se modificó la data existente.');
        $this->info('Tabla seccionk_eventos_edicion lista. No se modificó la data existente.');

        return self::SUCCESS;
    }
}
