<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Usuario;

class ImpresoraService
{
    public function defaults(): array
    {
        $defaults = config('impresora.defaults', []);

        return is_array($defaults) && $defaults !== []
            ? $defaults
            : [
                'ancho_mm' => 80,
                'auto_imprimir' => true,
                'mostrar_logo' => true,
                'tamano_fuente' => 'normal',
                'copias' => 1,
                'notas_pie' => '',
                'metodo' => 'navegador',
                'nombre_impresora_sistema' => '',
            ];
    }

    public function empresa(?Empresa $empresa): array
    {
        if (! $empresa) {
            return $this->defaults();
        }

        $cfg = ($empresa->configuracion ?? [])['impresora'] ?? [];

        return $this->normalizar(array_merge($this->defaults(), is_array($cfg) ? $cfg : []));
    }

    public function usuario(Usuario $usuario): array
    {
        $prefs = $usuario->preferencias ?? [];
        $cfg = is_array($prefs['impresora'] ?? null) ? $prefs['impresora'] : [];

        return $this->normalizar(array_merge($this->defaults(), $cfg));
    }

    public function usaEmpresa(Usuario $usuario): bool
    {
        return (bool) (($usuario->preferencias ?? [])['impresora_usa_empresa'] ?? true);
    }

    /** Configuración efectiva para imprimir (usuario o empresa). */
    public function paraUsuario(Usuario $usuario): array
    {
        $base = $this->empresa($usuario->empresa);

        if ($this->usaEmpresa($usuario)) {
            return $base;
        }

        $personal = $this->usuario($usuario);

        return $this->normalizar(array_merge($base, array_filter(
            $personal,
            fn ($v) => $v !== null && $v !== ''
        )));
    }

    public function estilosTicket(array $impresora): array
    {
        $ancho = (int) ($impresora['ancho_mm'] ?? 80);
        $tamano = (string) ($impresora['tamano_fuente'] ?? 'normal');
        $map = config("impresora.css.{$ancho}.{$tamano}")
            ?? config('impresora.css.80.normal')
            ?? ['body' => '13px', 'table' => '11px', 'max' => '360px', 'qr' => '168px'];

        return $map;
    }

    public function guardarEmpresa(Empresa $empresa, array $datos): void
    {
        $config = $empresa->configuracion ?? [];
        $config['impresora'] = $this->normalizar(array_merge($this->defaults(), $datos));
        $empresa->update(['configuracion' => $config]);
    }

    public function guardarUsuario(Usuario $usuario, array $datos, bool $usaEmpresa): void
    {
        $prefs = $usuario->preferencias ?? [];
        $prefs['impresora_usa_empresa'] = $usaEmpresa;

        if ($usaEmpresa) {
            unset($prefs['impresora']);
        } else {
            $prefs['impresora'] = $this->normalizar(array_merge($this->defaults(), $datos));
        }

        $usuario->update(['preferencias' => $prefs]);
    }

    public function reglasValidacion(): array
    {
        $metodos = implode(',', array_keys(config('impresora.metodos', [])));

        return [
            'ancho_mm' => 'required|integer|in:58,80',
            'auto_imprimir' => 'nullable|boolean',
            'mostrar_logo' => 'nullable|boolean',
            'tamano_fuente' => 'required|in:compacta,normal,grande',
            'copias' => 'required|integer|min:1|max:3',
            'notas_pie' => 'nullable|string|max:200',
            'metodo' => 'required|in:'.$metodos,
            'nombre_impresora_sistema' => 'nullable|string|max:120',
        ];
    }

    private function normalizar(array $cfg): array
    {
        $d = $this->defaults();
        $cfg = array_merge($d, $cfg);

        $metodosValidos = array_keys(config('impresora.metodos', []));

        return [
            'ancho_mm' => in_array((int) ($cfg['ancho_mm'] ?? 80), [58, 80], true)
                ? (int) $cfg['ancho_mm'] : (int) ($d['ancho_mm'] ?? 80),
            'auto_imprimir' => filter_var($cfg['auto_imprimir'] ?? $d['auto_imprimir'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_logo' => filter_var($cfg['mostrar_logo'] ?? $d['mostrar_logo'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'tamano_fuente' => in_array($cfg['tamano_fuente'] ?? '', ['compacta', 'normal', 'grande'], true)
                ? $cfg['tamano_fuente'] : ($d['tamano_fuente'] ?? 'normal'),
            'copias' => max(1, min(3, (int) ($cfg['copias'] ?? $d['copias'] ?? 1))),
            'notas_pie' => trim((string) ($cfg['notas_pie'] ?? '')),
            'metodo' => in_array($cfg['metodo'] ?? '', $metodosValidos, true)
                ? $cfg['metodo'] : ($d['metodo'] ?? 'navegador'),
            'nombre_impresora_sistema' => trim((string) ($cfg['nombre_impresora_sistema'] ?? '')),
        ];
    }
}
