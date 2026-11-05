<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function declaracion(): HasOne
    {
        return $this->hasOne(SeccionKDeclaracion::class, 'evento_id');
    }

    public function edicion(): HasOne
    {
        return $this->hasOne(SeccionKEventoEdicion::class, 'evento_id');
    }

    public function fechaYaPaso(): bool
    {
        if ($this->tipo !== 'evento' || ! $this->fecha) {
            return false;
        }

        $hoy = now()->timezone('America/Lima')->toDateString();

        return $this->fecha->toDateString() < $hoy;
    }

    public function permiteEditar(): bool
    {
        return ! $this->fechaYaPaso() || (bool) $this->edicion?->abierto;
    }

    public function esSoloReferencia(): bool
    {
        return $this->fechaYaPaso() && ! $this->permiteEditar();
    }

    public function estaCerrado(): bool
    {
        return (bool) $this->declaracion?->cerrado;
    }

    public function montoUtilizado(): float
    {
        return (float) ($this->declaracion?->monto_utilizado ?? 0);
    }

    public function esCuotaMensual(): bool
    {
        return $this->tipo === 'cuota_mensual';
    }

    public static function mesesEspanol(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }

    public static function etiquetaDesdeFecha(mixed $fecha): string
    {
        $fecha = \Carbon\Carbon::parse($fecha);

        return self::mesesEspanol()[$fecha->month].' '.$fecha->year;
    }

    public function claveMes(): string
    {
        if ($this->mes_cuota && preg_match('/^(\p{L}+)\s+(\d{4})$/u', trim($this->mes_cuota), $partes)) {
            return mb_strtolower($partes[1]).' '.$partes[2];
        }

        if ($this->fecha) {
            return mb_strtolower(self::etiquetaDesdeFecha($this->fecha));
        }

        return mb_strtolower(trim((string) $this->mes_cuota));
    }

    public function mesCorto(): string
    {
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

        if ($this->mes_cuota && preg_match('/^(\p{L}+)\s+(\d{4})$/u', trim($this->mes_cuota), $partes)) {
            $abrev = $mapa[mb_strtolower($partes[1])] ?? mb_strtoupper(mb_substr($partes[1], 0, 3));

            return $abrev.substr($partes[2], -2);
        }

        if ($this->fecha) {
            $meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

            return $meses[$this->fecha->month - 1].$this->fecha->format('y');
        }

        return $this->mes_cuota ?: '';
    }

    public function mesAbrev(): string
    {
        if (preg_match('/^([A-ZÁÉÍÓÚÜ]+)/u', $this->mesCorto(), $partes)) {
            return $partes[1];
        }

        return $this->mesCorto();
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
