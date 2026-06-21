<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE usuarios MODIFY rol ENUM('super_admin','dueno','cajero','supervisor','digitador') NOT NULL DEFAULT 'cajero'");
        }

        Schema::table('cierres_caja', function (Blueprint $table) {
            $table->string('terminal', 10)->default('01')->after('tipo_cierre');
            $table->string('caja', 10)->default('01')->after('terminal');
            $table->decimal('monto_efectivo_contado', 12, 2)->nullable()->after('total_efectivo');
            $table->decimal('diferencia_efectivo', 12, 2)->nullable()->after('monto_efectivo_contado');
            $table->foreignId('supervisor_usuario_id')->nullable()->after('usuario_id')
                ->constrained('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cierres_caja', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_usuario_id');
            $table->dropColumn(['terminal', 'caja', 'monto_efectivo_contado', 'diferencia_efectivo']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE usuarios MODIFY rol ENUM('super_admin','dueno','cajero','supervisor') NOT NULL DEFAULT 'cajero'");
        }
    }
};
