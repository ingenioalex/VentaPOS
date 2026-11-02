<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeccionKDeclaracion extends Model
{
    protected $table = 'seccionk_declaraciones';

    protected $fillable = [
        'evento_id',
        'cerrado',
        'monto_utilizado',
        'fecha_cierre',
        'observaciones',
    ];

    protected $casts = [
        'cerrado' => 'boolean',
        'monto_utilizado' => 'decimal:2',
        'fecha_cierre' => 'date',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(SeccionKEvento::class, 'evento_id');
    }

    public static function registrarCierre(SeccionKEvento $evento, float $montoUtilizado, ?string $observaciones = null): self
    {
        return self::updateOrCreate(
            ['evento_id' => $evento->id],
            [
                'cerrado' => true,
                'monto_utilizado' => $montoUtilizado,
                'fecha_cierre' => now()->toDateString(),
                'observaciones' => $observaciones,
            ]
        );
    }
}
