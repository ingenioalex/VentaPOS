<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de promociones (miembros de la sección K)
        Schema::create('seccionk_promociones', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->unique();
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100);
            $table->string('direccion', 255)->nullable();
            $table->string('celular', 15)->nullable();
            $table->boolean('participa_cuotas')->default(false);
            $table->text('referencias')->nullable()->comment('Referencias de otras promociones');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('dni');
            $table->index('apellido_paterno');
            $table->index('activo');
        });

        // Tabla de eventos
        Schema::create('seccionk_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->decimal('monto', 10, 2)->default(0);
            $table->enum('tipo', ['evento', 'cuota_mensual'])->default('evento');
            $table->string('mes_cuota', 20)->nullable()->comment('Formato: Octubre 2026');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('fecha');
            $table->index('tipo');
            $table->index('activo');
        });

        // Tabla de participaciones (relación promoción-evento)
        Schema::create('seccionk_participaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promocion_id')->constrained('seccionk_promociones')->onDelete('cascade');
            $table->foreignId('evento_id')->constrained('seccionk_eventos')->onDelete('cascade');
            $table->boolean('participo')->default(false);
            $table->boolean('pago')->default(false);
            $table->decimal('monto_aportado', 10, 2)->default(0);
            $table->string('numero_transaccion', 100)->nullable();
            $table->string('imagen_sustento', 255)->nullable();
            $table->date('fecha_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->unique(['promocion_id', 'evento_id']);
            $table->index('participo');
            $table->index('pago');
            $table->index('fecha_pago');
        });

        // Tabla de usuarios admin del panel
        Schema::create('seccionk_usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('usuario', 60)->unique();
            $table->string('password');
            $table->string('nombre', 100);
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seccionk_participaciones');
        Schema::dropIfExists('seccionk_eventos');
        Schema::dropIfExists('seccionk_promociones');
        Schema::dropIfExists('seccionk_usuarios');
    }
};
