<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeccionKEsquema
{
    public static function asegurarDeclaraciones(): void
    {
        if (! Schema::hasTable('seccionk_eventos') || Schema::hasTable('seccionk_declaraciones')) {
            return;
        }

        Schema::create('seccionk_declaraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('seccionk_eventos')->cascadeOnDelete();
            $table->boolean('cerrado')->default(true);
            $table->decimal('monto_utilizado', 10, 2)->default(0);
            $table->date('fecha_cierre')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique('evento_id');
        });
    }
}
