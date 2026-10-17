<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeccionKSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario administrador por defecto
        DB::table('seccionk_usuarios')->insert([
            'usuario' => 'admin',
            'password' => Hash::make('seccionk2026'),
            'nombre' => 'Administrador SecciónK',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Cuotas mensuales de Octubre 2026 a Diciembre 2027 (15 meses)
        $meses = [
            ['mes' => 'Octubre', 'anio' => 2026],
            ['mes' => 'Noviembre', 'anio' => 2026],
            ['mes' => 'Diciembre', 'anio' => 2026],
            ['mes' => 'Enero', 'anio' => 2027],
            ['mes' => 'Febrero', 'anio' => 2027],
            ['mes' => 'Marzo', 'anio' => 2027],
            ['mes' => 'Abril', 'anio' => 2027],
            ['mes' => 'Mayo', 'anio' => 2027],
            ['mes' => 'Junio', 'anio' => 2027],
            ['mes' => 'Julio', 'anio' => 2027],
            ['mes' => 'Agosto', 'anio' => 2027],
            ['mes' => 'Septiembre', 'anio' => 2027],
            ['mes' => 'Octubre', 'anio' => 2027],
            ['mes' => 'Noviembre', 'anio' => 2027],
            ['mes' => 'Diciembre', 'anio' => 2027],
        ];

        foreach ($meses as $index => $mes) {
            $primerDia = \Carbon\Carbon::create($mes['anio'], $index + 10, 1);
            if ($primerDia->month > 12) {
                $primerDia = \Carbon\Carbon::create($mes['anio'], ($index + 10) % 12 ?: 12, 1);
            }
            
            DB::table('seccionk_eventos')->insert([
                'nombre' => "Cuota Mensual {$mes['mes']} {$mes['anio']}",
                'descripcion' => "Aporte mensual correspondiente a {$mes['mes']} {$mes['anio']}",
                'fecha' => $primerDia->format('Y-m-d'),
                'monto' => 50.00,
                'tipo' => 'cuota_mensual',
                'mes_cuota' => "{$mes['mes']} {$mes['anio']}",
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Promociones de ejemplo (miembros de la sección K 2001)
        $promociones = [
            [
                'dni' => '12345678',
                'nombre' => 'Juan Carlos',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'López',
                'direccion' => 'Av. Los Álamos 123, San Isidro',
                'celular' => '987654321',
                'participa_cuotas' => true,
                'referencias' => 'Sección A 2000, Sección B 2002',
            ],
            [
                'dni' => '23456789',
                'nombre' => 'María Elena',
                'apellido_paterno' => 'Rodríguez',
                'apellido_materno' => 'Sánchez',
                'direccion' => 'Jr. Las Flores 456, Miraflores',
                'celular' => '987654322',
                'participa_cuotas' => true,
                'referencias' => null,
            ],
            [
                'dni' => '34567890',
                'nombre' => 'Pedro Miguel',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Torres',
                'direccion' => 'Calle Los Pinos 789, Surco',
                'celular' => '987654323',
                'participa_cuotas' => false,
                'referencias' => 'Sección K 2000',
            ],
        ];

        foreach ($promociones as $promo) {
            DB::table('seccionk_promociones')->insert(array_merge($promo, [
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Evento de ejemplo
        DB::table('seccionk_eventos')->insert([
            'nombre' => 'Partido de Fútbol',
            'descripcion' => 'Encuentro deportivo de confraternidad',
            'fecha' => '2026-09-21',
            'monto' => 5.00,
            'tipo' => 'evento',
            'mes_cuota' => null,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
