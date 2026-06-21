<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('empresa_id')
                ->constrained('categorias')->nullOnDelete();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('control_lote')->default(false)->after('activo');
        });

        Schema::create('producto_lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('lote', 60)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('cantidad', 12, 4)->default(0);
            $table->string('referencia', 100)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->index(['empresa_id', 'fecha_vencimiento']);
            $table->index(['producto_id', 'fecha_vencimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_lotes');

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('control_lote');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
