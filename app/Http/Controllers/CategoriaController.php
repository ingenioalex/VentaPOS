<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Services\ExtrasEmpresaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function __construct(private ExtrasEmpresaService $extras) {}

    public function index()
    {
        $empresa = Auth::user()->empresa;
        $jerarquicas = $empresa && $this->extras->activo($empresa, 'categorias_jerarquicas');

        $categorias = Categoria::with(['parent', 'children'])
            ->withCount('productos')
            ->when($jerarquicas, fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        if ($jerarquicas) {
            $todas = Categoria::orderBy('nombre')->get();
        } else {
            $todas = $categorias;
        }

        return view('app.categorias.index', compact('categorias', 'jerarquicas', 'todas'));
    }

    public function guardar(Request $request)
    {
        $empresa = Auth::user()->empresa;
        $jerarquicas = $empresa && $this->extras->activo($empresa, 'categorias_jerarquicas');

        $reglas = [
            'nombre' => 'required|string|max:100',
            'orden' => 'nullable|integer|min:0',
        ];

        if ($jerarquicas) {
            $reglas['parent_id'] = 'nullable|integer';
        }

        $datos = $request->validate($reglas);

        if ($jerarquicas && ! empty($datos['parent_id'])) {
            $padre = Categoria::where('id', $datos['parent_id'])->first();
            if (! $padre || $padre->parent_id) {
                return back()->withErrors(['parent_id' => 'Seleccione una categoría padre válida (solo un nivel).']);
            }
        }

        Categoria::create([
            'empresa_id' => Auth::user()->empresa_id,
            'parent_id' => $jerarquicas ? ($datos['parent_id'] ?? null) : null,
            'nombre' => strtoupper(trim($datos['nombre'])),
            'orden' => $datos['orden'] ?? 0,
        ]);

        return redirect()->route('categorias.index')->with('ok', 'Categoría creada.');
    }

    public function actualizar(Request $request, Categoria $categoria)
    {
        $this->autorizar($categoria);
        $empresa = Auth::user()->empresa;
        $jerarquicas = $empresa && $this->extras->activo($empresa, 'categorias_jerarquicas');

        $reglas = [
            'nombre' => 'required|string|max:100',
            'orden' => 'nullable|integer|min:0',
        ];

        if ($jerarquicas) {
            $reglas['parent_id'] = [
                'nullable', 'integer',
                Rule::notIn([$categoria->id]),
            ];
        }

        $datos = $request->validate($reglas);

        if ($jerarquicas && ! empty($datos['parent_id'])) {
            $padre = Categoria::where('id', $datos['parent_id'])->first();
            if (! $padre || $padre->parent_id || $padre->id === $categoria->id) {
                return back()->withErrors(['parent_id' => 'Categoría padre inválida.']);
            }
        }

        $categoria->update([
            'nombre' => strtoupper(trim($datos['nombre'])),
            'orden' => $datos['orden'] ?? $categoria->orden,
            'parent_id' => $jerarquicas ? ($datos['parent_id'] ?? null) : null,
        ]);

        return redirect()->route('categorias.index')->with('ok', 'Categoría actualizada.');
    }

    public function eliminar(Categoria $categoria)
    {
        $this->autorizar($categoria);

        if ($categoria->productos()->exists()) {
            return back()->withErrors(['nombre' => 'No se puede eliminar: tiene productos asociados.']);
        }

        if ($categoria->children()->exists()) {
            return back()->withErrors(['nombre' => 'No se puede eliminar: tiene subcategorías. Reasígnelas primero.']);
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('ok', 'Categoría eliminada.');
    }

    private function autorizar(Categoria $categoria): void
    {
        if ($categoria->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }
    }
}
