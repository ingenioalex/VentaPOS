<?php

namespace App\Models;

use App\Models\Concerns\AsignaEmpresaAlCrear;
use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use AsignaEmpresaAlCrear, FiltraPorEmpresa;

    protected $table = 'productos';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'empresa_id', 'categoria_id', 'sku', 'codigo_barra', 'nombre',
        'unidad_medida', 'costo_compra', 'precio_venta', 'afecto_igv',
        'stock_disponible', 'stock_minimo', 'campo1', 'campo2', 'campo3', 'activo', 'control_lote',
    ];

    protected function casts(): array
    {
        return [
            'afecto_igv' => 'boolean',
            'activo' => 'boolean',
            'control_lote' => 'boolean',
            'costo_compra' => 'decimal:4',
            'precio_venta' => 'decimal:4',
            'stock_disponible' => 'decimal:4',
            'stock_minimo' => 'decimal:4',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function lotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductoLote::class, 'producto_id');
    }

    public function scopeStockBajo($query)
    {
        return $query->where('activo', true)
            ->where(function ($q) {
                $q->where('stock_disponible', '<', 0)
                    ->orWhere(function ($sub) {
                        $sub->where('stock_minimo', '>', 0)
                            ->whereColumn('stock_disponible', '<=', 'stock_minimo');
                    });
            });
    }

    public function tieneStockBajo(): bool
    {
        if (! $this->activo) {
            return false;
        }

        if ((float) $this->stock_disponible < 0) {
            return true;
        }

        return (float) $this->stock_minimo > 0
            && (float) $this->stock_disponible <= (float) $this->stock_minimo;
    }
}
