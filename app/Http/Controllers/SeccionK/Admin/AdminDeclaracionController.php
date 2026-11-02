<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Support\SeccionKEsquema;
use App\Support\SeccionKEstadisticas;

class AdminDeclaracionController extends Controller
{
    public function index()
    {
        SeccionKEsquema::asegurarDeclaraciones();

        return view('seccionk.admin.declaracion', SeccionKEstadisticas::declaracionFondos());
    }
}
