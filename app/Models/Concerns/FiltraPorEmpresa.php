<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait FiltraPorEmpresa
{
    protected static function bootFiltraPorEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $query) {
            $usuario = auth()->user();
            if ($usuario && $usuario->empresa_id) {
                $query->where($query->getModel()->getTable().'.empresa_id', $usuario->empresa_id);
            }
        });
    }

    /** Evita IDOR: solo resuelve registros de la empresa del usuario autenticado. */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        $usuario = auth()->user();

        if (! $usuario?->empresa_id) {
            throw (new ModelNotFoundException)->setModel(static::class, [$value]);
        }

        return static::query()
            ->where($field, $value)
            ->where($this->getTable().'.empresa_id', $usuario->empresa_id)
            ->firstOrFail();
    }
}
