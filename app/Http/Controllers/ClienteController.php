<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $q = trim($request->get('q', ''));

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('numero_documento', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('app.clientes.index', compact('clientes', 'q'));
    }

    public function crear()
    {
        return view('app.clientes.form', ['cliente' => new Cliente]);
    }

    public function guardar(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $datos = $request->validate([
            'tipo_documento' => 'required|in:dni,ruc,ce,otro',
            'numero_documento' => 'required|string|max:15',
            'nombre' => 'required|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        Cliente::create(array_merge($datos, [
            'empresa_id' => $empresaId,
            'activo' => true,
        ]));

        return redirect()->route('clientes.index')->with('ok', 'Cliente registrado.');
    }

    public function editar(Cliente $cliente)
    {
        $this->autorizar($cliente);

        return view('app.clientes.form', compact('cliente'));
    }

    public function actualizar(Request $request, Cliente $cliente)
    {
        $this->autorizar($cliente);

        $datos = $request->validate([
            'tipo_documento' => 'required|in:dni,ruc,ce,otro',
            'numero_documento' => 'required|string|max:15',
            'nombre' => 'required|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $cliente->update($datos);

        return redirect()->route('clientes.index')->with('ok', 'Cliente actualizado.');
    }

    private function autorizar(Cliente $cliente): void
    {
        if ($cliente->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }
    }
}
