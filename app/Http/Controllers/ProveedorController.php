<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $q = trim($request->get('q', ''));

        $proveedores = Proveedor::where('empresa_id', $empresaId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('razon_social', 'like', "%{$q}%")
                        ->orWhere('numero_documento', 'like', "%{$q}%");
                });
            })
            ->orderBy('razon_social')
            ->paginate(20)
            ->withQueryString();

        return view('app.proveedores.index', compact('proveedores', 'q'));
    }

    public function crear()
    {
        return view('app.proveedores.form', ['proveedor' => new Proveedor]);
    }

    public function guardar(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $datos = $this->validar($request, $empresaId);

        Proveedor::create(array_merge($datos, [
            'empresa_id' => $empresaId,
            'activo' => true,
        ]));

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor registrado.');
    }

    public function editar(Proveedor $proveedor)
    {
        $this->autorizar($proveedor);

        return view('app.proveedores.form', compact('proveedor'));
    }

    public function actualizar(Request $request, Proveedor $proveedor)
    {
        $this->autorizar($proveedor);

        $datos = $this->validar($request, $proveedor->empresa_id, $proveedor->id);
        $proveedor->update($datos);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado.');
    }

    private function validar(Request $request, int $empresaId, ?int $exceptoId = null): array
    {
        return $request->validate([
            'tipo_documento' => 'required|in:ruc,dni,ce,otro',
            'numero_documento' => [
                'required', 'string', 'max:15',
                Rule::unique('proveedores', 'numero_documento')
                    ->where('empresa_id', $empresaId)
                    ->ignore($exceptoId),
            ],
            'razon_social' => 'required|string|max:200',
            'nombre_contacto' => 'nullable|string|max:120',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);
    }

    private function autorizar(Proveedor $proveedor): void
    {
        if ($proveedor->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }
    }
}
