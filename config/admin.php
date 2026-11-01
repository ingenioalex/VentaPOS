<?php

return [
    /*
    | Ruta del panel super admin.
    | Local: omita ADMIN_PANEL_PATH → queda "admin" (/admin/login).
    | Producción: ruta personalizada, ej. va-cp-m8k3x7q2
    */
    'panel_path' => env('ADMIN_PANEL_PATH', 'admin'),

    /*
    | Devuelve 404 en /admin/* cuando el panel usa otra ruta.
    */
    'block_legacy_path' => env('ADMIN_BLOCK_LEGACY_PATH', true),
];
