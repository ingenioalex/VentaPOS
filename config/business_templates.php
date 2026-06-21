<?php

/**
 * Plantillas por rubro — copiar a config/ después de Laravel install.
 * Al crear empresa se aplican categorías, campos y módulos según tipo_negocio.
 */
return [

    'bodega' => [
        'label' => 'Bodega / Minimarket',
        'categories' => ['Bebidas', 'Abarrotes', 'Limpieza', 'Golosinas', 'Lácteos', 'Otros'],
        'default_unit' => 'und',
        'fields' => [
            ['key' => 'ubicacion', 'label' => 'Ubicación en anaquel', 'type' => 'text', 'required' => false],
        ],
        'modules' => [
            'expiry_tracking' => false,
            'unit_sale' => false,
            'wholesale_price' => false,
            'barcode_scanner' => true,
        ],
    ],

    'ferreteria' => [
        'label' => 'Ferretería',
        'categories' => ['Herramientas', 'Electricidad', 'Pintura', 'Plomería', 'Construcción', 'Otros'],
        'default_unit' => 'und',
        'fields' => [
            ['key' => 'marca', 'label' => 'Marca', 'type' => 'text', 'required' => false],
            ['key' => 'numero_parte', 'label' => 'Número de parte', 'type' => 'text', 'required' => false],
        ],
        'modules' => [
            'expiry_tracking' => false,
            'unit_sale' => true,
            'wholesale_price' => true,
            'barcode_scanner' => true,
        ],
        'units' => ['und', 'metro', 'caja', 'kg', 'litro'],
    ],

    'farmacia' => [
        'label' => 'Farmacia / Botica',
        'categories' => ['Medicamentos OTC', 'Genéricos', 'Cuidado personal', 'Suplementos', 'Otros'],
        'default_unit' => 'und',
        'fields' => [
            ['key' => 'principio_activo', 'label' => 'Principio activo', 'type' => 'text', 'required' => false],
            ['key' => 'registro_sanitario', 'label' => 'Reg. sanitario', 'type' => 'text', 'required' => false],
        ],
        'modules' => [
            'expiry_tracking' => true,
            'lot_tracking' => true,
            'unit_sale' => false,
            'wholesale_price' => false,
            'barcode_scanner' => true,
        ],
    ],

    'otro' => [
        'label' => 'Otro rubro',
        'categories' => ['General', 'Otros'],
        'default_unit' => 'und',
        'fields' => [],
        'modules' => [
            'expiry_tracking' => false,
            'unit_sale' => false,
            'wholesale_price' => false,
            'barcode_scanner' => true,
        ],
    ],

];
