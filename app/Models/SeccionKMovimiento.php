<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class SeccionKMovimiento extends Model
{
    public const TIPO_PRESTAMO = 'prestamo';
    public const TIPO_DEVOLUCION = 'devolucion';
    public const TIPO_INGRESO = 'ingreso';
    public const TIPO_EGRESO = 'egreso';

    protected $table = 'seccionk_movimientos';

    protected $fillable = [
        'tipo',
        'persona',
        'monto',
        'fecha',
        'comentario',
        'prestamo_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    public static function tipos(): array
    {
        return [
            self::TIPO_PRESTAMO => 'Préstamo (egreso)',
            self::TIPO_DEVOLUCION => 'Devolución de préstamo (ingreso)',
            self::TIPO_INGRESO => 'Ingreso',
            self::TIPO_EGRESO => 'Egreso',
        ];
    }

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'prestamo_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(self::class, 'prestamo_id');
    }

    public function esPrestamo(): bool
    {
        return $this->tipo === self::TIPO_PRESTAMO;
    }

    public function etiquetaTipo(): string
    {
        return self::tipos()[$this->tipo] ?? $this->tipo;
    }

    public function esEgreso(): bool
    {
        return in_array($this->tipo, [self::TIPO_PRESTAMO, self::TIPO_EGRESO], true);
    }

    public function montoDevuelto(): float
    {
        return (float) $this->devoluciones->sum('monto');
    }

    public function saldoPendiente(): float
    {
        if (! $this->esPrestamo()) {
            return 0.0;
        }

        return max((float) $this->monto - $this->montoDevuelto(), 0);
    }

    public function estaPendiente(): bool
    {
        return $this->esPrestamo() && $this->saldoPendiente() > 0.009;
    }

    public static function prestamosPendientes(): Collection
    {
        return self::query()
            ->where('tipo', self::TIPO_PRESTAMO)
            ->with('devoluciones')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (self $prestamo) => $prestamo->estaPendiente())
            ->values();
    }

    public static function totalPrestamosPendientes(): float
    {
        return (float) self::prestamosPendientes()->sum(fn (self $prestamo) => $prestamo->saldoPendiente());
    }

    public static function resumenFondo(): array
    {
        $movimientos = self::query()
            ->with(['prestamo', 'devoluciones'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $pendientes = $movimientos
            ->where('tipo', self::TIPO_PRESTAMO)
            ->filter(fn (self $prestamo) => $prestamo->estaPendiente())
            ->values();

        $ingresos = (float) $movimientos
            ->filter(fn (self $mov) => ! $mov->esEgreso())
            ->sum('monto');
        $egresos = (float) $movimientos
            ->filter(fn (self $mov) => $mov->esEgreso())
            ->sum('monto');

        return [
            'movimientos' => $movimientos,
            'prestamos_pendientes' => $pendientes,
            'total_prestamos_pendientes' => (float) $pendientes->sum(fn (self $prestamo) => $prestamo->saldoPendiente()),
            'total_ingresos' => $ingresos,
            'total_egresos' => $egresos,
        ];
    }
}
