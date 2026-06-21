<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class GuiaController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        return view('app.guia.index', compact('usuario'));
    }
}
