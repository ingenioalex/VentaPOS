<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('codigo', 10);
            $table->string('nombre', 100);
            $table->string('local', 100)->nullable();
            $table->string('caja', 10)->default('01');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['empresa_id', 'codigo']);
        });

        Schema::create('usuario_terminal', function (Blueprint $table) {
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('terminal_id')->constrained('terminales')->cascadeOnDelete();
            $table->boolean('es_defecto')->default(false);
            $table->primary(['usuario_id', 'terminal_id']);
        });

        Schema::table('comprobantes', function (Blueprint $table) {
            $table->foreignId('terminal_id')->nullable()->after('usuario_id')
                ->constrained('terminales')->nullOnDelete();
        });

        Schema::table('cierres_caja', function (Blueprint $table) {
            $table->foreignId('terminal_id')->nullable()->after('tipo_cierre')
                ->constrained('terminales')->nullOnDelete();
        });

        $this->migrarConfiguracionLegacy();
    }

    public function down(): void
    {
        Schema::table('cierres_caja', function (Blueprint $table) {
            $table->dropConstrainedForeignId('terminal_id');
        });

        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('terminal_id');
        });

        Schema::dropIfExists('usuario_terminal');
        Schema::dropIfExists('terminales');
    }

    private function migrarConfiguracionLegacy(): void
    {
        $empresas = DB::table('empresas')->get(['id', 'configuracion']);

        foreach ($empresas as $empresa) {
            $config = json_decode($empresa->configuracion ?? '{}', true) ?: [];
            $codigo = $config['terminal'] ?? '01';
            $local = $config['local'] ?? null;
            $caja = $config['caja'] ?? '01';

            $terminalId = DB::table('terminales')->insertGetId([
                'empresa_id' => $empresa->id,
                'codigo' => $codigo,
                'nombre' => 'Terminal '.$codigo,
                'local' => $local,
                'caja' => $caja,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);

            $cajeros = DB::table('usuarios')
                ->where('empresa_id', $empresa->id)
                ->whereIn('rol', ['cajero', 'supervisor'])
                ->pluck('id');

            foreach ($cajeros as $index => $usuarioId) {
                DB::table('usuario_terminal')->insert([
                    'usuario_id' => $usuarioId,
                    'terminal_id' => $terminalId,
                    'es_defecto' => $index === 0,
                ]);
            }
        }
    }
};
