<?php

return [
    'defaults' => [
        'ancho_mm' => 80,
        'auto_imprimir' => true,
        'mostrar_logo' => true,
        'tamano_fuente' => 'normal',
        'copias' => 1,
        'notas_pie' => '',
        'metodo' => 'navegador',
        'nombre_impresora_sistema' => '',
    ],

    'anchos' => [
        58 => '58 mm (ticket pequeño)',
        80 => '80 mm (ticket estándar bodega)',
    ],

    'tamanos_fuente' => [
        'compacta' => 'Compacta (más líneas)',
        'normal' => 'Normal',
        'grande' => 'Grande (más legible)',
    ],

    'metodos' => [
        'navegador' => 'Impresión del navegador (recomendado hoy)',
        'termica_usb' => 'Térmica USB — misma vía navegador, impresora por defecto en Windows',
        'bluetooth' => 'Bluetooth — impresora emparejada al equipo (Windows/tablet/móvil)',
    ],

    'css' => [
        58 => [
            'compacta' => ['body' => '11px', 'table' => '9px', 'max' => '220px', 'qr' => '120px'],
            'normal' => ['body' => '12px', 'table' => '10px', 'max' => '240px', 'qr' => '140px'],
            'grande' => ['body' => '13px', 'table' => '11px', 'max' => '260px', 'qr' => '150px'],
        ],
        80 => [
            'compacta' => ['body' => '12px', 'table' => '10px', 'max' => '300px', 'qr' => '150px'],
            'normal' => ['body' => '13px', 'table' => '11px', 'max' => '360px', 'qr' => '168px'],
            'grande' => ['body' => '14px', 'table' => '12px', 'max' => '380px', 'qr' => '180px'],
        ],
    ],
];
