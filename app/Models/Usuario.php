<?php

namespace App\Models;

use App\Services\PermisoService;
use App\Services\OnboardingService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'empresa_id', 'nombre', 'usuario', 'email', 'password', 'rol', 'activo',
        'en_sesion', 'sesion_inicio_en', 'sesion_laravel_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'en_sesion' => 'boolean',
            'sesion_inicio_en' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function terminales(): BelongsToMany
    {
        return $this->belongsToMany(Terminal::class, 'usuario_terminal', 'usuario_id', 'terminal_id')
            ->withPivot('es_defecto');
    }

    public function terminalDefecto(): ?Terminal
    {
        return $this->terminales()
            ->where('terminales.activo', true)
            ->wherePivot('es_defecto', true)
            ->first();
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === 'super_admin';
    }

    public function esDueno(): bool
    {
        return in_array($this->rol, ['super_admin', 'dueno'], true);
    }

    public function puedeGestionar(): bool
    {
        return in_array($this->rol, ['super_admin', 'dueno', 'supervisor'], true);
    }

    /** @return list<string> */
    public function modulosPermitidos(): array
    {
        if ($this->rol === 'super_admin') {
            return config('permisos.modulos_por_rol.super_admin', []);
        }

        if ($this->empresa_id && $this->empresa) {
            return app(PermisoService::class)->modulosPorRol($this->empresa, $this->rol);
        }

        return config('permisos.modulos_por_rol.'.$this->rol, []);
    }

    public function tieneModulo(string $modulo): bool
    {
        return in_array($modulo, $this->modulosPermitidos(), true);
    }

    public function etiquetaRol(): string
    {
        return config('permisos.etiquetas_rol.'.$this->rol, $this->rol);
    }

    public function rutaInicio(): string
    {
        if ($this->esSuperAdmin()) {
            return route('admin.inicio');
        }

        if ($this->esDueno() && $this->empresa
            && ! app(OnboardingService::class)->completado($this->empresa)) {
            return route('onboarding.index');
        }

        if ($this->tieneModulo('inicio')) {
            return route('app.inicio');
        }
        if ($this->tieneModulo('pos')) {
            return route('pos.index');
        }
        if ($this->tieneModulo('productos')) {
            return route('productos.index');
        }

        return route('login');
    }
}
