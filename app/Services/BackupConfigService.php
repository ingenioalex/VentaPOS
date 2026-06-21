<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class BackupConfigService
{
    private const ARCHIVO = 'backup-settings.json';

    public function rutaArchivo(): string
    {
        return storage_path('app/'.self::ARCHIVO);
    }

    /** @return array{retencion_dias: int, hora_automatica: string, automatico_activo: bool} */
    public function configuracion(): array
    {
        $defaults = [
            'retencion_dias' => (int) config('backup.retencion_dias', 30),
            'hora_automatica' => (string) config('backup.hora_automatica', '03:00'),
            'automatico_activo' => (bool) config('backup.automatico_activo', true),
        ];

        $ruta = $this->rutaArchivo();
        if (! File::exists($ruta)) {
            return $defaults;
        }

        $datos = json_decode(File::get($ruta), true);
        if (! is_array($datos)) {
            return $defaults;
        }

        return [
            'retencion_dias' => max(1, min(365, (int) ($datos['retencion_dias'] ?? $defaults['retencion_dias']))),
            'hora_automatica' => $this->normalizarHora((string) ($datos['hora_automatica'] ?? $defaults['hora_automatica'])),
            'automatico_activo' => (bool) ($datos['automatico_activo'] ?? $defaults['automatico_activo']),
        ];
    }

    public function aplicarEnConfig(): void
    {
        $cfg = $this->configuracion();

        config([
            'backup.retencion_dias' => $cfg['retencion_dias'],
            'backup.hora_automatica' => $cfg['hora_automatica'],
            'backup.automatico_activo' => $cfg['automatico_activo'],
        ]);
    }

    /** @param array{retencion_dias: int, hora_automatica: string, automatico_activo: bool} $datos */
    public function guardar(array $datos): void
    {
        $payload = [
            'retencion_dias' => max(1, min(365, (int) $datos['retencion_dias'])),
            'hora_automatica' => $this->normalizarHora($datos['hora_automatica']),
            'automatico_activo' => (bool) $datos['automatico_activo'],
        ];

        File::ensureDirectoryExists(storage_path('app'));
        File::put($this->rutaArchivo(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->aplicarEnConfig();
    }

    public function tienePersonalizacion(): bool
    {
        return File::exists($this->rutaArchivo());
    }

    private function normalizarHora(string $hora): string
    {
        if (preg_match('/^(\d{1,2}):(\d{2})$/', trim($hora), $m)) {
            $h = max(0, min(23, (int) $m[1]));
            $min = max(0, min(59, (int) $m[2]));

            return sprintf('%02d:%02d', $h, $min);
        }

        return '03:00';
    }
}
