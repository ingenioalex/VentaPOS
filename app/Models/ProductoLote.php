<?php

namespace App\Models;

use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoLote extends Model
{
    use FiltraPorEmpresa;

    protected $table = 'producto_lotes';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id', 'producto_id', 'lote', 'fecha_vencimiento', 'cantidad', 'referencia',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'cantidad' => 'decimal:4',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->lt(today());
    }

    public function vencePronto(int $dias = 30): bool
    {
        if (! $this->fecha_vencimiento) {
            return false;
        }

        return $this->fecha_vencimiento->lte(today()->addDays($dias));
    }
}
