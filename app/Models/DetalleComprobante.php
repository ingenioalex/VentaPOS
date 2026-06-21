<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleComprobante extends Model
{
    protected $table = 'detalle_comprobante';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = [
        'comprobante_id', 'producto_id', 'descripcion_producto',
        'cantidad', 'precio_unitario', 'descuento_pct', 'subtotal',
    ];

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
