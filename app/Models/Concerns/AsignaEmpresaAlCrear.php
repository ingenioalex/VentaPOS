<?php

namespace App\Models\Concerns;

trait AsignaEmpresaAlCrear
{
    protected static function bootAsignaEmpresaAlCrear(): void
    {
        static::creating(function ($model) {
            $usuario = auth()->user();
            if ($usuario?->empresa_id && empty($model->empresa_id)) {
                $model->empresa_id = $usuario->empresa_id;
            }
        });
    }
}
