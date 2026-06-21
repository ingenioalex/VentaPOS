<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::where('codigo', 'bodega-el-sol')->first();
        if (! $empresa) {
            return;
        }

        if (Producto::withoutGlobalScopes()->where('empresa_id', $empresa->id)->count() >= 36) {
            return;
        }

        $categorias = [
            'Bebidas' => 0, 'Abarrotes' => 1, 'Limpieza' => 2, 'Golosinas' => 3,
            'Lácteos' => 4, 'Enlatados' => 5, 'Higiene' => 6, 'Snacks' => 7,
        ];

        $catIds = [];
        foreach ($categorias as $nombre => $orden) {
            $catIds[$nombre] = Categoria::withoutGlobalScopes()->firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => $nombre],
                ['orden' => $orden]
            )->id;
        }

        $productos = [
            ['nombre' => 'AGUA SAN LUIS 625ML', 'barra' => '7751234001012', 'precio' => 1.50, 'stock' => 48, 'cat' => 'Bebidas', 'marca' => 'SAN LUIS'],
            ['nombre' => 'INCA KOLA 1.5L', 'barra' => '7751234002023', 'precio' => 6.50, 'stock' => 24, 'cat' => 'Bebidas', 'marca' => 'INCA KOLA'],
            ['nombre' => 'COCA COLA 1.5L', 'barra' => '7751234002024', 'precio' => 6.80, 'stock' => 20, 'cat' => 'Bebidas', 'marca' => 'COCA COLA'],
            ['nombre' => 'KR 2.5L', 'barra' => '7751234002025', 'precio' => 5.50, 'stock' => 18, 'cat' => 'Bebidas', 'marca' => 'KR'],
            ['nombre' => 'PILSEN 630ML', 'barra' => '7751234002026', 'precio' => 5.00, 'stock' => 36, 'cat' => 'Bebidas', 'marca' => 'PILSEN'],
            ['nombre' => 'ARROZ COSTEÑO 1KG', 'barra' => '7751234003034', 'precio' => 4.20, 'stock' => 30, 'cat' => 'Abarrotes', 'marca' => 'COSTEÑO'],
            ['nombre' => 'ACEITE PRIMOR 1L', 'barra' => '7751234004045', 'precio' => 9.90, 'stock' => 18, 'cat' => 'Abarrotes', 'marca' => 'PRIMOR'],
            ['nombre' => 'AZUCAR RUBIO 1KG', 'barra' => '7751234003035', 'precio' => 3.80, 'stock' => 25, 'cat' => 'Abarrotes', 'marca' => 'CARTAVIO'],
            ['nombre' => 'FIDEOS DON VITTORIO 500G', 'barra' => '7751234003036', 'precio' => 2.50, 'stock' => 40, 'cat' => 'Abarrotes', 'marca' => 'DON VITTORIO'],
            ['nombre' => 'SAL MARAS 1KG', 'barra' => '7751234003037', 'precio' => 1.80, 'stock' => 35, 'cat' => 'Abarrotes', 'marca' => 'MARAS'],
            ['nombre' => 'DETERGENTE OPAL 1KG', 'barra' => '7751234005056', 'precio' => 12.50, 'stock' => 15, 'cat' => 'Limpieza', 'marca' => 'OPAL'],
            ['nombre' => 'JABON PROTEX 110G', 'barra' => '7751234005057', 'precio' => 2.20, 'stock' => 30, 'cat' => 'Limpieza', 'marca' => 'PROTEX'],
            ['nombre' => 'CLORO SAPOLIO 1L', 'barra' => '7751234005058', 'precio' => 4.50, 'stock' => 12, 'cat' => 'Limpieza', 'marca' => 'SAPOLIO'],
            ['nombre' => 'LIMPIAVIDRIOS SAPOLIO', 'barra' => '7751234005059', 'precio' => 5.90, 'stock' => 10, 'cat' => 'Limpieza', 'marca' => 'SAPOLIO'],
            ['nombre' => 'GALLETA SODA FIELD 6U', 'barra' => '7751234006067', 'precio' => 3.00, 'stock' => 40, 'cat' => 'Golosinas', 'marca' => 'FIELD'],
            ['nombre' => 'CHOCOLATE Sublime 38G', 'barra' => '7751234006068', 'precio' => 2.50, 'stock' => 50, 'cat' => 'Golosinas', 'marca' => 'SUBLIME'],
            ['nombre' => 'MENTITAS INTI 12U', 'barra' => '7751234006069', 'precio' => 1.00, 'stock' => 60, 'cat' => 'Golosinas', 'marca' => 'INTI'],
            ['nombre' => 'CARAMELO FRUGOS 10U', 'barra' => '7751234006070', 'precio' => 1.20, 'stock' => 45, 'cat' => 'Golosinas', 'marca' => 'FRUGOS'],
            ['nombre' => 'LECHE GLORIA AZUL 390G', 'barra' => '7751234007071', 'precio' => 4.80, 'stock' => 28, 'cat' => 'Lácteos', 'marca' => 'GLORIA'],
            ['nombre' => 'LECHE IDEAL EVAP 410G', 'barra' => '7751234007072', 'precio' => 5.20, 'stock' => 22, 'cat' => 'Lácteos', 'marca' => 'IDEAL'],
            ['nombre' => 'YOGURT GLORIA FRESA 1KG', 'barra' => '7751234007073', 'precio' => 8.50, 'stock' => 8, 'cat' => 'Lácteos', 'marca' => 'GLORIA'],
            ['nombre' => 'MANTEQUILLA GLORIA 200G', 'barra' => '7751234007074', 'precio' => 6.90, 'stock' => 14, 'cat' => 'Lácteos', 'marca' => 'GLORIA'],
            ['nombre' => 'ATUN FLORIDA LOMO 170G', 'barra' => '7751234008075', 'precio' => 6.50, 'stock' => 32, 'cat' => 'Enlatados', 'marca' => 'FLORIDA'],
            ['nombre' => 'LECHE CONDENSADA GLORIA', 'barra' => '7751234008076', 'precio' => 5.80, 'stock' => 20, 'cat' => 'Enlatados', 'marca' => 'GLORIA'],
            ['nombre' => 'POROTOS RIMAC 500G', 'barra' => '7751234008077', 'precio' => 3.50, 'stock' => 18, 'cat' => 'Enlatados', 'marca' => 'RIMAC'],
            ['nombre' => 'DURAZNOS EN ALMIBAR', 'barra' => '7751234008078', 'precio' => 4.20, 'stock' => 15, 'cat' => 'Enlatados', 'marca' => 'FRUTISUR'],
            ['nombre' => 'PAPEL HIGIENICO ELITE 4U', 'barra' => '7751234009079', 'precio' => 8.90, 'stock' => 20, 'cat' => 'Higiene', 'marca' => 'ELITE'],
            ['nombre' => 'SHAMPOO SAVITAL 380ML', 'barra' => '7751234009080', 'precio' => 11.50, 'stock' => 12, 'cat' => 'Higiene', 'marca' => 'SAVITAL'],
            ['nombre' => 'PASTA DENTAL COLGATE', 'barra' => '7751234009081', 'precio' => 4.50, 'stock' => 25, 'cat' => 'Higiene', 'marca' => 'COLGATE'],
            ['nombre' => 'DESODORANTE REXONA 50ML', 'barra' => '7751234009082', 'precio' => 9.90, 'stock' => 10, 'cat' => 'Higiene', 'marca' => 'REXONA'],
            ['nombre' => 'PAPAS LAYS CLASICAS 140G', 'barra' => '7751234010083', 'precio' => 4.50, 'stock' => 30, 'cat' => 'Snacks', 'marca' => 'LAYS'],
            ['nombre' => 'CHIZITOS DONOFRIO 100G', 'barra' => '7751234010084', 'precio' => 2.80, 'stock' => 35, 'cat' => 'Snacks', 'marca' => 'DONOFRIO'],
            ['nombre' => 'MANI SALADO COSTA 150G', 'barra' => '7751234010085', 'precio' => 3.20, 'stock' => 22, 'cat' => 'Snacks', 'marca' => 'COSTA'],
            ['nombre' => 'HUEVOS ROJOS DOCENA', 'barra' => '7751234010086', 'precio' => 12.00, 'stock' => 15, 'cat' => 'Abarrotes', 'marca' => 'FRESCO', 'um' => 'DOC'],
            ['nombre' => 'PAN FRANCES UNIDAD', 'barra' => '7751234010087', 'precio' => 0.50, 'stock' => 100, 'cat' => 'Snacks', 'marca' => 'PANADERIA', 'um' => 'UND'],
            ['nombre' => 'HIELO BOLSA 2KG', 'barra' => '7751234010088', 'precio' => 3.00, 'stock' => 8, 'cat' => 'Bebidas', 'marca' => 'HIELO', 'um' => 'BOL'],
        ];

        $existentes = Producto::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->pluck('codigo_barra')
            ->flip();

        $n = Producto::withoutGlobalScopes()->where('empresa_id', $empresa->id)->count();

        foreach ($productos as $p) {
            if ($existentes->has($p['barra'])) {
                continue;
            }
            $n++;
            Producto::withoutGlobalScopes()->create([
                'empresa_id' => $empresa->id,
                'categoria_id' => $catIds[$p['cat']],
                'sku' => 'SKU'.str_pad((string) $n, 5, '0', STR_PAD_LEFT),
                'codigo_barra' => $p['barra'],
                'nombre' => $p['nombre'],
                'precio_venta' => $p['precio'],
                'costo_compra' => round($p['precio'] * 0.72, 2),
                'stock_disponible' => $p['stock'],
                'stock_minimo' => 5,
                'unidad_medida' => $p['um'] ?? 'UND',
                'campo1' => $p['marca'],
                'activo' => true,
            ]);
        }
    }
}
