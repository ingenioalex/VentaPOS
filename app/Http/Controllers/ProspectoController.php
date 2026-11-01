<?php

namespace App\Http\Controllers;

use App\Models\Prospecto;
use App\Services\ProspectoAntispamService;
use Illuminate\Http\Request;

class ProspectoController extends Controller
{
    public function __construct(private ProspectoAntispamService $antispam) {}

    public function beta(Request $request)
    {
        if ($this->antispam->esBot($request)) {
            return redirect()->route('contacto')
                ->with('ok', 'Gracias por su interés. Le contactaremos pronto.');
        }

        $datos = $request->validate([
            'nombre_negocio' => 'required|string|max:150',
            'nombre_contacto' => 'nullable|string|max:120',
            'telefono' => 'required|string|max:20',
            'tipo_negocio' => 'required|string|max:50',
            'mensaje' => 'nullable|string|max:2000',
            'empresa_web' => 'nullable|string|max:0',
            'formulario_inicio' => 'nullable|integer',
        ]);

        Prospecto::create([
            'nombre_negocio' => $datos['nombre_negocio'],
            'nombre_contacto' => $datos['nombre_contacto'] ?? null,
            'telefono' => $datos['telefono'],
            'tipo_negocio' => $datos['tipo_negocio'],
            'mensaje' => $datos['mensaje'] ?? null,
            'origen' => 'beta',
            'estado' => 'nuevo',
        ]);

        $wa = config('ventapos.whatsapp', '51973034118');
        $texto = rawurlencode(
            "Solicitud Beta VentasÁgil\n\n".
            "Negocio: {$datos['nombre_negocio']}\n".
            "Contacto: ".($datos['nombre_contacto'] ?? '-')."\n".
            "WhatsApp: {$datos['telefono']}\n".
            "Rubro: {$datos['tipo_negocio']}\n".
            "Mensaje: ".($datos['mensaje'] ?? '-')
        );

        return redirect()->away("https://wa.me/{$wa}?text={$texto}");
    }
}
