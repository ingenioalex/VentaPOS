<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeccionKEventoEdicion extends Model
{
    protected $table = 'seccionk_eventos_edicion';

    protected $fillable = [
        'evento_id',
        'abierto',
    ];

    protected $casts = [
        'abierto' => 'boolean',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(SeccionKEvento::class, 'evento_id');
    }

    public static function marcar(SeccionKEvento $evento, bool $abierto): self
    {
        return self::updateOrCreate(
            ['evento_id' => $evento->id],
            ['abierto' => $abierto]
        );
    }
}
