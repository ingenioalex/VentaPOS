<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminGuiaController extends Controller
{
    public function index(Request $request)
    {
        $guia = $this->guiaConfig();
        $vars = $this->variablesPlantilla($request);
        $plantillas = collect($guia['plantillas'] ?? [])
            ->map(function (array $item) use ($vars) {
                $item['texto_resuelto'] = $this->aplicarVariables($item['texto'], $vars);
                $item['grupo'] = $item['grupo'] ?? 'soporte';

                return $item;
            });

        $grupos = $guia['grupos'] ?? [];
        $plantillasPorGrupo = $plantillas->groupBy('grupo');

        return view('admin.guia.index', [
            'tituloGuia' => $guia['titulo'] ?? 'Guía de respuesta',
            'descripcionModulo' => $guia['descripcion'] ?? '',
            'grupos' => $grupos,
            'plantillasPorGrupo' => $plantillasPorGrupo,
            'totalPlantillas' => $plantillas->count(),
            'vars' => $vars,
            'planes' => config('planes'),
        ]);
    }

    public function imprimir()
    {
        return view('admin.guia.imprimir', [
            'planes' => config('planes'),
            'arranque' => config('planes.arranque'),
        ]);
    }

    private function guiaConfig(): array
    {
        $cfg = config('guia_respuesta');

        if (is_array($cfg) && ! empty($cfg['plantillas'])) {
            return $cfg;
        }

        $path = config_path('guia_respuesta.php');

        return is_file($path) ? (require $path) : ['plantillas' => [], 'grupos' => []];
    }

    private function variablesPlantilla(Request $request): array
    {
        $planes = config('planes');

        return [
            'nombre' => trim((string) $request->get('nombre', '[NOMBRE]')),
            'plan' => trim((string) $request->get('plan', 'Básico')),
            'usuario' => trim((string) $request->get('usuario', '[usuario]')),
            'arranque' => (string) ($planes['arranque']['monto'] ?? 99),
            'mensual' => trim((string) $request->get('mensual', (string) ($planes['basico']['mensual'] ?? 29))),
            'basico_mensual' => (string) ($planes['basico']['mensual'] ?? 29),
            'negocio_mensual' => (string) ($planes['negocio']['mensual'] ?? 45),
            'pro_mensual' => (string) ($planes['pro']['mensual'] ?? 59),
        ];
    }

    private function aplicarVariables(string $texto, array $vars): string
    {
        $reemplazos = [];
        foreach ($vars as $clave => $valor) {
            $reemplazos['{'.$clave.'}'] = $valor;
        }

        return strtr($texto, $reemplazos);
    }
}
