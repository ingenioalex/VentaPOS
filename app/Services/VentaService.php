<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\Empresa;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VentaService
{
    private const TASA_IGV = 0.18;

    public function __construct(
        private TerminalSesionService $terminalSesion,
        private LoteService $lotes,
    ) {}

    /**
     * @param  array<int, array{producto_id: int, cantidad: float, descuento_pct?: float}>  $lineas
     */
    public function registrar(
        Usuario $usuario,
        array $lineas,
        string $medioPago = 'efectivo',
        ?int $clienteId = null,
        float $descuentoGlobal = 0,
        ?float $montoRecibido = null,
        string $tipoDocumento = 'boleta',
        ?array $pagosMixtos = null,
    ): Comprobante {
        if ($usuario->empresa_id === null) {
            throw new RuntimeException('El usuario no pertenece a una empresa.');
        }

        return DB::transaction(function () use ($usuario, $lineas, $medioPago, $clienteId, $descuentoGlobal, $montoRecibido, $tipoDocumento, $pagosMixtos) {
            $empresaId = $usuario->empresa_id;
            $empresa = Empresa::findOrFail($empresaId);
            $config = $empresa->configuracion ?? [];
            $tipoDocumento = in_array($tipoDocumento, ['boleta', 'factura'], true) ? $tipoDocumento : 'boleta';
            $this->validarClienteDocumento($tipoDocumento, $clienteId, $empresaId);
            $serie = $this->resolverSerie($config, $tipoDocumento);
            $numero = $this->siguienteNumero($empresaId, $serie);
            $numeroInterno = sprintf('%s-%06d', $serie, $numero);

            $subtotal = 0;
            $igvTotal = 0;

            $terminal = $this->terminalSesion->datosActivos();

            $comprobante = Comprobante::create([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuario->id,
                'terminal_id' => $terminal['id'],
                'cliente_id' => $clienteId,
                'numero_interno' => $numeroInterno,
                'tipo_documento' => $tipoDocumento,
                'serie' => $serie,
                'numero' => $numero,
                'subtotal' => 0,
                'valor_igv' => 0,
                'descuento' => 0,
                'total' => 0,
                'medio_pago' => $medioPago,
                'estado' => 'emitido',
                'emitido_en' => now(),
            ]);

            foreach ($lineas as $linea) {
                $producto = Producto::where('empresa_id', $empresaId)
                    ->where('id', $linea['producto_id'])
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidad = (float) $linea['cantidad'];
                if ($cantidad <= 0) {
                    throw new RuntimeException('Cantidad inválida.');
                }
                $descuentoPct = min(100, max(0, (float) ($linea['descuento_pct'] ?? 0)));
                $precio = (float) $producto->precio_venta;
                $bruto = round($precio * $cantidad, 2);
                $descuentoLinea = round($bruto * $descuentoPct / 100, 2);
                $lineaSubtotal = round($bruto - $descuentoLinea, 2);
                $lineaIgv = $producto->afecto_igv
                    ? round($lineaSubtotal * self::TASA_IGV / (1 + self::TASA_IGV), 2)
                    : 0;

                DetalleComprobante::create([
                    'comprobante_id' => $comprobante->id,
                    'producto_id' => $producto->id,
                    'descripcion_producto' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'descuento_pct' => $descuentoPct,
                    'subtotal' => $lineaSubtotal,
                ]);

                $stockAnterior = (float) $producto->stock_disponible;
                $stockNuevo = $stockAnterior - $cantidad;
                $producto->update(['stock_disponible' => $stockNuevo]);

                if ($producto->control_lote) {
                    $this->lotes->descontarVenta($producto, $cantidad, $numeroInterno);
                }

                InventarioMovimiento::create([
                    'empresa_id' => $empresaId,
                    'producto_id' => $producto->id,
                    'usuario_id' => $usuario->id,
                    'tipo_movimiento' => 'venta',
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'referencia' => $numeroInterno,
                ]);

                $subtotal += $lineaSubtotal;
                $igvTotal += $lineaIgv;
            }

            $descuentoGlobal = min($subtotal, max(0, round($descuentoGlobal, 2)));
            $total = round($subtotal - $descuentoGlobal, 2);
            $pago = $this->resolverPago($medioPago, $total, $montoRecibido, $pagosMixtos);

            $comprobante->update([
                'subtotal' => round($subtotal, 2),
                'valor_igv' => round($igvTotal, 2),
                'descuento' => $descuentoGlobal,
                'total' => $total,
                'monto_recibido' => $pago['monto_recibido'],
                'vuelto' => $pago['vuelto'],
                'observaciones' => $pago['observaciones'],
            ]);

            return $comprobante->fresh(['detalles', 'cliente', 'empresa', 'usuario']);
        });
    }

    public function previewSiguiente(Usuario $usuario, string $tipoDocumento = 'boleta'): array
    {
        $empresa = Empresa::findOrFail($usuario->empresa_id);
        $config = $empresa->configuracion ?? [];
        $tipoDocumento = in_array($tipoDocumento, ['boleta', 'factura'], true) ? $tipoDocumento : 'boleta';
        $serie = $this->resolverSerie($config, $tipoDocumento);
        $numero = $this->siguienteNumero($usuario->empresa_id, $serie);
        $terminal = $this->terminalSesion->datosActivos();

        return [
            'tipo_documento' => $tipoDocumento,
            'tipo_etiqueta' => $tipoDocumento === 'factura' ? 'Factura' : 'Boleta',
            'serie' => $serie,
            'numero' => $numero,
            'numero_interno' => sprintf('%s-%06d', $serie, $numero),
            'terminal' => $terminal['codigo'],
            'terminal_nombre' => $terminal['nombre'],
            'terminal_local' => $terminal['local'],
            'caja' => $terminal['caja'],
            'local' => $terminal['local'],
        ];
    }

    private function resolverSerie(array $config, string $tipoDocumento): string
    {
        return $tipoDocumento === 'factura'
            ? ($config['serie_factura'] ?? 'F001')
            : ($config['serie_boleta'] ?? 'B001');
    }

    private function validarClienteDocumento(string $tipoDocumento, ?int $clienteId, int $empresaId): void
    {
        if ($tipoDocumento !== 'factura') {
            return;
        }

        if ($clienteId === null) {
            throw new RuntimeException('La factura requiere un cliente con RUC y razón social.');
        }

        $cliente = Cliente::where('empresa_id', $empresaId)
            ->where('id', $clienteId)
            ->where('activo', true)
            ->first();

        if (! $cliente || $cliente->tipo_documento !== 'ruc' || strlen($cliente->numero_documento) !== 11) {
            throw new RuntimeException('La factura requiere un cliente registrado con RUC válido (11 dígitos).');
        }
    }

    private function siguienteNumero(int $empresaId, string $serie): int
    {
        $ultimo = Comprobante::where('empresa_id', $empresaId)
            ->where('serie', $serie)
            ->max('numero');

        return ((int) $ultimo) + 1;
    }

    /**
     * @param  ?array<int, array{medio: string, monto: float|int|string}>  $pagosMixtos
     * @return array{monto_recibido: ?float, vuelto: ?float, observaciones: ?string}
     */
    private function resolverPago(string $medioPago, float $total, ?float $montoRecibido, ?array $pagosMixtos): array
    {
        if ($medioPago !== 'mixto') {
            if ($medioPago === 'efectivo' && $montoRecibido !== null && $montoRecibido < $total) {
                throw new RuntimeException('Monto recibido insuficiente.');
            }

            return [
                'monto_recibido' => $medioPago === 'efectivo' ? $montoRecibido : null,
                'vuelto' => ($medioPago === 'efectivo' && $montoRecibido !== null)
                    ? round($montoRecibido - $total, 2)
                    : null,
                'observaciones' => null,
            ];
        }

        if (empty($pagosMixtos)) {
            throw new RuntimeException('Indique los montos del pago mixto.');
        }

        $mediosValidos = ['efectivo', 'yape', 'plin', 'tarjeta'];
        $suma = 0.0;
        $efectivoMonto = 0.0;
        $pagos = [];

        foreach ($pagosMixtos as $pago) {
            $medio = $pago['medio'] ?? '';
            $monto = round((float) ($pago['monto'] ?? 0), 2);
            if (! in_array($medio, $mediosValidos, true) || $monto <= 0) {
                continue;
            }
            $suma += $monto;
            if ($medio === 'efectivo') {
                $efectivoMonto += $monto;
            }
            $pagos[] = ['medio' => $medio, 'monto' => $monto];
        }

        if ($pagos === []) {
            throw new RuntimeException('Indique al menos un monto de pago.');
        }

        if (abs($suma - $total) > 0.01) {
            throw new RuntimeException(sprintf(
                'Los pagos mixtos (S/ %.2f) deben sumar el total (S/ %.2f).',
                $suma,
                $total
            ));
        }

        $montoRecibidoEfectivo = null;
        $vuelto = null;
        if ($efectivoMonto > 0) {
            if ($montoRecibido === null || $montoRecibido < $efectivoMonto) {
                throw new RuntimeException('Monto en efectivo recibido insuficiente.');
            }
            $montoRecibidoEfectivo = $montoRecibido;
            $vuelto = round($montoRecibido - $efectivoMonto, 2);
        }

        return [
            'monto_recibido' => $montoRecibidoEfectivo,
            'vuelto' => $vuelto,
            'observaciones' => json_encode(['pagos' => $pagos], JSON_UNESCAPED_UNICODE),
        ];
    }
}
