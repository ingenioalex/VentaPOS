<?php



namespace App\Services;



use App\Models\Empresa;

use Illuminate\Validation\ValidationException;



class ExtrasEmpresaService

{

    /** @return array<string, array{nombre: string, descripcion: string, grupo: string, precio_mensual: int|float, requiere?: string}> */

    public function contratables(): array

    {

        return config('extras.disponibles', []);

    }



    /** @return array<string, bool> */

    public function todos(Empresa $empresa): array

    {

        $guardados = ($empresa->configuracion ?? [])['extras'] ?? [];

        $resultado = [];



        foreach (array_keys($this->contratables()) as $clave) {

            $resultado[$clave] = (bool) ($guardados[$clave] ?? false);

        }



        return $resultado;

    }



    /** @return list<string> */

    public function contratados(Empresa $empresa): array

    {

        return array_keys(array_filter($this->todos($empresa)));

    }



    public function activo(Empresa $empresa, string $extra): bool

    {

        if (! array_key_exists($extra, $this->contratables())) {

            return false;

        }



        return (bool) (($empresa->configuracion ?? [])['extras'][$extra] ?? false);

    }



    /** @param  list<string>  $extras */

    public function totalMensual(array $extras): float

    {

        $contratables = $this->contratables();



        return round(collect($extras)->sum(

            fn (string $clave) => (float) ($contratables[$clave]['precio_mensual'] ?? config('extras.precio_mensual_default', 9))

        ), 2);

    }



    /** @param  list<string>  $extras */

    public function guardarContratados(Empresa $empresa, array $extras, PermisoService $permisos): void

    {

        $validos = array_keys($this->contratables());

        $normalizados = array_values(array_intersect($extras, $validos));

        $modulosContratados = $permisos->modulosContratados($empresa);



        foreach ($normalizados as $clave) {

            $requiere = $this->contratables()[$clave]['requiere'] ?? null;

            if ($requiere && ! in_array($requiere, $modulosContratados, true)) {

                $nombreModulo = config('permisos.modulos_contratables.'.$requiere.'.nombre', $requiere);

                throw ValidationException::withMessages([

                    'extras' => "El extra «{$this->contratables()[$clave]['nombre']}» requiere el módulo «{$nombreModulo}» contratado.",

                ]);

            }

        }



        $flags = [];

        foreach ($validos as $clave) {

            $flags[$clave] = in_array($clave, $normalizados, true);

        }



        $this->guardar($empresa, $flags);

    }



    /** @param  array<string, bool>  $flags */

    public function guardar(Empresa $empresa, array $flags): void

    {

        $config = $empresa->configuracion ?? [];

        $extras = [];



        foreach (array_keys($this->contratables()) as $clave) {

            $extras[$clave] = (bool) ($flags[$clave] ?? false);

        }



        $config['extras'] = $extras;

        $empresa->update(['configuracion' => $config]);

    }



    /** @return array<string, bool> */

    public function defaultsParaTipo(string $tipoNegocio): array

    {

        $extras = [];

        foreach (array_keys($this->contratables()) as $clave) {

            $extras[$clave] = false;

        }



        return $extras;

    }



    /** @return list<string> */

    public function sugeridosParaTipo(string $tipoNegocio): array

    {

        $sugeridos = config('extras.sugeridos_por_tipo.'.$tipoNegocio, []);



        return array_values(array_intersect($sugeridos, array_keys($this->contratables())));

    }



    public function aplicarSugeridosPorTipo(Empresa $empresa, PermisoService $permisos): void

    {

        $actuales = $this->contratados($empresa);

        $sugeridos = $this->sugeridosParaTipo($empresa->tipo_negocio ?? 'otro');

        $merged = array_values(array_unique(array_merge($actuales, $sugeridos)));



        $this->guardarContratados($empresa, $merged, $permisos);

    }

}

