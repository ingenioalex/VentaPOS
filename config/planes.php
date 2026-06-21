<?php

/**
 * Modelo comercial VentaPOS
 *
 * Límites de usuarios (usuarios_max): cupo base del plan.
 * Cupo efectivo = plan + bonificados + extras_pago (ver cupos_usuarios en empresa.configuracion).
 */
return [

    /*
    | Planes disponibles en admin (incluye Beta para pruebas y clientes legacy).
    */
    'planes_disponibles' => ['beta', 'basico', 'negocio', 'pro'],

    'arranque' => [
        'monto' => 99,
        'min' => 80,
        'max' => 120,
        'incluye_primer_mes' => true,
        'etiqueta' => 'Instalación + 1er mes',
        'detalle' => 'Mismo precio en todos los planes: puesta en marcha, capacitación y el primer mes de servicio incluidos.',
    ],

    'usuario_extra' => [
        'nombre' => 'Usuario adicional (add-on)',
        'precio_mensual' => 5,
        'descripcion' => 'Cada cupo extra de pago permite un usuario activo adicional.',
    ],

    'beta' => [
        'nombre' => 'Beta',
        'mensual' => 0,
        'nota' => 'Gratis 2 meses · 3 usuarios · sin cargo de arranque',
        'limites' => [
            'usuarios_max' => 3,
            'productos_max' => 200,
        ],
        'equipo_sugerido' => '1 admin + 1 cajero + 1 supervisor (prueba)',
    ],

    'basico' => [
        'nombre' => 'Básico',
        'mensual' => 29,
        'limites' => [
            'usuarios_max' => 4,
            'productos_max' => 500,
        ],
        'equipo_sugerido' => '1 admin + 1 supervisor + 2 cajeros (o 1 cajero + 1 digitador)',
        'incluye' => [
            'Hasta 4 usuarios (admin + supervisor + 2 cajeros)',
            '500 productos',
            'POS + ventas',
            'Cierre de caja',
            'Reportes básicos',
        ],
    ],

    'negocio' => [
        'nombre' => 'Negocio',
        'mensual' => 45,
        'limites' => [
            'usuarios_max' => 8,
            'productos_max' => 2000,
        ],
        'equipo_sugerido' => '1 admin + 1–2 supervisores + cajeros y digitadores',
        'incluye' => [
            'Hasta 8 usuarios',
            '2000 productos',
            'Inventario y clientes',
            'Compras y reportes',
            'Export Excel',
        ],
    ],

    'pro' => [
        'nombre' => 'Pro',
        'mensual' => 59,
        'limites' => [
            'usuarios_max' => 15,
            'productos_max' => null,
        ],
        'equipo_sugerido' => '1 admin + equipo multi-caja (supervisores, cajeros, digitadores)',
        'incluye' => [
            'Hasta 15 usuarios',
            'Productos ilimitados',
            'POS Móvil y multi-terminal',
            'Facturación electrónica SUNAT',
            'Soporte prioritario',
        ],
    ],

];
