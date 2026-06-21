<?php

namespace App\Models;

use App\Models\Concerns\AsignaEmpresaAlCrear;
use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoPrecio extends Model
{
    use AsignaEmpresaAlCrear, FiltraPorEmpresa;

    protected $table = 'producto_precio';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id', 'producto_id', 'usuario_id',
        'precio_anterior', 'precio_nuevo', 'costo_anterior', 'costo_nuevo',
    ];

    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
