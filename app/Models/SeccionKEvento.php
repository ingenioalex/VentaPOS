<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionKEvento extends Model
{
    protected $table = 'seccionk_eventos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha',
        'monto',
        'tipo',
        'mes_cuota',
        'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function participaciones(): HasMany
    {
        return $this->hasMany(SeccionKParticipacion::class, 'evento_id');
    }

    public function esCuotaMensual(): bool
    {
        return $this->tipo === 'cuota_mensual';
    }

    public function mesCorto(): string
    {
        if ($this->fecha) {
            $meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

            return $meses[$this->fecha->month - 1].$this->fecha->format('y');
        }

        if (! $this->mes_cuota) {
            return '';
        }

        $mapa = [
            'enero' => 'ENE',
            'febrero' => 'FEB',
            'marzo' => 'MAR',
            'abril' => 'ABR',
            'mayo' => 'MAY',
            'junio' => 'JUN',
            'julio' => 'JUL',
            'agosto' => 'AGO',
            'septiembre' => 'SEP',
            'octubre' => 'OCT',
            'noviembre' => 'NOV',
            'diciembre' => 'DIC',
        ];

        if (preg_match('/^(\p{L}+)\s+(\d{4})$/u', trim($this->mes_cuota), $partes)) {
            $abrev = $mapa[mb_strtolower($partes[1])] ?? mb_strtoupper(mb_substr($partes[1], 0, 3));

            return $abrev.substr($partes[2], -2);
        }

        return $this->mes_cuota;
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeCuotasMensuales($query)
    {
        return $query->where('tipo', 'cuota_mensual');
    }

    public function scopeEventos($query)
    {
        return $query->where('tipo', 'evento');
    }
}
