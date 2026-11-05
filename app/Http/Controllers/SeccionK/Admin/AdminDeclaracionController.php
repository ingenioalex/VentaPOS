<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKMovimiento;
use App\Support\SeccionKEsquema;
use App\Support\SeccionKEstadisticas;

class AdminDeclaracionController extends Controller
{
    public function index()
    {
        SeccionKEsquema::asegurarDeclaraciones();
        SeccionKEsquema::asegurarMovimientos();

        return view('seccionk.admin.declaracion', array_merge(
            SeccionKEstadisticas::declaracionFondos(),
            SeccionKMovimiento::resumenFondo()
        ));
    }
}
