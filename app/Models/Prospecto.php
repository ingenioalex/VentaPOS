<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    protected $table = 'prospectos';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre_negocio', 'nombre_contacto', 'telefono', 'email',
        'tipo_negocio', 'mensaje', 'origen', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    /** @return list<string> */
    public static function estados(): array
    {
        return ['nuevo', 'contactado', 'demo', 'beta', 'cliente', 'descartado'];
    }

    /** @return list<string> */
    public static function origenes(): array
    {
        return ['landing', 'beta', 'whatsapp', 'referido'];
    }

    public function etiquetaEstado(): string
    {
        return match ($this->estado) {
            'nuevo' => 'Nuevo',
            'contactado' => 'Contactado',
            'demo' => 'Demo',
            'beta' => 'En beta',
            'cliente' => 'Cliente',
            'descartado' => 'Descartado',
            default => ucfirst((string) $this->estado),
        };
    }

    public function etiquetaOrigen(): string
    {
        return match ($this->origen) {
            'landing' => 'Landing',
            'beta' => 'Formulario beta',
            'whatsapp' => 'WhatsApp',
            'referido' => 'Referido',
            default => ucfirst((string) $this->origen),
        };
    }

    public function whatsappUrl(): string
    {
        $tel = preg_replace('/\D/', '', (string) $this->telefono);
        if ($tel !== '' && ! str_starts_with($tel, '51')) {
            $tel = '51'.$tel;
        }

        return 'https://wa.me/'.$tel;
    }
}
