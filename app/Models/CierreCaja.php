<?php

namespace App\Models;

use App\Models\Concerns\FiltraPorEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CierreCaja extends Model
{
    use FiltraPorEmpresa;

    protected $table = 'cierres_caja';

    const CREATED_AT = 'creado_en';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id', 'usuario_id', 'supervisor_usuario_id', 'tipo_cierre', 'terminal_id', 'terminal', 'caja',
        'fecha_inicio', 'fecha_fin', 'cantidad_ventas', 'total_ventas', 'ticket_promedio',
        'total_efectivo', 'monto_efectivo_contado', 'diferencia_efectivo',
        'total_tarjeta', 'total_otros', 'total_igv', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'total_ventas' => 'decimal:2',
            'ticket_promedio' => 'decimal:2',
            'total_efectivo' => 'decimal:2',
            'total_tarjeta' => 'decimal:2',
            'total_otros' => 'decimal:2',
            'total_igv' => 'decimal:2',
            'monto_efectivo_contado' => 'decimal:2',
            'diferencia_efectivo' => 'decimal:2',
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

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'supervisor_usuario_id');
    }
}
