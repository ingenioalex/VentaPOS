<?php

namespace App\Services;

use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDatasetService
{
    public function __construct(private PermisoService $permisos) {}

    public function seedMinimal(): void
    {
        DB::transaction(function () {
            $empresa = Empresa::create([
                'nombre' => 'Negocio Demo',
                'codigo' => 'negocio-demo',
                'tipo_negocio' => 'bodega',
                'ruc' => '20123456789',
                'telefono' => '974123456',
                'direccion' => 'Av. Principal 123, Chiclayo',
                'plan' => 'basico',
                'activo' => true,
                'configuracion' => [
                    'serie_boleta' => 'B001',
                    'serie_factura' => 'F001',
                    'local' => 'Local principal',
                    'caja' => '01',
                    'terminal' => '01',
                    'onboarding_completado' => true,
                    'onboarding_paso' => 4,
                    'modulos_contratados' => $this->permisos->modulosPorPlan('basico'),
                    'extras' => [],
                    'cupos_usuarios' => ['bonificados' => 0, 'extras_pago' => 0, 'nota_interna' => null],
                ],
            ]);

            $this->crearSuperAdmin();
            $this->crearUsuariosBase($empresa->id);

            $catId = DB::table('categorias')->insertGetId([
                'empresa_id' => $empresa->id,
                'nombre' => 'GENERAL',
                'orden' => 0,
                'creado_en' => now(),
            ]);

            $productos = [
                ['Arroz extra 1kg', '7751000001001', 5.50, 4.20, 80],
                ['Aceite vegetal 1L', '7751000001002', 9.90, 7.50, 45],
                ['Azúcar rubia 1kg', '7751000001003', 4.80, 3.60, 60],
                ['Leche evaporada', '7751000001004', 3.50, 2.80, 100],
                ['Galletas surtidas', '7751000001005', 2.50, 1.90, 120],
            ];

            foreach ($productos as $i => [$nombre, $barra, $precio, $costo, $stock]) {
                DB::table('productos')->insert([
                    'empresa_id' => $empresa->id,
                    'categoria_id' => $catId,
                    'sku' => 'DEMO'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'codigo_barra' => $barra,
                    'nombre' => $nombre,
                    'precio_venta' => $precio,
                    'costo_compra' => $costo,
                    'stock_disponible' => $stock,
                    'stock_minimo' => 10,
                    'activo' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            }

            $terminalId = $this->crearTerminal($empresa->id, '01', 'Caja principal');
            $cajeroId = DB::table('usuarios')->where('empresa_id', $empresa->id)->where('rol', 'cajero')->value('id');
            if ($cajeroId) {
                DB::table('usuario_terminal')->insert([
                    'usuario_id' => $cajeroId,
                    'terminal_id' => $terminalId,
                    'es_defecto' => true,
                ]);
            }
        });
    }

    public function seedShowcase(): void
    {
        DB::transaction(function () {
            $modulosPro = $this->permisos->modulosPorPlan('pro');
            $extras = app(ExtrasEmpresaService::class)->defaultsParaTipo('bodega');
            $extras['whatsapp_resumen'] = true;
            $extras['lotes_vencimiento'] = true;

            $empresa = Empresa::create([
                'nombre' => 'Distribuidora San Martín SAC',
                'codigo' => 'san-martin',
                'tipo_negocio' => 'bodega',
                'ruc' => '20601234567',
                'telefono' => '974888777',
                'direccion' => 'Av. José Balta 1450, Chiclayo',
                'plan' => 'pro',
                'activo' => true,
                'configuracion' => [
                    'serie_boleta' => 'B001',
                    'serie_factura' => 'F001',
                    'local' => 'Sede Chiclayo',
                    'caja' => '01',
                    'terminal' => '01',
                    'onboarding_completado' => true,
                    'onboarding_paso' => 4,
                    'modulos_contratados' => $modulosPro,
                    'extras' => $extras,
                    'whatsapp_resumen' => ['telefono' => '974888777'],
                    'sunat' => ['activo' => true, 'modo' => 'simulacion', 'usuario_sol' => 'MODODEMO', 'clave_sol' => ''],
                    'cupos_usuarios' => ['bonificados' => 0, 'extras_pago' => 0, 'nota_interna' => 'Demo comercial Pro'],
                ],
            ]);

            $empresaId = $empresa->id;
            $this->crearSuperAdmin();

            $duenoId = $this->insertUsuario($empresaId, 'Carlos Mendoza', 'admin', 'admin@sanmartin.demo', 'dueno', 'admin123');
            $supervisorId = $this->insertUsuario($empresaId, 'Ana Torres', 'supervisor', 'supervisor@sanmartin.demo', 'supervisor', 'super123');
            $cajero1Id = $this->insertUsuario($empresaId, 'Luis Ramírez', 'cajero1', 'cajero1@sanmartin.demo', 'cajero', 'cajero123');
            $cajero2Id = $this->insertUsuario($empresaId, 'María Vargas', 'cajero2', 'cajero2@sanmartin.demo', 'cajero', 'cajero123');
            $digitadorId = $this->insertUsuario($empresaId, 'Pedro Díaz', 'digitador', 'digitador@sanmartin.demo', 'digitador', 'digit123');

            $terminal1 = $this->crearTerminal($empresaId, '01', 'Caja 1 - Frente');
            $terminal2 = $this->crearTerminal($empresaId, '02', 'Caja 2 - Abarrotes', '02');
            foreach ([$cajero1Id => $terminal1, $cajero2Id => $terminal2, $supervisorId => $terminal1] as $uid => $tid) {
                DB::table('usuario_terminal')->insert([
                    'usuario_id' => $uid,
                    'terminal_id' => $tid,
                    'es_defecto' => true,
                ]);
            }

            $categorias = $this->crearCategoriasShowcase($empresaId);
            $productos = $this->crearProductosShowcase($empresaId, $categorias);
            $clientes = $this->crearClientesShowcase($empresaId);
            $proveedores = $this->crearProveedoresShowcase($empresaId);

            $this->crearComprasShowcase($empresaId, $proveedores, $productos, $duenoId);
            $this->crearVentasShowcase(
                $empresaId,
                $productos,
                $clientes,
                [$cajero1Id, $cajero2Id, $supervisorId],
                [$terminal1, $terminal2],
            );
            $this->crearAjustesInventario($empresaId, $productos, $digitadorId);
            $this->crearCierresShowcase($empresaId, [$cajero1Id, $cajero2Id], [$terminal1, $terminal2], $supervisorId);
        });
    }

    private function crearSuperAdmin(): void
    {
        DB::table('usuarios')->insert([
            'empresa_id' => null,
            'nombre' => 'Super Admin',
            'usuario' => 'super',
            'email' => 'super@ventapos.local',
            'password' => Hash::make('superadmin123'),
            'rol' => 'super_admin',
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);
    }

    private function crearUsuariosBase(int $empresaId): void
    {
        $this->insertUsuario($empresaId, 'Administrador Demo', 'admin', 'admin@demo.local', 'dueno', 'admin123');
        $this->insertUsuario($empresaId, 'Cajero Demo', 'cajero', 'cajero@demo.local', 'cajero', 'cajero123');
    }

    private function insertUsuario(
        int $empresaId,
        string $nombre,
        string $usuario,
        string $email,
        string $rol,
        string $password,
    ): int {
        return (int) DB::table('usuarios')->insertGetId([
            'empresa_id' => $empresaId,
            'nombre' => $nombre,
            'usuario' => $usuario,
            'email' => $email,
            'password' => Hash::make($password),
            'rol' => $rol,
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);
    }

    private function crearTerminal(int $empresaId, string $codigo, string $nombre, string $caja = '01'): int
    {
        return (int) DB::table('terminales')->insertGetId([
            'empresa_id' => $empresaId,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'local' => 'Local principal',
            'caja' => $caja,
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);
    }

    /** @return array<string, int> */
    private function crearCategoriasShowcase(int $empresaId): array
    {
        $map = [];
        $grupos = [
            'Abarrotes' => ['Arroz y granos', 'Pastas', 'Harinas'],
            'Bebidas' => ['Gaseosas', 'Jugos', 'Agua'],
            'Limpieza' => ['Detergentes', 'Desinfectantes'],
            'Lácteos' => ['Leches', 'Yogurt'],
            'Snacks' => ['Galletas', 'Chocolates'],
            'Higiene' => ['Jabones', 'Papel higiénico'],
        ];

        $orden = 0;
        foreach ($grupos as $padre => $hijos) {
            $padreId = DB::table('categorias')->insertGetId([
                'empresa_id' => $empresaId,
                'parent_id' => null,
                'nombre' => $padre,
                'orden' => $orden++,
                'creado_en' => now(),
            ]);
            $map[$padre] = $padreId;
            foreach ($hijos as $hijo) {
                $map[$hijo] = DB::table('categorias')->insertGetId([
                    'empresa_id' => $empresaId,
                    'parent_id' => $padreId,
                    'nombre' => $hijo,
                    'orden' => $orden++,
                    'creado_en' => now(),
                ]);
            }
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $categorias
     * @return list<array{id: int, precio: float, costo: float, nombre: string}>
     */
    private function crearProductosShowcase(int $empresaId, array $categorias): array
    {
        $nombres = [
            'Arroz superior 1kg', 'Arroz costeño 1kg', 'Fideos spaghetti 500g', 'Fideos macarrones 500g',
            'Harina blanca 1kg', 'Harina integral 1kg', 'Azúcar rubia 1kg', 'Azúcar blanca 1kg',
            'Aceite vegetal 1L', 'Aceite oliva 500ml', 'Sal de mesa 1kg', 'Conservas atún 170g',
            'Conservas sardina', 'Leche entera 1L', 'Leche deslactosada 1L', 'Yogurt fresa 1L',
            'Queso fresco 500g', 'Mantequilla 200g', 'Coca Cola 1.5L', 'Inca Kola 1.5L',
            'Sprite 1.5L', 'Agua sin gas 2.5L', 'Jugo durazno 1L', 'Jugo piña 1L',
            'Galletas soda', 'Galletas vainilla', 'Chocolate tableta', 'Caramelos surtidos',
            'Detergente polvo 1kg', 'Detergente líquido 1L', 'Cloro 1L', 'Limpiavidrios 500ml',
            'Jabón líquido manos', 'Jabón de tocador', 'Papel higiénico 4 rollos', 'Toallas papel',
            'Shampoo 400ml', 'Acondicionador 400ml', 'Pasta dental', 'Cepillo dental',
            'Arroz integral 1kg', 'Menestra lenteja 500g', 'Menestra arveja 500g', 'Avena 500g',
            'Café molido 250g', 'Té surtido 25 sobres', 'Mayonesa 475g', 'Ketchup 500g',
            'Mostaza 250g', 'Salsa de tomate 200g', 'Panetón mini', 'Galletas chocolate',
            'Mermelada fresa', 'Miel de abeja 250g', 'Cereal hojuelas', 'Barra cereal',
            'Energizante 473ml', 'Agua gasificada 625ml', 'Refresco litro', 'Jugo concentrado',
            'Desodorante roll-on', 'Crema corporal', 'Rasuradora desechable', 'Pañuelos bolsillo',
            'Bolsa basura grande', 'Esponja cocina', 'Trapeador repuesto', 'Escoba plástico',
            'Fósforos caja', 'Velas pack', 'Pilas AA 4u', 'Linterna LED',
            'Arroz graneado 5kg', 'Aceite girasol 3L', 'Azúcar rubia 5kg', 'Harina 5kg',
            'Gaseosa 3L familiar', 'Agua 7L bidón', 'Detergente 3kg', 'Cloro galón',
            'Papel higiénico 12 rollos', 'Servilletas 200u', 'Jabón antibacterial', 'Alcohol gel 500ml',
            'Leche condensada', 'Crema chantilly', 'Gelatina sabor', 'Flan instantáneo',
            'Sopas instantáneas', 'Caldo en cubos', 'Sazonador completo', 'Pimienta molida',
            'Orégano 50g', 'Comino 50g', 'Ají panca 100g', 'Ajo molido',
            'Vinagre blanco 1L', 'Vinagre manzana', 'Sillao 500ml', 'Aceite ajonjolí',
            'Atún aceite 2u pack', 'Mejillones lata', 'Pechuga atún', 'Caballa en aceite',
            'Leche evaporada 4 pack', 'Leche gloria 390g', 'Yogurt griego', 'Queso parmesano',
        ];

        $catKeys = array_keys($categorias);
        $productos = [];
        foreach (array_slice($nombres, 0, 100) as $i => $nombre) {
            $catId = $categorias[$catKeys[$i % count($catKeys)]];
            $costo = round(1.5 + ($i % 40) * 0.35, 2);
            $precio = round($costo * (1.25 + ($i % 5) * 0.05), 2);
            $id = DB::table('productos')->insertGetId([
                'empresa_id' => $empresaId,
                'categoria_id' => $catId,
                'sku' => 'SM'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'codigo_barra' => '7752000'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'nombre' => $nombre,
                'precio_venta' => $precio,
                'costo_compra' => $costo,
                'stock_disponible' => 50 + ($i % 30) * 5,
                'stock_minimo' => 8 + ($i % 4) * 2,
                'control_lote' => $i % 17 === 0,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
            $productos[] = ['id' => $id, 'precio' => $precio, 'costo' => $costo, 'nombre' => $nombre];
        }

        return $productos;
    }

    /** @return array{dni: list<int>, ruc: list<int>} */
    private function crearClientesShowcase(int $empresaId): array
    {
        $clientesDni = [];
        $clientesRuc = [];
        $dniList = [
            ['45678912', 'Juan Pérez García'],
            ['42345678', 'Rosa López Díaz'],
            ['40123456', 'Miguel Ángel Ruiz'],
            ['43456789', 'Carmen Flores Vega'],
            ['41234567', 'José Luis Castro'],
            ['44567890', 'Lucía Mendoza'],
            ['46789012', 'Andrés Salazar'],
            ['47890123', 'Patricia Ríos'],
            ['48901234', 'Fernando Gutiérrez'],
            ['49012345', 'Sofía Herrera'],
        ];
        foreach ($dniList as [$doc, $nombre]) {
            $clientesDni[] = DB::table('clientes')->insertGetId([
                'empresa_id' => $empresaId,
                'tipo_documento' => 'dni',
                'numero_documento' => $doc,
                'nombre' => $nombre,
                'telefono' => '9'.substr($doc, 0, 8),
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        }

        $rucList = [
            ['20512345678', 'Comercial El Trébol EIRL'],
            ['20456789012', 'Restaurante La Casona SAC'],
            ['20678901234', 'Hotel Costa Norte SAC'],
            ['20123456789', 'Farmacia Salud Total SAC'],
            ['20345678901', 'Minimarket Los Olivos EIRL'],
        ];
        foreach ($rucList as [$doc, $nombre]) {
            $clientesRuc[] = DB::table('clientes')->insertGetId([
                'empresa_id' => $empresaId,
                'tipo_documento' => 'ruc',
                'numero_documento' => $doc,
                'nombre' => $nombre,
                'telefono' => '074'.random_int(100000, 999999),
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        }

        return ['dni' => $clientesDni, 'ruc' => $clientesRuc];
    }

    /** @return list<int> */
    private function crearProveedoresShowcase(int $empresaId): array
    {
        $proveedores = [
            ['20100070970', 'SUPERMERCADOS PERUANOS SA'],
            ['20131312955', 'GLORIA SA'],
            ['20517030301', 'ALICORP SAA'],
            ['20100055237', 'CENCOSUD RETAIL PERU SA'],
            ['20498189658', 'DISTRIBUIDORA NORTE EIRL'],
        ];
        $ids = [];
        foreach ($proveedores as [$ruc, $razon]) {
            $ids[] = DB::table('proveedores')->insertGetId([
                'empresa_id' => $empresaId,
                'tipo_documento' => 'ruc',
                'numero_documento' => $ruc,
                'razon_social' => $razon,
                'telefono' => '014'.random_int(100000, 999999),
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        }

        return $ids;
    }

    /**
     * @param  list<int>  $proveedores
     * @param  list<array{id: int, precio: float, costo: float, nombre: string}>  $productos
     */
    private function crearComprasShowcase(int $empresaId, array $proveedores, array $productos, int $usuarioId): void
    {
        $fechas = [
            '2026-01-08', '2026-02-05', '2026-03-10', '2026-04-07',
            '2026-05-12', '2026-06-03', '2026-06-18',
        ];

        foreach ($fechas as $idx => $fecha) {
            $proveedorId = $proveedores[$idx % count($proveedores)];
            $items = array_slice($productos, ($idx * 11) % 80, 8);
            $subtotal = 0.0;
            $detalles = [];
            foreach ($items as $p) {
                $cant = random_int(10, 40);
                $linea = round($cant * $p['costo'], 2);
                $subtotal += $linea;
                $detalles[] = [$p, $cant, $linea];
            }
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);

            $compraId = DB::table('compras')->insertGetId([
                'empresa_id' => $empresaId,
                'proveedor_id' => $proveedorId,
                'usuario_id' => $usuarioId,
                'tipo_documento' => 'factura',
                'serie' => 'F001',
                'numero_documento' => str_pad((string) (1000 + $idx), 8, '0', STR_PAD_LEFT),
                'fecha_emision' => $fecha,
                'fecha_recepcion' => $fecha,
                'subtotal' => $subtotal,
                'valor_igv' => $igv,
                'total' => $total,
                'estado' => 'recibido',
                'recibido_en' => $fecha.' 10:30:00',
                'creado_en' => $fecha.' 09:00:00',
                'actualizado_en' => $fecha.' 10:30:00',
            ]);

            foreach ($detalles as [$p, $cant, $linea]) {
                DB::table('compra_detalle')->insert([
                    'compra_id' => $compraId,
                    'producto_id' => $p['id'],
                    'descripcion' => $p['nombre'],
                    'cantidad' => $cant,
                    'costo_unitario' => $p['costo'],
                    'subtotal' => $linea,
                    'creado_en' => $fecha.' 10:30:00',
                ]);
                DB::table('inventario_movimiento')->insert([
                    'empresa_id' => $empresaId,
                    'producto_id' => $p['id'],
                    'usuario_id' => $usuarioId,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $cant,
                    'stock_anterior' => 0,
                    'stock_nuevo' => $cant,
                    'referencia' => 'Compra #'.$compraId,
                    'compra_id' => $compraId,
                    'creado_en' => $fecha.' 10:30:00',
                ]);
            }
        }
    }

    /**
     * @param  list<array{id: int, precio: float, costo: float, nombre: string}>  $productos
     * @param  array{dni: list<int>, ruc: list<int>}  $clientes
     * @param  list<int>  $cajeros
     * @param  list<int>  $terminales
     */
    private function crearVentasShowcase(
        int $empresaId,
        array $productos,
        array $clientes,
        array $cajeros,
        array $terminales,
    ): void {
        $inicio = Carbon::parse('2026-01-01 08:00:00');
        $fin = Carbon::parse('2026-06-30 20:00:00');
        $totalVentas = 3600;
        $numBoleta = 0;
        $numFactura = 0;
        $numNota = 0;
        $comprobantes = [];
        $detalles = [];
        $movimientos = [];
        $nextComprobanteId = 1;

        $medios = ['efectivo', 'yape', 'plin', 'tarjeta', 'mixto'];

        for ($i = 0; $i < $totalVentas; $i++) {
            $fraccion = $totalVentas > 1 ? $i / ($totalVentas - 1) : 0;
            $emitido = $inicio->copy()->addSeconds((int) ($inicio->diffInSeconds($fin) * $fraccion));
            $emitido->addMinutes(random_int(0, 45))->addSeconds(random_int(0, 59));

            $roll = random_int(1, 100);
            if ($roll <= 72) {
                $tipoDoc = 'boleta';
                $serie = 'B001';
                $numBoleta++;
                $numero = $numBoleta;
                $clienteId = $clientes['dni'][array_rand($clientes['dni'])];
            } elseif ($roll <= 92) {
                $tipoDoc = 'factura';
                $serie = 'F001';
                $numFactura++;
                $numero = $numFactura;
                $clienteId = $clientes['ruc'][array_rand($clientes['ruc'])];
            } else {
                $tipoDoc = 'nota';
                $serie = 'NV01';
                $numNota++;
                $numero = $numNota;
                $clienteId = null;
            }

            $lineas = random_int(1, 5);
            $subtotal = 0.0;
            $lineasDetalle = [];
            for ($l = 0; $l < $lineas; $l++) {
                $prod = $productos[array_rand($productos)];
                $cant = $tipoDoc === 'nota' ? random_int(1, 2) : random_int(1, 4);
                $precio = $prod['precio'];
                $lineaSub = round($cant * $precio, 2);
                $subtotal += $lineaSub;
                $lineasDetalle[] = [$prod, $cant, $precio, $lineaSub];
            }

            $descuento = random_int(1, 20) === 1 ? round($subtotal * 0.05, 2) : 0;
            $base = round($subtotal - $descuento, 2);
            $igv = round($base - round($base / 1.18, 2), 2);
            $sub = round($base - $igv, 2);
            $total = $base;
            $medio = $medios[array_rand($medios)];

            $estado = random_int(1, 100) <= 3 ? 'anulado' : 'emitido';
            $sunat = $this->sunatDemo($tipoDoc, $serie, $numero, $total, $emitido, $estado);

            $usuarioId = $cajeros[array_rand($cajeros)];
            $terminalId = $terminales[array_rand($terminales)];
            $comprobanteId = $nextComprobanteId++;

            $comprobantes[] = [
                'id' => $comprobanteId,
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'terminal_id' => $terminalId,
                'cliente_id' => $clienteId,
                'numero_interno' => sprintf('%s-%06d', $serie, $numero),
                'tipo_documento' => $tipoDoc,
                'serie' => $serie,
                'numero' => $numero,
                'subtotal' => $sub,
                'valor_igv' => $igv,
                'descuento' => $descuento,
                'total' => $total,
                'monto_recibido' => $medio === 'efectivo' ? ceil($total) : $total,
                'vuelto' => $medio === 'efectivo' ? round(ceil($total) - $total, 2) : 0,
                'medio_pago' => $medio,
                'estado' => $estado,
                'observaciones' => $medio === 'mixto' ? json_encode(['pagos' => [
                    ['medio' => 'efectivo', 'monto' => round($total * 0.6, 2)],
                    ['medio' => 'yape', 'monto' => round($total * 0.4, 2)],
                ]]) : null,
                'emitido_en' => $emitido->format('Y-m-d H:i:s'),
                'sunat_estado' => $sunat['estado'],
                'sunat_codigo_respuesta' => $sunat['codigo'],
                'sunat_mensaje' => $sunat['mensaje'],
                'sunat_hash' => $sunat['hash'],
                'sunat_xml' => $sunat['xml'],
                'sunat_enviado_en' => $sunat['enviado'],
                'creado_en' => $emitido->format('Y-m-d H:i:s'),
            ];

            foreach ($lineasDetalle as [$prod, $cant, $precio, $lineaSub]) {
                $detalles[] = [
                    'comprobante_id' => $comprobanteId,
                    'producto_id' => $prod['id'],
                    'descripcion_producto' => $prod['nombre'],
                    'cantidad' => $cant,
                    'precio_unitario' => $precio,
                    'descuento_pct' => 0,
                    'subtotal' => $lineaSub,
                    'creado_en' => $emitido->format('Y-m-d H:i:s'),
                ];
                if ($estado === 'emitido') {
                    $movimientos[] = [
                        'empresa_id' => $empresaId,
                        'producto_id' => $prod['id'],
                        'usuario_id' => $usuarioId,
                        'tipo_movimiento' => 'venta',
                        'cantidad' => $cant,
                        'stock_anterior' => 0,
                        'stock_nuevo' => 0,
                        'referencia' => sprintf('%s-%06d', $serie, $numero),
                        'creado_en' => $emitido->format('Y-m-d H:i:s'),
                    ];
                }
            }

            if (count($comprobantes) >= 150) {
                $this->insertarVentasBulk($comprobantes, $detalles, $movimientos);
                $comprobantes = [];
                $detalles = [];
                $movimientos = [];
            }
        }

        if ($comprobantes !== []) {
            $this->insertarVentasBulk($comprobantes, $detalles, $movimientos);
        }
    }

    /** @param list<array<string, mixed>> $comprobantes */
    private function insertarVentasBulk(array $comprobantes, array $detalles, array $movimientos): void
    {
        foreach (array_chunk($comprobantes, 150) as $chunk) {
            DB::table('comprobantes')->insert($chunk);
        }

        foreach (array_chunk($detalles, 300) as $chunk) {
            DB::table('detalle_comprobante')->insert($chunk);
        }
        foreach (array_chunk($movimientos, 300) as $chunk) {
            DB::table('inventario_movimiento')->insert($chunk);
        }
    }

    /** @return array{estado: string, codigo: ?string, mensaje: ?string, hash: ?string, xml: ?string, enviado: ?string} */
    private function sunatDemo(
        string $tipoDoc,
        string $serie,
        int $numero,
        float $total,
        Carbon $emitido,
        string $estadoVenta,
    ): array {
        if ($tipoDoc === 'nota' || $estadoVenta === 'anulado') {
            return ['estado' => 'omitido', 'codigo' => null, 'mensaje' => null, 'hash' => null, 'xml' => null, 'enviado' => null];
        }

        $hash = hash('sha256', $serie.'-'.$numero.'-'.$total.'-'.$emitido->timestamp);
        $xml = 'sunat/demo/'.Str::slug($serie).'-'.str_pad((string) $numero, 8, '0', STR_PAD_LEFT).'.xml';
        $roll = random_int(1, 100);

        if ($roll <= 88) {
            return [
                'estado' => 'simulado',
                'codigo' => '0',
                'mensaje' => 'Aceptado en modo simulación (sin envío a SUNAT).',
                'hash' => $hash,
                'xml' => $xml,
                'enviado' => $emitido->format('Y-m-d H:i:s'),
            ];
        }
        if ($roll <= 95) {
            return [
                'estado' => 'aceptado',
                'codigo' => '0',
                'mensaje' => 'La Factura/Boleta ha sido aceptada (demo producción).',
                'hash' => $hash,
                'xml' => $xml,
                'enviado' => $emitido->format('Y-m-d H:i:s'),
            ];
        }
        if ($roll <= 98) {
            return [
                'estado' => 'rechazado',
                'codigo' => '2324',
                'mensaje' => 'El documento ya fue registrado (demo error).',
                'hash' => $hash,
                'xml' => $xml,
                'enviado' => $emitido->format('Y-m-d H:i:s'),
            ];
        }

        return [
            'estado' => 'error',
            'codigo' => 'NET',
            'mensaje' => 'Timeout de conexión OSE (demo).',
            'hash' => $hash,
            'xml' => $xml,
            'enviado' => $emitido->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  list<array{id: int, precio: float, costo: float, nombre: string}>  $productos
     */
    private function crearAjustesInventario(int $empresaId, array $productos, int $usuarioId): void
    {
        $fechas = ['2026-02-15', '2026-04-20', '2026-06-10'];
        foreach ($fechas as $idx => $fecha) {
            $prod = $productos[($idx * 13) % count($productos)];
            DB::table('inventario_movimiento')->insert([
                'empresa_id' => $empresaId,
                'producto_id' => $prod['id'],
                'usuario_id' => $usuarioId,
                'tipo_movimiento' => 'ajuste',
                'cantidad' => 0,
                'stock_anterior' => 40,
                'stock_nuevo' => 38,
                'referencia' => 'Conteo físico mensual',
                'creado_en' => $fecha.' 18:00:00',
            ]);
        }
    }

    /** @param list<int> $cajeros @param list<int> $terminales */
    private function crearCierresShowcase(int $empresaId, array $cajeros, array $terminales, int $supervisorId): void
    {
        $fecha = Carbon::parse('2026-01-05');
        while ($fecha->lte('2026-06-28')) {
            if ($fecha->dayOfWeek === Carbon::SUNDAY) {
                $fecha->addDay();
                continue;
            }
            foreach ($cajeros as $i => $cajeroId) {
                $inicio = $fecha->copy()->setTime(8, 0);
                $fin = $fecha->copy()->setTime(18, 0);
                $ventas = random_int(15, 35);
                $total = round($ventas * random_int(28, 65), 2);
                DB::table('cierres_caja')->insert([
                    'empresa_id' => $empresaId,
                    'usuario_id' => $cajeroId,
                    'supervisor_usuario_id' => $supervisorId,
                    'terminal_id' => $terminales[$i % count($terminales)],
                    'tipo_cierre' => $fecha->dayOfWeek === Carbon::SATURDAY ? 'Z' : 'X',
                    'terminal' => '0'.($i + 1),
                    'caja' => '0'.($i + 1),
                    'fecha_inicio' => $inicio->format('Y-m-d H:i:s'),
                    'fecha_fin' => $fin->format('Y-m-d H:i:s'),
                    'cantidad_ventas' => $ventas,
                    'total_ventas' => $total,
                    'ticket_promedio' => round($total / max(1, $ventas), 2),
                    'total_efectivo' => round($total * 0.55, 2),
                    'total_tarjeta' => round($total * 0.25, 2),
                    'total_otros' => round($total * 0.20, 2),
                    'total_igv' => round($total * 0.18 / 1.18, 2),
                    'monto_efectivo_contado' => round($total * 0.55, 2),
                    'diferencia_efectivo' => 0,
                    'creado_en' => $fin->format('Y-m-d H:i:s'),
                ]);
            }
            $fecha->addDays($fecha->dayOfWeek === Carbon::FRIDAY ? 3 : 1);
        }
    }
}
