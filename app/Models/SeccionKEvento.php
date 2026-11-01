<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionKEvento extends Model
{
    protected $table = 'seccionk_eventos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha',
        'monto',
        'tipo',
        'mes_cuota',
        'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function participaciones(): HasMany
    {
        return $this->hasMany(SeccionKParticipacion::class, 'evento_id');
    }

    public function esCuotaMensual(): bool
    {
        return $this->tipo === 'cuota_mensual';
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeCuotasMensuales($query)
    {
        return $query->where('tipo', 'cuota_mensual');
    }

    public function scopeEventos($query)
    {
        return $query->where('tipo', 'evento');
    }
}
