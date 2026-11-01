<?php

return [
    'whatsapp' => env('VENTAPOS_WHATSAPP', '51973034118'),
    'nombre' => env('APP_NAME', 'VentaPOS'),
    /** URL del perfil LinkedIn del fundador (página de contacto). Ej: https://www.linkedin.com/in/yuri-vargas-lazo */
    'linkedin_fundador' => env('VENTAPOS_LINKEDIN_FUNDADOR')
        ?: 'https://www.linkedin.com/in/yuri-alexander-vargas-l-1774142b9/',
];
