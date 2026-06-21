<?php

namespace App\Services;

use App\Models\Empresa;

class PermisoService
{
    /** @return array<string, string> */
    public function modulosDisponibles(): array
    {
        return config('permisos.modulos', []);
    }

    /** @return array<string, array{nombre: string, grupo: string, precio_mensual: int|float}> */
    public function modulosContratables(): array
    {
        return config('permisos.modulos_contratables', []);
    }

    /** @return list<string> */
    public function modulosBase(): array
    {
        return config('permisos.modulos_base', ['inicio', 'usuarios', 'permisos', 'configuracion']);
    }

    /** @return list<string> */
    public function modulosPorPlan(string $plan): array
    {
        $contratables = array_keys($this->modulosContratables());
        $porPlan = config('permisos.modulos_por_plan.'.$plan);

        if (! is_array($porPlan)) {
            return $contratables;
        }

        return array_values(array_intersect($porPlan, $contratables));
    }

    /** @return list<string> */
    public function modulosContratados(Empresa $empresa): array
    {
        $guardados = ($empresa->configuracion ?? [])['modulos_contratados'] ?? null;

        if (is_array($guardados)) {
            return array_values(array_intersect($guardados, array_keys($this->modulosContratables())));
        }

        return $this->modulosPorPlan($empresa->plan ?? 'beta');
    }

    /** @return list<string> */
    public function modulosHabilitadosEmpresa(Empresa $empresa): array
    {
        return array_values(array_unique(array_merge(
            $this->modulosBase(),
            $this->modulosContratados($empresa)
        )));
    }

    /** @param  list<string>  $modulos */
    public function totalMensualContrato(array $modulos): float
    {
        $contratables = $this->modulosContratables();

        return round(collect($modulos)->sum(
            fn (string $modulo) => (float) ($contratables[$modulo]['precio_mensual'] ?? 0)
        ), 2);
    }

    /** @param  list<string>  $modulos */
    public function guardarModulosContratados(Empresa $empresa, array $modulos): void
    {
        $validos = array_keys($this->modulosContratables());
        $normalizados = array_values(array_intersect($modulos, $validos));

        if (in_array('pos_movil', $normalizados, true) && ! in_array('pos', $normalizados, true)) {
            $normalizados[] = 'pos';
        }

        $config = $empresa->configuracion ?? [];
        $config['modulos_contratados'] = $normalizados;
        $empresa->update(['configuracion' => $config]);

        $this->sincronizarPermisosConContrato($empresa);
    }

    public function aplicarModulosDelPlan(Empresa $empresa): void
    {
        $this->guardarModulosContratados($empresa, $this->modulosPorPlan($empresa->plan ?? 'beta'));
    }

    /** @return array<string, string> */
    public function etiquetasRol(): array
    {
        return config('permisos.etiquetas_rol', []);
    }

    /** @return list<string> */
    public function rolesConfigurables(): array
    {
        return config('permisos.roles_configurables', []);
    }

    /** @return list<string> */
    public function rolesGestionables(): array
    {
        return config('permisos.roles_gestionables', []);
    }

    /** @return array<string, list<string>> */
    public function plantillaPorDefecto(): array
    {
        $roles = $this->rolesConfigurables();

        return collect(config('permisos.modulos_por_rol', []))
            ->only($roles)
            ->map(fn ($modulos) => array_values($modulos))
            ->all();
    }

    /** @return array<string, list<string>> */
    public function permisosEmpresa(Empresa $empresa): array
    {
        $guardados = ($empresa->configuracion ?? [])['permisos_por_rol'] ?? null;

        if (! is_array($guardados)) {
            return $this->filtrarPermisosPorContrato($empresa, $this->plantillaPorDefecto());
        }

        $plantilla = $this->plantillaPorDefecto();
        $modulosValidos = $this->modulosHabilitadosEmpresa($empresa);

        foreach ($this->rolesConfigurables() as $rol) {
            $modulos = $guardados[$rol] ?? $plantilla[$rol] ?? [];
            if (! is_array($modulos)) {
                $modulos = $plantilla[$rol] ?? [];
            }

            $plantilla[$rol] = array_values(array_intersect($modulos, $modulosValidos));
        }

        return $plantilla;
    }

    /** @return list<string> */
    public function modulosPorRol(Empresa $empresa, string $rol): array
    {
        if ($rol === 'super_admin') {
            return config('permisos.modulos_por_rol.super_admin', []);
        }

        $habilitados = $this->modulosHabilitadosEmpresa($empresa);

        if ($rol === 'dueno') {
            return array_values(array_intersect(
                config('permisos.modulos_por_rol.dueno', []),
                $habilitados
            ));
        }

        return array_values(array_intersect(
            $this->permisosEmpresa($empresa)[$rol] ?? [],
            $habilitados
        ));
    }

    /** @param  array<string, list<string>>  $permisos */
    public function guardarPermisos(Empresa $empresa, array $permisos): void
    {
        $modulosValidos = $this->modulosHabilitadosEmpresa($empresa);
        $normalizados = [];

        foreach ($this->rolesConfigurables() as $rol) {
            $modulos = $permisos[$rol] ?? [];
            if (! is_array($modulos)) {
                $modulos = [];
            }

            $normalizados[$rol] = array_values(array_intersect($modulos, $modulosValidos));
        }

        $config = $empresa->configuracion ?? [];
        $config['permisos_por_rol'] = $normalizados;
        $empresa->update(['configuracion' => $config]);
    }

    private function sincronizarPermisosConContrato(Empresa $empresa): void
    {
        $permisos = $this->permisosEmpresa($empresa);
        $filtrados = $this->filtrarPermisosPorContrato($empresa, $permisos);

        if ($filtrados !== $permisos) {
            $this->guardarPermisos($empresa, $filtrados);
        }
    }

    /** @param  array<string, list<string>>  $permisos */
    private function filtrarPermisosPorContrato(Empresa $empresa, array $permisos): array
    {
        $habilitados = $this->modulosHabilitadosEmpresa($empresa);

        foreach ($permisos as $rol => $modulos) {
            $permisos[$rol] = array_values(array_intersect($modulos, $habilitados));
        }

        return $permisos;
    }
}
