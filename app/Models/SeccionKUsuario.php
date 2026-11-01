<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class SeccionKUsuario extends Authenticatable
{
    protected $table = 'seccionk_usuarios';

    protected $guard = 'seccionk';

    protected $fillable = [
        'usuario',
        'password',
        'nombre',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'password' => 'hashed',
    ];
}
