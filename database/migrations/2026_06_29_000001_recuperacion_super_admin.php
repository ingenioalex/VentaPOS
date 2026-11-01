<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('email_recuperacion', 150)->nullable()->after('email');
            $table->string('pregunta_secreta', 255)->nullable()->after('email_recuperacion');
            $table->string('respuesta_secreta', 255)->nullable()->after('pregunta_secreta');
        });

        Schema::create('admin_recuperacion_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expira_en');
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_recuperacion_tokens');

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['email_recuperacion', 'pregunta_secreta', 'respuesta_secreta']);
        });
    }
};
