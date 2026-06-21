<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

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
}
