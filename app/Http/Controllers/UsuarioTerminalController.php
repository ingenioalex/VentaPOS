<?php

namespace App\Http\Controllers;

use App\Models\Terminal;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsuarioTerminalController extends Controller
{
    /** @return list<string> */
    private function rolesAsignables(): array
    {
        return ['cajero', 'supervisor'];
    }

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $usuarios = Usuario::where('empresa_id', Auth::user()->empresa_id)
            ->whereIn('rol', $this->rolesAsignables())
            ->withCount('terminales')
            ->with(['terminales' => fn ($query) => $query->wherePivot('es_defecto', true)])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('usuario', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('app.usuario-terminales.index', compact('usuarios', 'q'));
    }

    public function editar(Usuario $usuario)
    {
        $this->autorizar($usuario);

        $terminales = Terminal::where('activo', true)->orderBy('codigo')->get();
        $asignadas = $usuario->terminales()->pluck('terminales.id')->all();
        $defectoId = $usuario->terminalDefecto()?->id;

        return view('app.usuario-terminales.form', compact('usuario', 'terminales', 'asignadas', 'defectoId'));
    }

    public function actualizar(Request $request, Usuario $usuario)
    {
        $this->autorizar($usuario);

        $datos = $request->validate([
            'terminales' => 'nullable|array',
            'terminales.*' => [
                'integer',
                Rule::exists('terminales', 'id')->where(fn ($q) => $q->where('empresa_id', Auth::user()->empresa_id)),
            ],
            'terminal_defecto' => [
                'nullable',
                'integer',
                Rule::exists('terminales', 'id')->where(fn ($q) => $q->where('empresa_id', Auth::user()->empresa_id)),
            ],
        ]);

        $terminalIds = collect($datos['terminales'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $defectoId = isset($datos['terminal_defecto']) ? (int) $datos['terminal_defecto'] : null;

        if ($terminalIds->isEmpty()) {
            throw ValidationException::withMessages([
                'terminales' => 'Seleccione al menos una terminal.',
            ]);
        }

        if ($defectoId === null || ! $terminalIds->contains($defectoId)) {
            $defectoId = $terminalIds->first();
        }

        $validas = Terminal::whereIn('id', $terminalIds)->pluck('id');
        if ($validas->count() !== $terminalIds->count()) {
            throw ValidationException::withMessages([
                'terminales' => 'Una o más terminales no son válidas.',
            ]);
        }

        DB::transaction(function () use ($usuario, $terminalIds, $defectoId) {
            $sync = [];
            foreach ($terminalIds as $id) {
                $sync[$id] = ['es_defecto' => $id === $defectoId];
            }
            $usuario->terminales()->sync($sync);
        });

        return redirect()->route('usuario-terminales.index')
            ->with('ok', 'Terminales asignadas a '.$usuario->nombre.'.');
    }

    private function autorizar(Usuario $usuario): void
    {
        if ($usuario->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }

        if (! in_array($usuario->rol, $this->rolesAsignables(), true)) {
            abort(403, 'Solo puede asignar terminales a cajeros y supervisores.');
        }
    }
}
