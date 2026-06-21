<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CierreCaja;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\Producto;
use App\Services\AnulacionService;
use App\Services\CierreCajaService;
use App\Services\QrService;
use App\Services\SesionCajeroService;
use App\Services\SunatService;
use App\Services\TerminalSesionService;
use App\Services\VentaService;
use App\Services\VentaSuspendidaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(
        private VentaService $ventaService,
        private VentaSuspendidaService $suspendidaService,
        private CierreCajaService $cierreService,
        private SesionCajeroService $sesionCajero,
        private AnulacionService $anulacionService,
        private TerminalSesionService $terminalSesion,
        private SunatService $sunatService,
    ) {}

    public function index()
    {
        $usuario = Auth::user();

        if ($respuesta = $this->prepararSesionPos($usuario)) {
            return $respuesta;
        }

        return view('app.pos.index', $this->datosVistaPos($usuario));
    }

    public function movil()
    {
        $usuario = Auth::user();

        if ($respuesta = $this->prepararSesionPos($usuario)) {
            return $respuesta;
        }

        return view('app.pos.movil', $this->datosVistaPos($usuario));
    }

    private function prepararSesionPos($usuario)
    {
        if ($this->terminalSesion->requiereTerminal($usuario)
            && $this->terminalSesion->terminalesDisponibles($usuario)->isEmpty()) {
            return view('app.pos.sin-terminal');
        }

        if (! $this->terminalSesion->terminalActivo()) {
            $this->terminalSesion->inicializarSesion($usuario);
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function datosVistaPos($usuario): array
    {
        $empresaId = $usuario->empresa_id;
        $categorias = Categoria::where('empresa_id', $empresaId)
            ->orderBy('orden')
            ->get(['id', 'nombre']);

        $preview = $this->ventaService->previewSiguiente($usuario, 'boleta');
        $previewFactura = $this->ventaService->previewSiguiente($usuario, 'factura');
        $terminalActiva = $this->terminalSesion->datosActivos();
        $terminalesDisponibles = $this->terminalSesion->terminalesDisponibles($usuario);

        return compact(
            'categorias',
            'preview',
            'previewFactura',
            'usuario',
            'terminalActiva',
            'terminalesDisponibles',
        );
    }

    public function cambiarTerminal(Request $request)
    {
        $datos = $request->validate([
            'terminal_id' => 'required|integer',
        ]);

        try {
            $terminal = $this->terminalSesion->cambiarTerminal(
                Auth::user(),
                (int) $datos['terminal_id'],
            );

            return response()->json([
                'ok' => true,
                'terminal' => [
                    'id' => $terminal->id,
                    'codigo' => $terminal->codigo,
                    'nombre' => $terminal->nombre,
                    'local' => $terminal->local ?? '',
                    'caja' => $terminal->caja,
                ],
                'preview_boleta' => $this->ventaService->previewSiguiente(Auth::user(), 'boleta'),
                'preview_factura' => $this->ventaService->previewSiguiente(Auth::user(), 'factura'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function resumenTurno()
    {
        $usuario = Auth::user();
        if ($usuario->rol !== 'cajero' || ! $usuario->en_sesion) {
            return response()->json(['ok' => false, 'error' => 'Sin turno activo'], 403);
        }

        return response()->json([
            'ok' => true,
            'resumen' => $this->cierreService->resumenActual($usuario),
            'cierre_x_completado' => (bool) session('cierre_x_completado', false),
        ]);
    }

    public function cierreX(Request $request)
    {
        $usuario = Auth::user();
        if ($usuario->rol !== 'cajero' || ! $usuario->en_sesion) {
            return response()->json(['ok' => false, 'error' => 'Solo cajeros en sesión'], 403);
        }

        if (session('cierre_x_completado')) {
            return response()->json(['ok' => false, 'error' => 'El Cierre X ya fue registrado.'], 422);
        }

        $datos = $request->validate([
            'supervisor_usuario' => 'required|string|max:60',
            'supervisor_password' => 'required|string',
            'monto_efectivo_contado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $supervisor = $this->anulacionService->validarSupervisor(
            $usuario->empresa_id,
            $datos['supervisor_usuario'],
            $datos['supervisor_password'],
        );

        if (! $supervisor) {
            return response()->json(['ok' => false, 'error' => 'Usuario o clave de supervisor incorrectos.'], 422);
        }

        try {
            $cierre = $this->sesionCajero->cierreXDesdePos(
                $usuario,
                $supervisor,
                (float) $datos['monto_efectivo_contado'],
                $datos['observaciones'] ?? null,
            );

            session([
                'cierre_x_completado' => true,
                'ultimo_cierre_id' => $cierre->id,
            ]);

            return response()->json([
                'ok' => true,
                'cierre_id' => $cierre->id,
                'ticket_url' => route('pos.ticket-cierre', $cierre),
                'mensaje' => 'Cierre X registrado. Ya puede cerrar sesión.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function ticketCierre(CierreCaja $cierre)
    {
        $usuario = Auth::user();
        if ($cierre->empresa_id !== $usuario->empresa_id || $cierre->usuario_id !== $usuario->id) {
            abort(404);
        }

        $cierre->load(['usuario', 'supervisor', 'empresa']);

        $porCajero = collect();

        return view('app.cierres.ticket', compact('cierre', 'porCajero'));
    }

    public function cerrarSesion(Request $request)
    {
        $usuario = Auth::user();
        if ($usuario->rol !== 'cajero' || ! $usuario->en_sesion) {
            return redirect()->route('logout');
        }

        if (! session('cierre_x_completado')) {
            return back()->withErrors(['cierre' => 'Debe completar el Cierre X con supervisor antes de cerrar sesión.']);
        }

        $this->sesionCajero->cerrarSesion($usuario);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('ok', 'Sesión cerrada correctamente.');
    }

    public function buscar(Request $request)
    {
        $q = trim($request->get('q', ''));
        $empresaId = Auth::user()->empresa_id;

        if ($q === '') {
            return response()->json([]);
        }

        $productos = Producto::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo_barra', $q)
                    ->orWhere('sku', $q);
            })
            ->limit(15)
            ->get(['id', 'nombre', 'codigo_barra', 'sku', 'precio_venta', 'stock_disponible', 'stock_minimo', 'unidad_medida', 'afecto_igv']);

        return response()->json($productos);
    }

    public function buscarExacto(Request $request)
    {
        $q = trim($request->get('q', ''));
        $empresaId = Auth::user()->empresa_id;

        if ($q === '') {
            return response()->json(null);
        }

        $producto = Producto::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where(function ($query) use ($q) {
                $query->where('codigo_barra', $q)->orWhere('sku', $q);
            })
            ->first(['id', 'nombre', 'codigo_barra', 'sku', 'precio_venta', 'stock_disponible', 'stock_minimo', 'unidad_medida', 'afecto_igv']);

        return response()->json($producto);
    }

    public function masVendidos(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $categoriaId = $request->get('categoria_id');

        $campos = [
            'productos.id', 'productos.nombre', 'productos.codigo_barra', 'productos.sku',
            'productos.precio_venta', 'productos.stock_disponible', 'productos.stock_minimo', 'productos.unidad_medida', 'productos.afecto_igv',
        ];

        $ranking = DetalleComprobante::query()
            ->join('comprobantes', 'comprobantes.id', '=', 'detalle_comprobante.comprobante_id')
            ->join('productos', 'productos.id', '=', 'detalle_comprobante.producto_id')
            ->where('comprobantes.empresa_id', $empresaId)
            ->where('comprobantes.estado', 'emitido')
            ->where('productos.activo', true);

        if ($categoriaId && $categoriaId !== 'todos') {
            $ranking->where('productos.categoria_id', (int) $categoriaId);
        }

        $top = $ranking
            ->select('detalle_comprobante.producto_id', DB::raw('SUM(detalle_comprobante.cantidad) as total_vendido'))
            ->groupBy('detalle_comprobante.producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        if ($top->isEmpty()) {
            $consulta = Producto::where('empresa_id', $empresaId)->where('activo', true);
            if ($categoriaId && $categoriaId !== 'todos') {
                $consulta->where('categoria_id', (int) $categoriaId);
            }

            return response()->json(
                $consulta->orderBy('nombre')->limit(5)->get($campos)
                    ->map(fn ($p) => array_merge($p->toArray(), ['total_vendido' => 0]))
            );
        }

        $vendidos = $top->keyBy('producto_id');
        $productos = Producto::whereIn('id', $top->pluck('producto_id'))->get($campos);

        $ordenados = $top->pluck('producto_id')->map(function ($id) use ($productos, $vendidos) {
            $producto = $productos->firstWhere('id', $id);
            if (! $producto) {
                return null;
            }

            return array_merge($producto->toArray(), [
                'total_vendido' => (float) $vendidos[$id]->total_vendido,
            ]);
        })->filter()->values();

        return response()->json($ordenados);
    }

    public function buscarCliente(Request $request)
    {
        $doc = trim($request->get('doc', ''));
        $empresaId = Auth::user()->empresa_id;

        if ($doc === '') {
            return response()->json(null);
        }

        $cliente = Cliente::where('empresa_id', $empresaId)
            ->where('numero_documento', $doc)
            ->where('activo', true)
            ->first(['id', 'nombre', 'numero_documento', 'tipo_documento']);

        return response()->json($cliente);
    }

    public function guardarCliente(Request $request)
    {
        $datos = $request->validate([
            'tipo_documento' => 'required|in:dni,ruc,ce,otro',
            'numero_documento' => 'required|string|max:15',
            'nombre' => 'required|string|max:200',
            'telefono' => 'nullable|string|max:20',
        ]);

        $doc = preg_replace('/\D/', '', $datos['numero_documento']);
        if ($datos['tipo_documento'] === 'dni' && strlen($doc) !== 8) {
            return response()->json(['ok' => false, 'error' => 'El DNI debe tener 8 dígitos.'], 422);
        }
        if ($datos['tipo_documento'] === 'ruc' && strlen($doc) !== 11) {
            return response()->json(['ok' => false, 'error' => 'El RUC debe tener 11 dígitos.'], 422);
        }

        $empresaId = Auth::user()->empresa_id;
        $datos['numero_documento'] = $doc;

        $cliente = Cliente::updateOrCreate(
            ['empresa_id' => $empresaId, 'numero_documento' => $doc],
            array_merge($datos, ['activo' => true])
        );

        return response()->json([
            'ok' => true,
            'id' => $cliente->id,
            'nombre' => $cliente->nombre,
            'numero_documento' => $cliente->numero_documento,
            'tipo_documento' => $cliente->tipo_documento,
        ]);
    }

    public function cobrar(Request $request)
    {
        $datos = $request->validate([
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|integer',
            'lineas.*.cantidad' => 'required|numeric|min:0.01',
            'lineas.*.descuento_pct' => 'nullable|numeric|min:0|max:100',
            'medio_pago' => 'required|in:efectivo,yape,plin,tarjeta,mixto',
            'tipo_documento' => 'required|in:boleta,factura',
            'cliente_id' => 'nullable|integer',
            'descuento_global' => 'nullable|numeric|min:0',
            'monto_recibido' => 'nullable|numeric|min:0',
            'pagos_mixtos' => 'nullable|array',
            'pagos_mixtos.*.medio' => 'required_with:pagos_mixtos|in:efectivo,yape,plin,tarjeta',
            'pagos_mixtos.*.monto' => 'required_with:pagos_mixtos|numeric|min:0',
        ]);

        try {
            $comprobante = $this->ventaService->registrar(
                Auth::user(),
                $datos['lineas'],
                $datos['medio_pago'],
                $datos['cliente_id'] ?? null,
                (float) ($datos['descuento_global'] ?? 0),
                isset($datos['monto_recibido']) ? (float) $datos['monto_recibido'] : null,
                $datos['tipo_documento'],
                $datos['pagos_mixtos'] ?? null,
            );

            try {
                $comprobante = $this->sunatService->emitir($comprobante);
            } catch (\Throwable $e) {
                $comprobante->update([
                    'sunat_estado' => 'error',
                    'sunat_mensaje' => $e->getMessage(),
                ]);
                $comprobante = $comprobante->fresh();
            }

            $tipoDoc = $datos['tipo_documento'];

            return response()->json([
                'ok' => true,
                'numero' => $comprobante->numero_interno,
                'tipo_documento' => $tipoDoc,
                'tipo_etiqueta' => $tipoDoc === 'factura' ? 'Factura' : 'Boleta',
                'serie' => $comprobante->serie,
                'correlativo' => $comprobante->numero,
                'total' => $comprobante->total,
                'vuelto' => $comprobante->vuelto,
                'ticket_url' => route('pos.ticket', $comprobante),
                'sunat_estado' => $comprobante->sunat_estado,
                'sunat_mensaje' => $comprobante->sunat_mensaje,
                'preview' => $this->ventaService->previewSiguiente(Auth::user(), $tipoDoc),
                'preview_boleta' => $this->ventaService->previewSiguiente(Auth::user(), 'boleta'),
                'preview_factura' => $this->ventaService->previewSiguiente(Auth::user(), 'factura'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function suspender(Request $request)
    {
        $datos = $request->validate([
            'lineas' => 'required|array|min:1',
            'total' => 'required|numeric|min:0',
            'cliente_id' => 'nullable|integer',
            'descuento_global' => 'nullable|numeric|min:0',
        ]);

        try {
            $hold = $this->suspendidaService->suspender(Auth::user(), $request->all());

            return response()->json([
                'ok' => true,
                'codigo_hold' => $hold->codigo_hold,
                'total' => $hold->total,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function recuperar(Request $request)
    {
        $datos = $request->validate(['codigo_hold' => 'required|string|size:13']);

        try {
            $venta = $this->suspendidaService->recuperar(Auth::user(), $datos['codigo_hold']);

            return response()->json(['ok' => true, 'venta' => $venta]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function listarSuspendidas()
    {
        return response()->json(
            $this->suspendidaService->listar(Auth::user()->empresa_id)
        );
    }

    public function ticket(Comprobante $comprobante, QrService $qr)
    {
        if ($comprobante->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }

        $comprobante->load(['detalles', 'cliente', 'empresa', 'usuario', 'terminal']);

        $qrDataUri = null;
        if ($comprobante->esComprobanteElectronico() && $comprobante->textoQrSunat()) {
            $qrDataUri = $qr->pngDataUri($comprobante->textoQrSunat());
        }

        return view('app.pos.ticket', compact('comprobante', 'qrDataUri'));
    }

    public function qrComprobante(Comprobante $comprobante, QrService $qr)
    {
        if ($comprobante->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }

        $texto = $comprobante->textoQrSunat();
        if (! $texto) {
            abort(404);
        }

        return response($qr->pngBinary($texto), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
