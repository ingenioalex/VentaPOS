<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    protected $table = 'compra_detalle';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = [
        'compra_id', 'producto_id', 'descripcion', 'cantidad',
        'costo_unitario', 'afecto_igv', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'costo_unitario' => 'decimal:4',
            'subtotal' => 'decimal:2',
            'afecto_igv' => 'boolean',
        ];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
