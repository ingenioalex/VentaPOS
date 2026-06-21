<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('en_sesion')->default(false)->after('activo');
            $table->dateTime('sesion_inicio_en')->nullable()->after('en_sesion');
            $table->string('sesion_laravel_id', 120)->nullable()->after('sesion_inicio_en');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['en_sesion', 'sesion_inicio_en', 'sesion_laravel_id']);
        });
    }
};
