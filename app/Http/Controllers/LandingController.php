<?php

namespace App\Http\Controllers;

class LandingController extends Controller
{
    public function inicio()
    {
        return view('landing.inicio');
    }

    public function precios()
    {
        return view('landing.precios');
    }

    public function contacto()
    {
        return view('landing.contacto');
    }
}
