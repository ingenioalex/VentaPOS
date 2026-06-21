<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->enum('tipo_documento', ['ruc', 'dni', 'ce', 'otro'])->default('ruc');
            $table->string('numero_documento', 15);
            $table->string('razon_social', 200);
            $table->string('nombre_contacto', 120)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['empresa_id', 'numero_documento']);
        });

        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->enum('tipo_documento', ['orden', 'guia', 'factura'])->default('factura');
            $table->string('serie', 10)->nullable();
            $table->string('numero_documento', 20);
            $table->date('fecha_emision');
            $table->date('fecha_recepcion')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('valor_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado', ['borrador', 'recibido', 'anulado'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->string('motivo_anulacion')->nullable();
            $table->foreignId('anulado_por_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->timestamp('recibido_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['empresa_id', 'proveedor_id', 'tipo_documento', 'serie', 'numero_documento'], 'compras_doc_unique');
        });

        Schema::create('compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 12, 4);
            $table->decimal('costo_unitario', 12, 4);
            $table->boolean('afecto_igv')->default(true);
            $table->decimal('subtotal', 12, 2);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::table('inventario_movimiento', function (Blueprint $table) {
            $table->foreignId('compra_id')->nullable()->after('referencia')->constrained('compras')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventario_movimiento', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compra_id');
        });

        Schema::dropIfExists('compra_detalle');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('proveedores');
    }
};
