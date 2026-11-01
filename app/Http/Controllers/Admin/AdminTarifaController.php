<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PermisoService;
use App\Services\TarifaService;
use Illuminate\Http\Request;

class AdminTarifaController extends Controller
{
    public function __construct(
        private TarifaService $tarifas,
        private PermisoService $permisos,
    ) {}

    public function index()
    {
        $porGrupo = $this->permisos->contratablesAgrupados();

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
