<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminRecuperacionToken extends Model
{
    public $timestamps = false;

    protected $table = 'admin_recuperacion_tokens';

    protected $fillable = ['usuario_id', 'token', 'expira_en'];

    protected function casts(): array
    {
        return [
            'expira_en' => 'datetime',
            'creado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function vigente(): bool
    {
        return $this->expira_en->isFuture();
    }
}
