<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TarifaService
{
    private const ARCHIVO = 'tarifas.json';

    public function rutaArchivo(): string
    {
        return storage_path('app/'.self::ARCHIVO);
    }

    /** @return array<string, mixed> */
    public function leerOverrides(): array
    {
        $ruta = $this->rutaArchivo();
        if (! File::exists($ruta)) {
            return [];
        }

        $datos = json_decode(File::get($ruta), true);

        return is_array($datos) ? $datos : [];
    }

    public function aplicarEnConfig(): void
    {
        $overrides = $this->leerOverrides();

        if (isset($overrides['arranque']['monto'])) {
            config(['planes.arranque.monto' => (float) $overrides['arranque']['monto']]);
        }

        foreach (['basico', 'negocio', 'pro', 'beta'] as $plan) {
            if (isset($overrides['planes'][$plan]['mensual'])) {
                config(["planes.{$plan}.mensual" => (float) $overrides['planes'][$plan]['mensual']]);
            }
        }

        $modulos = config('permisos.modulos_contratables', []);
        foreach ($overrides['modulos'] ?? [] as $clave => $precio) {
            if (isset($modulos[$clave])) {
                $modulos[$clave]['precio_mensual'] = (float) $precio;
            }
        }
        if ($modulos !== []) {
            config(['permisos.modulos_contratables' => $modulos]);
        }

        if (isset($overrides['extras_default'])) {
            config(['extras.precio_mensual_default' => (float) $overrides['extras_default']]);
        }

        if (isset($overrides['usuario_extra'])) {
            config(['planes.usuario_extra.precio_mensual' => (float) $overrides['usuario_extra']]);
        }

        $extras = config('extras.disponibles', []);
        foreach ($overrides['extras'] ?? [] as $clave => $precio) {
            if (isset($extras[$clave])) {
                $extras[$clave]['precio_mensual'] = (float) $precio;
            }
        }
        if ($extras !== []) {
            config(['extras.disponibles' => $extras]);
        }
    }

    public function guardarDesdeRequest(Request $request): void
    {
        $modulosValidos = array_keys(config('permisos.modulos_contratables', []));
        $extrasValidos = array_keys(config('extras.disponibles', []));

        $reglas = [
            'arranque_monto' => 'required|numeric|min:0',
            'plan_basico_mensual' => 'required|numeric|min:0',
            'plan_negocio_mensual' => 'required|numeric|min:0',
            'plan_pro_mensual' => 'required|numeric|min:0',
            'extras_default' => 'required|numeric|min:0',
            'usuario_extra_mensual' => 'required|numeric|min:0',
        ];

        foreach ($modulosValidos as $clave) {
            $reglas["modulo_{$clave}"] = 'required|numeric|min:0';
        }
        foreach ($extrasValidos as $clave) {
            $reglas["extra_{$clave}"] = 'required|numeric|min:0';
        }

        $datos = $request->validate($reglas);

        $payload = [
            'arranque' => ['monto' => (float) $datos['arranque_monto']],
            'planes' => [
                'basico' => ['mensual' => (float) $datos['plan_basico_mensual']],
                'negocio' => ['mensual' => (float) $datos['plan_negocio_mensual']],
                'pro' => ['mensual' => (float) $datos['plan_pro_mensual']],
            ],
            'modulos' => [],
            'extras_default' => (float) $datos['extras_default'],
            'usuario_extra' => (float) $datos['usuario_extra_mensual'],
            'extras' => [],
            'actualizado_en' => now()->toIso8601String(),
        ];

        foreach ($modulosValidos as $clave) {
            $payload['modulos'][$clave] = (float) $datos["modulo_{$clave}"];
        }
        foreach ($extrasValidos as $clave) {
            $payload['extras'][$clave] = (float) $datos["extra_{$clave}"];
        }

        File::ensureDirectoryExists(storage_path('app'));
        File::put($this->rutaArchivo(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function restaurarDefaults(): void
    {
        if (File::exists($this->rutaArchivo())) {
            File::delete($this->rutaArchivo());
        }
    }
}
