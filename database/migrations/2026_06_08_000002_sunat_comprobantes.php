<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->enum('sunat_estado', [
                'omitido', 'pendiente', 'simulado', 'aceptado', 'rechazado', 'error',
            ])->default('omitido')->after('estado');
            $table->string('sunat_codigo_respuesta', 10)->nullable()->after('sunat_estado');
            $table->string('sunat_mensaje')->nullable()->after('sunat_codigo_respuesta');
            $table->string('sunat_hash', 64)->nullable()->after('sunat_mensaje');
            $table->string('sunat_xml', 255)->nullable()->after('sunat_hash');
            $table->timestamp('sunat_enviado_en')->nullable()->after('sunat_xml');
        });
    }

    public function down(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropColumn([
                'sunat_estado', 'sunat_codigo_respuesta', 'sunat_mensaje',
                'sunat_hash', 'sunat_xml', 'sunat_enviado_en',
            ]);
        });
    }
};
