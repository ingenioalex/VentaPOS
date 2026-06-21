<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->enum('tipo_documento', ['dni', 'ruc', 'ce', 'otro'])->default('dni');
            $table->string('numero_documento', 15);
            $table->string('nombre', 200);
            $table->string('telefono', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['empresa_id', 'numero_documento']);
        });

        Schema::create('ventas_suspendidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('codigo_hold', 13)->unique();
            $table->json('datos');
            $table->decimal('total', 12, 2);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::table('comprobantes', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->after('usuario_id')->constrained('clientes')->nullOnDelete();
            $table->enum('tipo_documento', ['boleta', 'factura', 'nota'])->default('boleta')->after('numero_interno');
            $table->string('serie', 10)->default('B001')->after('tipo_documento');
            $table->unsignedInteger('numero')->default(0)->after('serie');
            $table->decimal('monto_recibido', 12, 2)->nullable()->after('total');
            $table->decimal('vuelto', 12, 2)->nullable()->after('monto_recibido');
        });

        Schema::table('detalle_comprobante', function (Blueprint $table) {
            $table->decimal('descuento_pct', 5, 2)->default(0)->after('precio_unitario');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_comprobante', function (Blueprint $table) {
            $table->dropColumn('descuento_pct');
        });

        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropColumn(['tipo_documento', 'serie', 'numero', 'monto_recibido', 'vuelto']);
        });

        Schema::dropIfExists('ventas_suspendidas');
        Schema::dropIfExists('clientes');
    }
};
