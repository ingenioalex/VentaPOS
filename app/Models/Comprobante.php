<?php

namespace App\Models;

use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comprobante extends Model
{
    use FiltraPorEmpresa;

    protected $table = 'comprobantes';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id', 'usuario_id', 'terminal_id', 'cliente_id', 'numero_interno', 'tipo_documento',
        'serie', 'numero', 'subtotal', 'valor_igv', 'descuento', 'total',
        'monto_recibido', 'vuelto', 'medio_pago', 'estado', 'observaciones',
        'motivo_anulacion', 'anulado_por_usuario_id', 'anulado_en', 'emitido_en',
        'sunat_estado', 'sunat_codigo_respuesta', 'sunat_mensaje', 'sunat_hash', 'sunat_xml', 'sunat_cdr', 'sunat_enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'emitido_en' => 'datetime',
            'anulado_en' => 'datetime',
            'sunat_enviado_en' => 'datetime',
            'subtotal' => 'decimal:2',
            'valor_igv' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'terminal_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleComprobante::class, 'comprobante_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'anulado_por_usuario_id');
    }

    /** @return array<int, array{medio: string, monto: float}> */
    public function pagosMixtos(): array
    {
        if ($this->medio_pago !== 'mixto') {
            return [];
        }

        $data = json_decode($this->observaciones ?? '', true);

        return is_array($data['pagos'] ?? null) ? $data['pagos'] : [];
    }

    public function etiquetaMedioPago(): string
    {
        if ($this->medio_pago !== 'mixto') {
            return ucfirst($this->medio_pago);
        }

        $etiquetas = [
            'efectivo' => 'Efectivo',
            'yape' => 'Yape',
            'plin' => 'Plin',
            'tarjeta' => 'Tarjeta',
        ];

        $partes = [];
        foreach ($this->pagosMixtos() as $pago) {
            $medio = $pago['medio'] ?? '';
            $monto = number_format((float) ($pago['monto'] ?? 0), 2);
            $partes[] = ($etiquetas[$medio] ?? ucfirst($medio)) . ' S/ ' . $monto;
        }

        return $partes ? 'Mixto (' . implode(' + ', $partes) . ')' : 'Mixto';
    }

    public function etiquetaSunat(): string
    {
        return match ($this->sunat_estado ?? 'omitido') {
            'pendiente' => 'Pendiente SUNAT',
            'simulado' => 'Simulado (demo)',
            'aceptado' => 'Aceptado SUNAT',
            'rechazado' => 'Rechazado SUNAT',
            'error' => 'Error SUNAT',
            default => 'Sin envío electrónico',
        };
    }

    public function sunatRequiereAccion(): bool
    {
        return in_array($this->sunat_estado, ['rechazado', 'error', 'pendiente'], true);
    }

    public function esComprobanteElectronico(): bool
    {
        return in_array($this->sunat_estado, ['simulado', 'aceptado', 'pendiente', 'rechazado', 'error'], true)
            && in_array($this->tipo_documento, ['boleta', 'factura'], true);
    }

    public function textoQrSunat(): ?string
    {
        if (! $this->esComprobanteElectronico() || ! $this->empresa?->ruc || ! $this->sunat_hash) {
            return null;
        }

        $tipo = config('sunat.tipos_comprobante.'.$this->tipo_documento, '03');
        $tipoCliente = config('sunat.tipos_documento_cliente.'.$this->cliente?->tipo_documento, '-');
        $numCliente = $this->cliente?->numero_documento ?? '-';

        return implode('|', [
            $this->empresa->ruc,
            $tipo,
            $this->serie,
            str_pad((string) $this->numero, 8, '0', STR_PAD_LEFT),
            number_format((float) $this->valor_igv, 2, '.', ''),
            number_format((float) $this->total, 2, '.', ''),
            $this->emitido_en->format('d-m-Y'),
            $tipoCliente,
            $numCliente,
            $this->sunat_hash,
        ]);
    }

    /** @return array{texto: string, titulo: string, clase: string} */
    public function badgeElectronico(): array
    {
        $tipoDoc = $this->tipo_documento === 'factura' ? 'E-F' : 'E-B';
        $tipoNombre = $this->tipo_documento === 'factura' ? 'Factura' : 'Boleta';

        return match ($this->sunat_estado ?? 'omitido') {
            'simulado' => [
                'texto' => "☑ {$tipoDoc}·SIM",
                'titulo' => "{$tipoNombre} electrónica generada (modo simulación)",
                'clase' => 'cpe-sim',
            ],
            'aceptado' => [
                'texto' => "☑ {$tipoDoc}·PRO",
                'titulo' => "{$tipoNombre} electrónica aceptada por SUNAT (producción)",
                'clase' => 'cpe-pro',
            ],
            'pendiente' => [
                'texto' => "◷ {$tipoDoc}",
                'titulo' => "{$tipoNombre} electrónica pendiente de envío SUNAT",
                'clase' => 'cpe-pnd',
            ],
            'rechazado' => [
                'texto' => "✗ {$tipoDoc}",
                'titulo' => "{$tipoNombre} electrónica rechazada por SUNAT",
                'clase' => 'cpe-err',
            ],
            'error' => [
                'texto' => "✗ {$tipoDoc}",
                'titulo' => "{$tipoNombre} electrónica con error de envío",
                'clase' => 'cpe-err',
            ],
            default => [
                'texto' => '☐ INT',
                'titulo' => 'Comprobante interno (sin emisión electrónica SUNAT)',
                'clase' => 'cpe-no',
            ],
        };
    }
}
