<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKParticipacion;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalPromociones = SeccionKPromocion::activos()->count();
        $participanCuotas = SeccionKPromocion::activos()->where('participa_cuotas', true)->count();
        $totalEventos = SeccionKEvento::activos()->count();
        $totalCuotas = SeccionKEvento::cuotasMensuales()->activos()->count();

        // Cuotas mensuales para el dashboard
        $cuotasMensuales = SeccionKEvento::cuotasMensuales()
            ->activos()
            ->orderBy('fecha')
            ->get();

        // Eventos recientes
        $eventosRecientes = SeccionKEvento::eventos()
            ->activos()
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();

        return view('seccionk.admin.dashboard', compact(
            'totalPromociones',
            'participanCuotas',
            'totalEventos',
            'totalCuotas',
            'cuotasMensuales',
            'eventosRecientes'
        ));
    }
}
