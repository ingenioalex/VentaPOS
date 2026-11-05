<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seccionk_movimientos')) {
            return;
        }

        Schema::create('seccionk_movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->string('persona', 150)->nullable();
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->text('comentario')->nullable();
            $table->unsignedBigInteger('prestamo_id')->nullable();
            $table->timestamps();
            $table->index('tipo');
            $table->index('prestamo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seccionk_movimientos');
    }
};
