<?php

return [
    /*
    | Módulos del panel por rol (plantilla por defecto)
    | dueno = administrador de la empresa
    */
    'modulos' => [
        'inicio' => 'Inicio',
        'pos' => 'POS Venta',
        'ventas' => 'Ventas',
        'cierres' => 'Cierre de caja',
        'productos' => 'Productos',
        'categorias' => 'Categorías',
        'clientes' => 'Clientes',
        'reportes' => 'Reportes',
        'usuarios' => 'Usuarios',
        'permisos' => 'Permisos por rol',
        'configuracion' => 'Configuración',
        'terminales' => 'Terminales',
        'usuario_terminales' => 'Usuarios y terminales',
        'inventario' => 'Inventario',
        'pos_movil' => 'POS Móvil',
        'impresora' => 'Config. impresora',
        'proveedores' => 'Proveedores',
        'compras' => 'Compras',
        'sunat' => 'Facturación SUNAT',
    ],

    'modulos_por_rol' => [
        'super_admin' => ['inicio', 'pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'reportes', 'usuarios', 'permisos', 'configuracion', 'terminales', 'usuario_terminales', 'inventario', 'proveedores', 'compras', 'sunat', 'impresora'],
        'dueno' => ['inicio', 'pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'reportes', 'usuarios', 'permisos', 'configuracion', 'terminales', 'usuario_terminales', 'inventario', 'proveedores', 'compras', 'sunat', 'impresora'],
        'supervisor' => ['inicio', 'pos', 'pos_movil', 'ventas', 'cierres', 'clientes', 'reportes', 'inventario', 'proveedores', 'compras', 'impresora'],
        'cajero' => ['pos', 'pos_movil', 'impresora'],
        'digitador' => ['inicio', 'productos', 'categorias', 'inventario', 'proveedores', 'compras'],
    ],

    'roles_gestionables' => ['supervisor', 'cajero', 'digitador'],

    'roles_configurables' => ['supervisor', 'cajero', 'digitador'],

    'etiquetas_rol' => [
        'super_admin' => 'Super Admin',
        'dueno' => 'Administrador',
        'supervisor' => 'Supervisor',
        'cajero' => 'Cajero',
        'digitador' => 'Digitador',
    ],

    /*
    | Módulos base incluidos en cualquier empresa activa (no se cobran aparte)
    */
    'modulos_base' => ['inicio', 'usuarios', 'permisos', 'configuracion'],

    /*
    | Orden de secciones en admin → Contratación → Módulos y Tarifas
    */
    'orden_grupos' => [
        'Ventas y POS',
        'Catálogo',
        'Inventario y compras',
        'Reportes',
        'Facturación electrónica',
        'Infraestructura',
    ],

    'descripcion_grupos' => [
        'Ventas y POS' => 'Punto de venta, historial de ventas y cierre de caja.',
        'Catálogo' => 'Productos, categorías y clientes.',
        'Inventario y compras' => 'Stock, proveedores y órdenes de compra.',
        'Reportes' => 'Un solo módulo: ventas, inventario, compras, cierres y productos vendidos (con exportación).',
        'Facturación electrónica' => 'Boletas y facturas electrónicas SUNAT.',
        'Infraestructura' => 'Terminales de caja y asignación a usuarios.',
    ],

    /*
    | Módulos que el super admin activa por empresa (facturación)
    | precio_mensual: referencia en soles (add-on o suma orientativa del plan)
    */
    'modulos_contratables' => [
        'pos' => ['nombre' => 'POS Venta', 'grupo' => 'Ventas y POS', 'precio_mensual' => 15],
        'pos_movil' => ['nombre' => 'POS Móvil (celular)', 'grupo' => 'Ventas y POS', 'precio_mensual' => 6, 'requiere' => 'pos'],
        'ventas' => ['nombre' => 'Ventas', 'grupo' => 'Ventas y POS', 'precio_mensual' => 4],
        'cierres' => ['nombre' => 'Cierre de caja', 'grupo' => 'Ventas y POS', 'precio_mensual' => 4],
        'productos' => ['nombre' => 'Productos', 'grupo' => 'Catálogo', 'precio_mensual' => 4],
        'categorias' => ['nombre' => 'Categorías', 'grupo' => 'Catálogo', 'precio_mensual' => 2],
        'clientes' => ['nombre' => 'Clientes', 'grupo' => 'Catálogo', 'precio_mensual' => 4],
        'inventario' => ['nombre' => 'Inventario', 'grupo' => 'Inventario y compras', 'precio_mensual' => 8],
        'proveedores' => ['nombre' => 'Proveedores', 'grupo' => 'Inventario y compras', 'precio_mensual' => 4],
        'compras' => ['nombre' => 'Compras', 'grupo' => 'Inventario y compras', 'precio_mensual' => 6, 'requiere' => 'inventario'],
        'reportes' => [
            'nombre' => 'Reportes',
            'grupo' => 'Reportes',
            'precio_mensual' => 6,
            'descripcion' => 'Incluye R. Ventas, R. Inventario, R. Compras, R. Cierres y R. Productos vendidos.',
        ],
        'sunat' => ['nombre' => 'Facturación electrónica SUNAT', 'grupo' => 'Facturación electrónica', 'precio_mensual' => 12, 'requiere' => 'ventas'],
        'terminales' => ['nombre' => 'Terminales', 'grupo' => 'Infraestructura', 'precio_mensual' => 4],
        'usuario_terminales' => ['nombre' => 'Usuarios y terminales', 'grupo' => 'Infraestructura', 'precio_mensual' => 2],
        'impresora' => ['nombre' => 'Configuración de impresora', 'grupo' => 'Infraestructura', 'precio_mensual' => 0],
    ],

    /*
    | Módulos sugeridos al crear empresa según plan
    */
    'modulos_por_plan' => [
        'beta' => ['pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'inventario', 'proveedores', 'compras', 'reportes', 'terminales', 'usuario_terminales', 'sunat', 'impresora'],
        'basico' => ['pos', 'ventas', 'cierres', 'productos', 'categorias', 'impresora'],
        'negocio' => ['pos', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'inventario', 'proveedores', 'compras', 'reportes', 'impresora'],
        'pro' => ['pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'inventario', 'proveedores', 'compras', 'reportes', 'terminales', 'usuario_terminales', 'sunat', 'impresora'],
    ],
];
