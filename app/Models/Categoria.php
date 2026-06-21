<?php

namespace App\Models;

use App\Models\Concerns\AsignaEmpresaAlCrear;
use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use AsignaEmpresaAlCrear, FiltraPorEmpresa;

    protected $table = 'categorias';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = ['empresa_id', 'parent_id', 'nombre', 'orden'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Categoria::class, 'parent_id')->orderBy('orden')->orderBy('nombre');
    }

    public function nombreCompleto(): string
    {
        if ($this->parent) {
            return $this->parent->nombre.' › '.$this->nombre;
        }

        return $this->nombre;
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
