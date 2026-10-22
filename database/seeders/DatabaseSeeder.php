<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\ExtrasEmpresaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $extrasDemo = app(ExtrasEmpresaService::class)->defaultsParaTipo('bodega');
        $extrasDemo['flejes'] = true;
        $extrasDemo['categorias_jerarquicas'] = true;

        $empresa = Empresa::create([
            'nombre' => 'Bodega El Sol (Demo)',
            'codigo' => 'bodega-el-sol',
            'tipo_negocio' => 'bodega',
            'plan' => 'basico',
            'activo' => true,
            'prueba_hasta' => now()->addMonths(2)->toDateString(),
            'telefono' => '987654321',
            'configuracion' => [
                'terminal' => '01',
                'caja' => '01',
                'local' => 'Chiclayo Centro',
                'serie_boleta' => 'B001',
                'serie_factura' => 'F001',
                'onboarding_completado' => true,
                'onboarding_paso' => 4,
                'modulos_contratados' => ['pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'inventario', 'reportes', 'terminales', 'usuario_terminales'],
                'extras' => $extrasDemo,
                'whatsapp_resumen' => ['telefono' => '987654321'],
            ],
        ]);

        // Segunda empresa para verificar aislamiento de datos
        $empresa2 = Empresa::create([
            'nombre' => 'Minimarket Luna (Demo)',
            'codigo' => 'minimarket-luna',
            'tipo_negocio' => 'bodega',
            'plan' => 'basico',
            'activo' => true,
            'configuracion' => [
                'terminal' => '02',
                'caja' => '01',
                'local' => 'Lambayeque',
                'serie_boleta' => 'B001',
                'serie_factura' => 'F001',
                'onboarding_completado' => true,
                'onboarding_paso' => 4,
            ],
        ]);

        Usuario::create([
            'empresa_id' => null,
            'nombre' => 'Super Admin',
            'usuario' => 'super',
            'email' => 'super@ventapos.local',
            'password' => Hash::make('superadmin123'),
            'rol' => 'super_admin',
            'activo' => true,
        ]);

        Usuario::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Administrador',
            'usuario' => 'admin',
            'email' => 'admin@ventapos.local',
            'password' => Hash::make('admin123'),
            'rol' => 'dueno',
            'activo' => true,
        ]);

        Usuario::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Cajero Demo',
            'usuario' => 'cajero',
            'email' => 'cajero@ventapos.local',
            'password' => Hash::make('cajero123'),
            'rol' => 'cajero',
            'activo' => true,
        ]);

        Usuario::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Supervisor Demo',
            'usuario' => 'supervisor',
            'email' => 'supervisor@ventapos.local',
            'password' => Hash::make('super123'),
            'rol' => 'supervisor',
            'activo' => true,
        ]);

        Usuario::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Digitador Demo',
            'usuario' => 'digitador',
            'email' => 'digitador@ventapos.local',
            'password' => Hash::make('digit123'),
            'rol' => 'digitador',
            'activo' => true,
        ]);

        Usuario::create([
            'empresa_id' => $empresa2->id,
            'nombre' => 'Admin Luna',
            'usuario' => 'adminluna',
            'email' => 'admin@luna.local',
            'password' => Hash::make('luna123'),
            'rol' => 'dueno',
            'activo' => true,
        ]);

        $catLuna = Categoria::create([
            'empresa_id' => $empresa2->id,
            'nombre' => 'GENERAL',
            'orden' => 0,
        ]);

        Producto::create([
            'empresa_id' => $empresa2->id,
            'categoria_id' => $catLuna->id,
            'sku' => 'LUNA001',
            'codigo_barra' => '7759999001001',
            'nombre' => 'PRODUCTO SOLO LUNA',
            'precio_venta' => 9.99,
            'costo_compra' => 7.00,
            'stock_disponible' => 10,
            'activo' => true,
        ]);

        $this->call(ProductosDemoSeeder::class);
        $this->call(SeccionKSeeder::class);
    }
}
