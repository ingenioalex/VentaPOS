<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Terminal;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class OnboardingService
{
    public function completado(Empresa $empresa): bool
    {
        return (bool) (($empresa->configuracion ?? [])['onboarding_completado'] ?? false);
    }

    public function pasoActual(Empresa $empresa): int
    {
        return max(1, min(4, (int) (($empresa->configuracion ?? [])['onboarding_paso'] ?? 1)));
    }

    public function guardarPasoEmpresa(Empresa $empresa, array $datos): void
    {
        $config = $empresa->configuracion ?? [];
        $config['serie_boleta'] = strtoupper($datos['serie_boleta']);
        $config['serie_factura'] = strtoupper($datos['serie_factura']);
        $config['onboarding_paso'] = 2;

        $empresa->update([
            'nombre' => $datos['nombre'],
            'ruc' => $datos['ruc'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'tipo_negocio' => $datos['tipo_negocio'],
            'configuracion' => $config,
        ]);

        $this->sembrarCategorias($empresa->fresh());
    }

    public function guardarPasoTerminal(Empresa $empresa, array $datos): Terminal
    {
        $config = $empresa->configuracion ?? [];
        $terminalId = (int) ($config['onboarding_terminal_id'] ?? 0);

        $terminal = $terminalId > 0
            ? Terminal::where('empresa_id', $empresa->id)->where('id', $terminalId)->first()
            : null;

        if (! $terminal) {
            $terminal = Terminal::where('empresa_id', $empresa->id)
                ->where('codigo', $datos['codigo'])
                ->first();
        }

        $payload = [
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'local' => $datos['local'] ?? null,
            'caja' => $datos['caja'],
            'activo' => true,
        ];

        if ($terminal) {
            $terminal->update($payload);
        } else {
            $terminal = Terminal::create([
                ...$payload,
                'empresa_id' => $empresa->id,
            ]);
        }

        $config['onboarding_paso'] = 3;
        $config['onboarding_terminal_id'] = $terminal->id;
        $empresa->update(['configuracion' => $config]);

        return $terminal;
    }

    public function avanzarPasoProductos(Empresa $empresa): void
    {
        $config = $empresa->configuracion ?? [];
        $config['onboarding_paso'] = 4;
        $empresa->update(['configuracion' => $config]);
    }

    public function guardarPasoCajero(Empresa $empresa, array $datos): Usuario
    {
        $terminalId = (int) (($empresa->configuracion ?? [])['onboarding_terminal_id'] ?? 0);
        if ($terminalId <= 0) {
            $terminalId = Terminal::where('empresa_id', $empresa->id)->value('id');
        }

        if (! $terminalId) {
            throw new RuntimeException('Primero configure una terminal en el paso 2.');
        }

        return DB::transaction(function () use ($empresa, $datos, $terminalId) {
            $cajero = Usuario::create([
                'empresa_id' => $empresa->id,
                'nombre' => $datos['nombre'],
                'usuario' => $datos['usuario'],
                'email' => $datos['email'] ?? null,
                'password' => Hash::make($datos['password']),
                'rol' => 'cajero',
                'activo' => true,
            ]);

            $cajero->terminales()->sync([
                $terminalId => ['es_defecto' => true],
            ]);

            $this->marcarCompletado($empresa);

            return $cajero;
        });
    }

    public function omitirCajero(Empresa $empresa): void
    {
        $this->marcarCompletado($empresa);
    }

    public function marcarCompletado(Empresa $empresa): void
    {
        $config = $empresa->configuracion ?? [];
        $config['onboarding_completado'] = true;
        $config['onboarding_paso'] = 4;
        unset($config['onboarding_terminal_id']);
        $empresa->update(['configuracion' => $config]);
    }

    public function reiniciar(Empresa $empresa): void
    {
        $config = $empresa->configuracion ?? [];
        $config['onboarding_completado'] = false;
        $config['onboarding_paso'] = 1;
        unset($config['onboarding_terminal_id']);
        $empresa->update(['configuracion' => $config]);
    }

    private function sembrarCategorias(Empresa $empresa): void
    {
        if (Categoria::where('empresa_id', $empresa->id)->exists()) {
            return;
        }

        $plantilla = config('business_templates.'.$empresa->tipo_negocio, config('business_templates.otro'));
        $nombres = $plantilla['categories'] ?? ['General'];

        foreach ($nombres as $orden => $nombre) {
            Categoria::create([
                'empresa_id' => $empresa->id,
                'nombre' => mb_strtoupper($nombre),
                'orden' => $orden,
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function contexto(Empresa $empresa): array
    {
        $config = $empresa->configuracion ?? [];
        $terminal = null;
        $terminalId = (int) ($config['onboarding_terminal_id'] ?? 0);

        if ($terminalId > 0) {
            $terminal = Terminal::find($terminalId);
        }

        if (! $terminal) {
            $terminal = Terminal::where('empresa_id', $empresa->id)->orderBy('codigo')->first();
        }

        return [
            'paso' => $this->pasoActual($empresa),
            'terminal' => $terminal,
            'tiene_productos' => $empresa->productos()->exists(),
            'tiene_categorias' => Categoria::where('empresa_id', $empresa->id)->exists(),
            'plantilla_negocio' => config('business_templates.'.$empresa->tipo_negocio, config('business_templates.otro')),
        ];
    }
}
