<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    protected $table = 'prospectos';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre_negocio', 'nombre_contacto', 'telefono', 'email',
        'tipo_negocio', 'mensaje', 'origen', 'estado',
    ];
}
