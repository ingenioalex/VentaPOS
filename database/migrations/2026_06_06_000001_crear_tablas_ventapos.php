<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('codigo', 80)->unique();
            $table->enum('tipo_negocio', ['bodega', 'ferreteria', 'farmacia', 'otro'])->default('bodega');
            $table->string('ruc', 11)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->json('configuracion')->nullable();
            $table->enum('plan', ['beta', 'basico', 'negocio', 'pro'])->default('beta');
            $table->boolean('activo')->default(true);
            $table->date('prueba_hasta')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->string('usuario', 60);
            $table->string('email', 150)->nullable();
            $table->string('password');
            $table->enum('rol', ['super_admin', 'dueno', 'cajero', 'supervisor'])->default('cajero');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['empresa_id', 'usuario']);
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->integer('orden')->default(0);
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['empresa_id', 'nombre']);
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('sku', 50)->nullable();
            $table->string('codigo_barra', 64)->nullable();
            $table->string('nombre', 200);
            $table->string('unidad_medida', 20)->default('UND');
            $table->decimal('costo_compra', 12, 4)->default(0);
            $table->decimal('precio_venta', 12, 4)->default(0);
            $table->boolean('afecto_igv')->default(true);
            $table->decimal('stock_disponible', 12, 4)->default(0);
            $table->decimal('stock_minimo', 12, 4)->default(0);
            $table->string('campo1', 100)->nullable();
            $table->string('campo2', 100)->nullable();
            $table->string('campo3', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->index(['empresa_id', 'codigo_barra']);
        });

        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('numero_interno', 30);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('valor_igv', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('medio_pago', ['efectivo', 'yape', 'plin', 'tarjeta', 'mixto'])->default('efectivo');
            $table->enum('estado', ['abierto', 'emitido', 'anulado'])->default('emitido');
            $table->dateTime('emitido_en');
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['empresa_id', 'numero_interno']);
        });

        Schema::create('detalle_comprobante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('descripcion_producto', 200);
            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_unitario', 12, 4);
            $table->decimal('subtotal', 12, 2);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('inventario_movimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'venta', 'ajuste']);
            $table->decimal('cantidad', 12, 4);
            $table->decimal('stock_anterior', 12, 4);
            $table->decimal('stock_nuevo', 12, 4);
            $table->string('referencia', 100)->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('prospectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_negocio', 150);
            $table->string('nombre_contacto', 120)->nullable();
            $table->string('telefono', 20);
            $table->string('email', 150)->nullable();
            $table->string('tipo_negocio', 50)->nullable();
            $table->text('mensaje')->nullable();
            $table->enum('origen', ['landing', 'beta', 'whatsapp', 'referido'])->default('landing');
            $table->enum('estado', ['nuevo', 'contactado', 'demo', 'beta', 'cliente', 'descartado'])->default('nuevo');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospectos');
        Schema::dropIfExists('inventario_movimiento');
        Schema::dropIfExists('detalle_comprobante');
        Schema::dropIfExists('comprobantes');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('empresas');
    }
};
