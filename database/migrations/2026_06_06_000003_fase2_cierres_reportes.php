<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->enum('tipo_cierre', ['X', 'Z'])->default('X');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->unsignedInteger('cantidad_ventas')->default(0);
            $table->decimal('total_ventas', 12, 2)->default(0);
            $table->decimal('ticket_promedio', 12, 2)->default(0);
            $table->decimal('total_efectivo', 12, 2)->default(0);
            $table->decimal('total_tarjeta', 12, 2)->default(0);
            $table->decimal('total_otros', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->index(['empresa_id', 'fecha_fin']);
        });

        Schema::create('producto_precio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->decimal('precio_anterior', 12, 4)->default(0);
            $table->decimal('precio_nuevo', 12, 4)->default(0);
            $table->decimal('costo_anterior', 12, 4)->default(0);
            $table->decimal('costo_nuevo', 12, 4)->default(0);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::table('comprobantes', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->after('estado');
            $table->string('motivo_anulacion', 255)->nullable()->after('observaciones');
            $table->foreignId('anulado_por_usuario_id')->nullable()->after('motivo_anulacion')
                ->constrained('usuarios')->nullOnDelete();
            $table->dateTime('anulado_en')->nullable()->after('anulado_por_usuario_id');
        });
    }

    public function down(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('anulado_por_usuario_id');
            $table->dropColumn(['observaciones', 'motivo_anulacion', 'anulado_en']);
        });
        Schema::dropIfExists('producto_precio');
        Schema::dropIfExists('cierres_caja');
    }
};
