<?php

namespace App\Http\Controllers;

use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TerminalController extends Controller
{
    public function index()
    {
        $terminales = Terminal::orderBy('codigo')->paginate(25);

        return view('app.terminales.index', compact('terminales'));
    }

    public function crear()
    {
        return view('app.terminales.form', ['terminal' => new Terminal]);
    }

    public function guardar(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $datos = $this->validar($request, $empresaId);

        Terminal::create([
            ...$datos,
            'empresa_id' => $empresaId,
            'activo' => true,
        ]);

        return redirect()->route('terminales.index')->with('ok', 'Terminal registrada.');
    }

    public function editar(Terminal $terminal)
    {
        return view('app.terminales.form', compact('terminal'));
    }

    public function actualizar(Request $request, Terminal $terminal)
    {
        $datos = $this->validar($request, $terminal->empresa_id, $terminal);
        $datos['activo'] = $request->boolean('activo', true);
        $terminal->update($datos);

        return redirect()->route('terminales.index')->with('ok', 'Terminal actualizada.');
    }

    private function validar(Request $request, int $empresaId, ?Terminal $terminal = null): array
    {
        return $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('terminales', 'codigo')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($terminal?->id),
            ],
            'nombre' => 'required|string|max:100',
            'local' => 'nullable|string|max:100',
            'caja' => 'required|string|max:10',
        ]);
    }
}
