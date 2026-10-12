<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeccionKParticipacion extends Model
{
    protected $table = 'seccionk_participaciones';

    protected $fillable = [
        'promocion_id',
        'evento_id',
        'participo',
        'pago',
        'monto_aportado',
        'numero_transaccion',
        'imagen_sustento',
        'fecha_pago',
        'observaciones',
    ];

    protected $casts = [
        'participo' => 'boolean',
        'pago' => 'boolean',
        'monto_aportado' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function promocion(): BelongsTo
    {
        return $this->belongsTo(SeccionKPromocion::class, 'promocion_id');
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(SeccionKEvento::class, 'evento_id');
    }

    public function scopeDePromocionesActivas($query)
    {
        return $query->whereHas('promocion', function ($promocion) {
            $promocion->where('activo', true);
        });
    }
}
