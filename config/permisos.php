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
        'proveedores' => 'Proveedores',
        'compras' => 'Compras',
        'sunat' => 'Facturación SUNAT',
    ],

    'modulos_por_rol' => [
        'super_admin' => ['inicio', 'pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'reportes', 'usuarios', 'permisos', 'configuracion', 'terminales', 'usuario_terminales', 'inventario', 'proveedores', 'compras', 'sunat'],
        'dueno' => ['inicio', 'pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'reportes', 'usuarios', 'permisos', 'configuracion', 'terminales', 'usuario_terminales', 'inventario', 'proveedores', 'compras', 'sunat'],
        'supervisor' => ['inicio', 'pos', 'pos_movil', 'ventas', 'cierres', 'clientes', 'reportes', 'inventario', 'proveedores', 'compras'],
        'cajero' => ['pos', 'pos_movil'],
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
    | Módulos que el super admin activa por empresa (facturación)
    | precio_mensual: referencia en soles (add-on o suma orientativa del plan)
    */
    'modulos_contratables' => [
        'pos' => ['nombre' => 'POS Venta', 'grupo' => 'Operación', 'precio_mensual' => 15],
        'ventas' => ['nombre' => 'Ventas', 'grupo' => 'Operación', 'precio_mensual' => 4],
        'cierres' => ['nombre' => 'Cierre de caja', 'grupo' => 'Operación', 'precio_mensual' => 4],
        'productos' => ['nombre' => 'Productos', 'grupo' => 'Catálogo', 'precio_mensual' => 4],
        'categorias' => ['nombre' => 'Categorías', 'grupo' => 'Catálogo', 'precio_mensual' => 2],
        'clientes' => ['nombre' => 'Clientes', 'grupo' => 'CRM', 'precio_mensual' => 4],
        'inventario' => ['nombre' => 'Inventario', 'grupo' => 'Inventario', 'precio_mensual' => 8],
        'proveedores' => ['nombre' => 'Proveedores', 'grupo' => 'Compras', 'precio_mensual' => 4],
        'compras' => ['nombre' => 'Compras', 'grupo' => 'Compras', 'precio_mensual' => 6, 'requiere' => 'inventario'],
        'reportes' => ['nombre' => 'Reportes', 'grupo' => 'Análisis', 'precio_mensual' => 6],
        'terminales' => ['nombre' => 'Terminales', 'grupo' => 'Operación', 'precio_mensual' => 4],
        'usuario_terminales' => ['nombre' => 'Usuarios y terminales', 'grupo' => 'Operación', 'precio_mensual' => 2],
        'pos_movil' => ['nombre' => 'POS Móvil (celular)', 'grupo' => 'Operación', 'precio_mensual' => 6, 'requiere' => 'pos'],
        'sunat' => ['nombre' => 'Facturación electrónica SUNAT', 'grupo' => 'Operación', 'precio_mensual' => 12, 'requiere' => 'ventas'],
    ],

    /*
    | Módulos sugeridos al crear empresa según plan
    */
    'modulos_por_plan' => [
        'beta' => ['pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'inventario', 'proveedores', 'compras', 'reportes', 'terminales', 'usuario_terminales', 'sunat'],
        'basico' => ['pos', 'ventas', 'cierres', 'productos', 'categorias'],
        'negocio' => ['pos', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'inventario', 'proveedores', 'compras', 'reportes'],
        'pro' => ['pos', 'pos_movil', 'ventas', 'cierres', 'productos', 'categorias', 'clientes', 'inventario', 'proveedores', 'compras', 'reportes', 'terminales', 'usuario_terminales', 'sunat'],
    ],
];
