<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $table = 'empresas';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre', 'codigo', 'tipo_negocio', 'ruc', 'telefono', 'direccion',
        'configuracion', 'plan', 'activo', 'prueba_hasta',
    ];

    protected function casts(): array
    {
        return [
            'configuracion' => 'array',
            'activo' => 'boolean',
            'prueba_hasta' => 'date',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'empresa_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'empresa_id');
    }

    public function logoUrl(): ?string
    {
        $logo = ($this->configuracion ?? [])['logo'] ?? null;
        if (! $logo || ! file_exists(public_path('storage/'.$logo))) {
            return null;
        }

        return asset('storage/'.$logo);
    }

    public function tieneExtra(string $extra): bool
    {
        return app(\App\Services\ExtrasEmpresaService::class)->activo($this, $extra);
    }

    public function limiteUsuarios(): int
    {
        return app(\App\Services\PlanLimiteService::class)->limiteUsuarios($this);
    }

    public function usuariosActivos(): int
    {
        return app(\App\Services\PlanLimiteService::class)->usuariosActivos($this);
    }
}
