<?php

namespace App\Models;

use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaSuspendida extends Model
{
    use FiltraPorEmpresa;

    protected $table = 'ventas_suspendidas';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id', 'usuario_id', 'codigo_hold', 'datos', 'total',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'total' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
