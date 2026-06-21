<?php

namespace App\Models;

use App\Models\Concerns\AsignaEmpresaAlCrear;
use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use AsignaEmpresaAlCrear, FiltraPorEmpresa;

    protected $table = 'proveedores';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'empresa_id', 'tipo_documento', 'numero_documento', 'razon_social',
        'nombre_contacto', 'telefono', 'email', 'direccion', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }

    public function etiquetaDocumento(): string
    {
        return strtoupper($this->tipo_documento).' '.$this->numero_documento;
    }
}
