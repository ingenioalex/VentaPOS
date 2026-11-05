<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seccionk_eventos_edicion')) {
            return;
        }

        Schema::create('seccionk_eventos_edicion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('seccionk_eventos')->cascadeOnDelete();
            $table->boolean('abierto')->default(false);
            $table->timestamps();
            $table->unique('evento_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seccionk_eventos_edicion');
    }
};
