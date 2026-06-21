<?php

namespace App\Models;

use App\Models\Concerns\AsignaEmpresaAlCrear;
use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use AsignaEmpresaAlCrear, FiltraPorEmpresa;

    protected $table = 'compras';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'empresa_id', 'proveedor_id', 'usuario_id', 'tipo_documento',
        'serie', 'numero_documento', 'fecha_emision', 'fecha_recepcion',
        'subtotal', 'valor_igv', 'total', 'estado', 'observaciones',
        'motivo_anulacion', 'anulado_por_usuario_id', 'anulado_en', 'recibido_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_recepcion' => 'date',
            'subtotal' => 'decimal:2',
            'valor_igv' => 'decimal:2',
            'total' => 'decimal:2',
            'anulado_en' => 'datetime',
            'recibido_en' => 'datetime',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'anulado_por_usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }

    public function etiquetaDocumento(): string
    {
        $tipo = match ($this->tipo_documento) {
            'guia' => 'Guía',
            'orden' => 'O.C.',
            default => 'Factura',
        };
        $serie = $this->serie ? $this->serie.'-' : '';

        return $tipo.' '.$serie.$this->numero_documento;
    }
}
