<?php

namespace App\Http\Controllers;

use App\Models\Prospecto;
use Illuminate\Http\Request;

class ProspectoController extends Controller
{
    public function beta(Request $request)
    {
        $datos = $request->validate([
            'nombre_negocio' => 'required|string|max:150',
            'nombre_contacto' => 'nullable|string|max:120',
            'telefono' => 'required|string|max:20',
            'tipo_negocio' => 'required|string|max:50',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        Prospecto::create([
            ...$datos,
            'origen' => 'beta',
            'estado' => 'nuevo',
        ]);

        $wa = config('ventapos.whatsapp', '51973034118');
        $texto = rawurlencode(
            "Solicitud Beta VentaPOS\n\n".
            "Negocio: {$datos['nombre_negocio']}\n".
            "Contacto: ".($datos['nombre_contacto'] ?? '-')."\n".
            "WhatsApp: {$datos['telefono']}\n".
            "Rubro: {$datos['tipo_negocio']}\n".
            "Mensaje: ".($datos['mensaje'] ?? '-')
        );

        return redirect()->away("https://wa.me/{$wa}?text={$texto}");
    }
}
