<?php



return [

    /*

    | Extras opcionales (add-ons) contratados por el super admin por empresa.

    | Se guardan en empresa.configuracion.extras (boolean por clave).

    | El dashboard KPI de ventas viene incluido con el módulo ventas (sin cargo extra).

    */

    'precio_mensual_default' => 9,



    'disponibles' => [

        'whatsapp_resumen' => [

            'nombre' => 'Resumen diario por WhatsApp',

            'descripcion' => 'Enlace wa.me con resumen de ventas del día; cron diario 20:00.',

            'grupo' => 'Operación',

            'precio_mensual' => 9,

            'requiere' => 'ventas',

        ],

        'lotes_vencimiento' => [

            'nombre' => 'Lotes y vencimientos',

            'descripcion' => 'Control por lote, FEFO en venta y alertas de vencimiento.',

            'grupo' => 'Inventario',

            'precio_mensual' => 9,

            'requiere' => 'inventario',

        ],

        'categorias_jerarquicas' => [

            'nombre' => 'Categorías jerárquicas',

            'descripcion' => 'Subcategorías dentro de categorías padre (un nivel).',

            'grupo' => 'Catálogo',

            'precio_mensual' => 9,

            'requiere' => 'categorias',

        ],

        'flejes' => [

            'nombre' => 'Flejes / etiquetas de precio',

            'descripcion' => 'Impresión de etiquetas con código de barras y precio.',

            'grupo' => 'Catálogo',

            'precio_mensual' => 9,

            'requiere' => 'productos',

        ],

    ],



    /*

    | Sugeridos al pulsar «Aplicar sugeridos por tipo» en admin (no se activan solos al crear empresa).

    */

    'sugeridos_por_tipo' => [

        'farmacia' => ['lotes_vencimiento'],

        'bodega' => [],

        'ferreteria' => [],

        'otro' => [],

    ],

];

