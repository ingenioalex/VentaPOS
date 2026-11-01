<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionKPromocion extends Model
{
    protected $table = 'seccionk_promociones';

    protected $fillable = [
        'dni',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'direccion',
        'celular',
        'participa_cuotas',
        'referencias',
        'activo',
    ];

    protected $casts = [
        'participa_cuotas' => 'boolean',
        'activo' => 'boolean',
    ];

    public function participaciones(): HasMany
    {
        return $this->hasMany(SeccionKParticipacion::class, 'promocion_id');
    }

    public function nombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
