<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $empresas = DB::table('empresas')->get(['id', 'configuracion']);

        foreach ($empresas as $empresa) {
            $config = json_decode($empresa->configuracion ?? '{}', true) ?: [];
            $config['onboarding_completado'] = true;
            $config['onboarding_paso'] = 4;

            DB::table('empresas')->where('id', $empresa->id)->update([
                'configuracion' => json_encode($config, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        // No revertir: empresas existentes deben conservar el flag.
    }
};
