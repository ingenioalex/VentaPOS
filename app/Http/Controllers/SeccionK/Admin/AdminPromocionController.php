<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKPromocion;
use Illuminate\Http\Request;

class AdminPromocionController extends Controller
{
    public function index(Request $request)
    {
        $query = SeccionKPromocion::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('dni', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('participa_cuotas')) {
            $query->where('participa_cuotas', $request->participa_cuotas === '1');
        }

        if (!$request->has('mostrar_inactivos')) {
            $query->where('activo', true);
        }

        $promociones = $query->orderBy('apellido_paterno')->orderBy('apellido_materno')->paginate(20);

        return view('seccionk.admin.promociones.index', compact('promociones'));
    }

    public function create()
    {
        return view('seccionk.admin.promociones.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dni' => 'required|digits:8|unique:seccionk_promociones,dni',
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:15',
            'participa_cuotas' => 'boolean',
            'referencias' => 'nullable|string',
        ]);

        SeccionKPromocion::create($data);

        return redirect()->route('seccionk.admin.promociones.index')
            ->with('success', 'Promoción registrada exitosamente.');
    }

    public function edit(SeccionKPromocion $promocion)
    {
        return view('seccionk.admin.promociones.form', compact('promocion'));
    }

    public function update(Request $request, SeccionKPromocion $promocion)
    {
        $data = $request->validate([
            'dni' => 'required|digits:8|unique:seccionk_promociones,dni,' . $promocion->id,
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:15',
            'participa_cuotas' => 'boolean',
            'referencias' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $promocion->update($data);

        return redirect()->route('seccionk.admin.promociones.index')
            ->with('success', 'Promoción actualizada exitosamente.');
    }

    public function destroy(SeccionKPromocion $promocion)
    {
        $promocion->update(['activo' => false]);

        return redirect()->route('seccionk.admin.promociones.index')
            ->with('success', 'Promoción ocultada exitosamente.');
    }
}
