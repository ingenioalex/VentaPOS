<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupConfigService;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBackupController extends Controller
{
    public function __construct(
        private DatabaseBackupService $backups,
        private BackupConfigService $configBackup,
    ) {}

    public function index()
    {
        $cfg = $this->configBackup->configuracion();

        return view('admin.backups.index', [
            'copias' => $this->backups->listar(),
            'espacioUsado' => $this->backups->espacioUsado(),
            'espacioLegible' => $this->backups->formatearTamano($this->backups->espacioUsado()),
            'ultimaEjecucion' => $this->backups->ultimaEjecucion(),
            'herramientaOk' => $this->backups->mysqldumpDisponible(),
            'infoConexion' => $this->backups->infoConexion(),
            'retencionDias' => $cfg['retencion_dias'],
            'horaAutomatica' => $cfg['hora_automatica'],
            'automaticoActivo' => $cfg['automatico_activo'],
            'directorio' => 'storage/app/'.config('backup.directorio', 'backups'),
        ]);
    }

    public function crear()
    {
        if (! $this->backups->mysqldumpDisponible()) {
            return redirect()->route('admin.backups.index')
                ->withErrors(['backup' => 'No se puede crear la copia: herramienta de backup no disponible.']);
        }

        $resultado = $this->backups->crear(programado: false);

        if (! $resultado['ok']) {
            return redirect()->route('admin.backups.index')
                ->withErrors(['backup' => $resultado['mensaje']]);
        }

        $mensaje = 'Copia creada: '.$resultado['archivo'];
        if (($resultado['eliminados'] ?? 0) > 0) {
            $mensaje .= ' · Se eliminaron '.$resultado['eliminados'].' copia(s) antigua(s).';
        }
        if (! empty($resultado['metodo']) && $resultado['metodo'] === 'pdo') {
            $mensaje .= ' (vía conexión PHP; en producción Linux suele usarse mysqldump).';
        }

        return redirect()->route('admin.backups.index')->with('ok', $mensaje);
    }

    public function descargar(string $archivo): StreamedResponse
    {
        try {
            $ruta = $this->backups->rutaDescarga($archivo);
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }

        return response()->streamDownload(function () use ($ruta) {
            $stream = fopen($ruta, 'rb');
            if ($stream === false) {
                return;
            }
            fpassthru($stream);
            fclose($stream);
        }, $archivo, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    public function eliminar(string $archivo)
    {
        try {
            $this->backups->eliminar($archivo);
        } catch (RuntimeException $e) {
            return redirect()->route('admin.backups.index')
                ->withErrors(['backup' => $e->getMessage()]);
        }

        return redirect()->route('admin.backups.index')
            ->with('ok', 'Copia eliminada.');
    }

    public function actualizarConfig(Request $request)
    {
        $datos = $request->validate([
            'retencion_dias' => 'required|integer|min:1|max:365',
            'hora_automatica' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'automatico_activo' => 'nullable|boolean',
        ]);

        $this->configBackup->guardar([
            'retencion_dias' => (int) $datos['retencion_dias'],
            'hora_automatica' => $datos['hora_automatica'],
            'automatico_activo' => $request->boolean('automatico_activo'),
        ]);

        return redirect()->route('admin.backups.index')
            ->with('ok', 'Configuración de backups guardada. El programador usará la hora '.$this->configBackup->configuracion()['hora_automatica'].'.');
    }
}
