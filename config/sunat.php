<?php

return [
    'tipos_comprobante' => [
        'factura' => '01',
        'boleta' => '03',
    ],

    'tipos_documento_cliente' => [
        'dni' => '1',
        'ruc' => '6',
        'ce' => '4',
        'otro' => '0',
        'pasaporte' => '7',
        'sin_doc' => '0',
    ],

    'nubefact_tipos_documento_cliente' => [
        'dni' => 1,
        'ruc' => 6,
        'ce' => 4,
        'pasaporte' => 7,
        'sin_doc' => '-',
    ],

    'proveedores_ose' => [
        'generico' => 'OSE/PSE genérico (JSON + XML)',
        'nubefact' => 'Nubefact (recomendado Perú)',
    ],
];
