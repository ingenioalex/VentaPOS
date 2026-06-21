<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TarifaService;
use Illuminate\Http\Request;

class AdminTarifaController extends Controller
{
    public function __construct(private TarifaService $tarifas) {}

    public function index()
    {
        $modulos = config('permisos.modulos_contratables', []);
        $porGrupo = collect($modulos)
            ->map(fn ($info, $clave) => array_merge($info, ['clave' => $clave]))
            ->groupBy('grupo');

        return view('admin.tarifas.index', [
            'arranque' => config('planes.arranque'),
            'planes' => [
                'basico' => config('planes.basico'),
                'negocio' => config('planes.negocio'),
                'pro' => config('planes.pro'),
            ],
            'extras' => config('extras.disponibles', []),
            'extraDefault' => config('extras.precio_mensual_default'),
            'porGrupo' => $porGrupo,
            'tienePersonalizacion' => file_exists($this->tarifas->rutaArchivo()),
        ]);
    }

    public function actualizar(Request $request)
    {
        $this->tarifas->guardarDesdeRequest($request);
        $this->tarifas->aplicarEnConfig();

        return redirect()->route('admin.tarifas.index')
            ->with('ok', 'Tarifas actualizadas. Los cambios se reflejan en admin, landing y configuración de empresas.');
    }

    public function restaurar()
    {
        $this->tarifas->restaurarDefaults();

        return redirect()->route('admin.tarifas.index')
            ->with('ok', 'Se restauraron los precios por defecto de config/planes.php y config/permisos.php.');
    }
}
