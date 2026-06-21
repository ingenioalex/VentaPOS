<?php

namespace App\Services;

use App\Models\Terminal;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use RuntimeException;

class TerminalSesionService
{
    /**
     * @return Collection<int, Terminal>
     */
    public function terminalesDisponibles(Usuario $usuario): Collection
    {
        $asignadas = $usuario->terminales()
            ->where('terminales.activo', true)
            ->orderBy('terminales.codigo')
            ->get();

        if ($asignadas->isNotEmpty()) {
            return $asignadas;
        }

        if (in_array($usuario->rol, ['dueno', 'supervisor', 'super_admin'], true)) {
            return Terminal::where('activo', true)->orderBy('codigo')->get();
        }

        return collect();
    }

    public function inicializarSesion(Usuario $usuario): void
    {
        $disponibles = $this->terminalesDisponibles($usuario);

        if ($disponibles->isEmpty()) {
            $this->limpiarSesion();

            return;
        }

        $defecto = $usuario->terminales()
            ->where('terminales.activo', true)
            ->wherePivot('es_defecto', true)
            ->first();

        if (! $defecto || ! $disponibles->contains('id', $defecto->id)) {
            $defecto = $disponibles->first();
        }

        $this->guardarEnSesion($defecto);
    }

    public function terminalActivo(): ?Terminal
    {
        $id = session('terminal_id');
        if (! $id) {
            return null;
        }

        return Terminal::where('id', $id)->where('activo', true)->first();
    }

    /**
     * @return array{id: ?int, codigo: string, nombre: string, local: string, caja: string}
     */
    public function datosActivos(): array
    {
        $terminal = $this->terminalActivo();

        if ($terminal) {
            return [
                'id' => $terminal->id,
                'codigo' => $terminal->codigo,
                'nombre' => $terminal->nombre,
                'local' => $terminal->local ?? '',
                'caja' => $terminal->caja,
            ];
        }

        $usuario = auth()->user();
        $config = $usuario?->empresa?->configuracion ?? [];

        return [
            'id' => null,
            'codigo' => $config['terminal'] ?? '01',
            'nombre' => 'Terminal '.($config['terminal'] ?? '01'),
            'local' => $config['local'] ?? '',
            'caja' => $config['caja'] ?? '01',
        ];
    }

    public function puedeUsarTerminal(Usuario $usuario, int $terminalId): bool
    {
        return $this->terminalesDisponibles($usuario)->contains('id', $terminalId);
    }

    public function cambiarTerminal(Usuario $usuario, int $terminalId): Terminal
    {
        if (! $this->puedeUsarTerminal($usuario, $terminalId)) {
            throw new RuntimeException('No tiene permiso para usar esta terminal.');
        }

        $terminal = Terminal::where('id', $terminalId)->where('activo', true)->firstOrFail();
        $this->guardarEnSesion($terminal);

        return $terminal;
    }

    public function requiereTerminal(Usuario $usuario): bool
    {
        return $usuario->rol === 'cajero';
    }

    public function tieneTerminalActiva(): bool
    {
        return $this->terminalActivo() !== null || session()->has('terminal_id');
    }

    public function guardarEnSesion(Terminal $terminal): void
    {
        session([
            'terminal_id' => $terminal->id,
            'terminal_codigo' => $terminal->codigo,
            'terminal_nombre' => $terminal->nombre,
            'terminal_local' => $terminal->local ?? '',
            'terminal_caja' => $terminal->caja,
        ]);
    }

    public function limpiarSesion(): void
    {
        session()->forget([
            'terminal_id',
            'terminal_codigo',
            'terminal_nombre',
            'terminal_local',
            'terminal_caja',
        ]);
    }
}
