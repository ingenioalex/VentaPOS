<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seccionk_declaraciones')) {
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

    public function down(): void
    {
        Schema::dropIfExists('seccionk_declaraciones');
    }
};
