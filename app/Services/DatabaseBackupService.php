<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupService
{
    private const ULTIMA_EJECUCION = 'backup-last-run.json';

    public function __construct(private BackupConfigService $configBackup) {}

    public function directorio(): string
    {
        return storage_path('app/'.config('backup.directorio', 'backups'));
    }

    public function asegurarDirectorio(): void
    {
        File::ensureDirectoryExists($this->directorio());
    }

    public function mysqldumpDisponible(): bool
    {
        $driver = config('database.default');
        if (in_array($driver, ['sqlite'], true)) {
            return File::exists($this->rutaSqlite());
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return false;
        }

        return $this->resolverMysqldump() !== null || $this->pdoMysqlDisponible();
    }

    /** @return array{ok: bool, archivo?: string, mensaje: string, eliminados?: int} */
    public function crear(bool $programado = false): array
    {
        $this->asegurarDirectorio();

        try {
            $driver = config('database.default');
            $resultadoMysql = match ($driver) {
                'mysql', 'mariadb' => $this->crearMysql($programado),
                'sqlite' => ['archivo' => $this->crearSqlite($programado), 'metodo' => 'sqlite'],
                default => throw new RuntimeException("Conexión «{$driver}» no soportada para backup."),
            };

            $archivo = $resultadoMysql['archivo'];
            $metodo = $resultadoMysql['metodo'] ?? 'mysqldump';

            $eliminados = $this->purgarAntiguos();

            $this->registrarEjecucion([
                'ok' => true,
                'archivo' => $archivo,
                'programado' => $programado,
                'mensaje' => 'Copia creada correctamente.',
                'eliminados' => $eliminados,
            ]);

            return [
                'ok' => true,
                'archivo' => $archivo,
                'mensaje' => 'Copia creada correctamente.',
                'eliminados' => $eliminados,
                'metodo' => $metodo,
            ];
        } catch (\Throwable $e) {
            $this->registrarEjecucion([
                'ok' => false,
                'programado' => $programado,
                'mensaje' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return list<array{archivo: string, tamano: int, tamano_legible: string, fecha: string, origen: string}>
     */
    public function listar(): array
    {
        $this->asegurarDirectorio();

        $archivos = collect(File::files($this->directorio()))
            ->filter(fn ($f) => $this->nombreValido($f->getFilename()))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        $meta = $this->metaPorArchivo();

        return $archivos->map(function ($f) use ($meta) {
            $nombre = $f->getFilename();
            $info = $meta[$nombre] ?? [];

            return [
                'archivo' => $nombre,
                'tamano' => $f->getSize(),
                'tamano_legible' => $this->formatearTamano($f->getSize()),
                'fecha' => date('Y-m-d H:i:s', $f->getMTime()),
                'origen' => $info['origen'] ?? 'desconocido',
                'metodo' => $info['metodo'] ?? 'mysqldump',
            ];
        })->all();
    }

    public function rutaDescarga(string $archivo): string
    {
        if (! $this->nombreValido($archivo)) {
            throw new RuntimeException('Nombre de archivo no válido.');
        }

        $ruta = $this->directorio().DIRECTORY_SEPARATOR.$archivo;
        if (! File::exists($ruta)) {
            throw new RuntimeException('La copia no existe.');
        }

        $realBase = realpath($this->directorio());
        $realFile = realpath($ruta);
        if ($realBase === false || $realFile === false || ! str_starts_with($realFile, $realBase)) {
            throw new RuntimeException('Ruta de archivo no permitida.');
        }

        return $realFile;
    }

    public function eliminar(string $archivo): void
    {
        $ruta = $this->rutaDescarga($archivo);
        File::delete($ruta);
        $this->quitarMeta($archivo);
    }

    public function purgarAntiguos(): int
    {
        $dias = (int) config('backup.retencion_dias', 30);
        $limite = now()->subDays($dias)->getTimestamp();
        $eliminados = 0;

        foreach (File::files($this->directorio()) as $f) {
            if (! $this->nombreValido($f->getFilename())) {
                continue;
            }
            if ($f->getMTime() >= $limite) {
                continue;
            }
            File::delete($f->getPathname());
            $this->quitarMeta($f->getFilename());
            $eliminados++;
        }

        return $eliminados;
    }

    public function espacioUsado(): int
    {
        $total = 0;
        foreach (File::files($this->directorio()) as $f) {
            if ($this->nombreValido($f->getFilename())) {
                $total += $f->getSize();
            }
        }

        return $total;
    }

    public function formatearTamano(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / (1024 * 1024), 2).' MB';
    }

    /** @return array<string, mixed>|null */
    public function ultimaEjecucion(): ?array
    {
        $ruta = storage_path('app/'.self::ULTIMA_EJECUCION);
        if (! File::exists($ruta)) {
            return null;
        }

        $datos = json_decode(File::get($ruta), true);

        return is_array($datos) ? $datos : null;
    }

    public function infoConexion(): string
    {
        $driver = config('database.default');
        $db = config("database.connections.{$driver}.database");

        return strtoupper($driver).' · '.$db;
    }

    /** @return array{archivo: string, metodo: string} */
    private function crearMysql(bool $programado): array
    {
        $conexion = config('database.connections.'.config('database.default'));
        $database = (string) ($conexion['database'] ?? '');
        $archivo = $this->nombreArchivo($database, 'sql.gz');
        $sql = null;
        $metodo = 'mysqldump';

        $mysqldump = $this->resolverMysqldump();
        if ($mysqldump !== null) {
            try {
                $sql = $this->ejecutarMysqldump($mysqldump, $conexion);
            } catch (Throwable $e) {
                if (! $this->pdoMysqlDisponible()) {
                    throw new RuntimeException($e->getMessage());
                }
                $metodo = 'pdo';
            }
        }

        if ($sql === null) {
            if (! $this->pdoMysqlDisponible()) {
                throw new RuntimeException(
                    'No se encontró mysqldump ni conexión MySQL. Configure BACKUP_MYSQLDUMP_PATH en .env.'
                );
            }
            $sql = $this->volcarMysqlPdo($database);
            $metodo = 'pdo';
        }

        if ($sql === '') {
            throw new RuntimeException('El volcado de la base de datos quedó vacío.');
        }

        $comprimido = gzencode($sql, 9);
        if ($comprimido === false) {
            throw new RuntimeException('No se pudo comprimir el volcado.');
        }

        File::put($this->directorio().DIRECTORY_SEPARATOR.$archivo, $comprimido);
        $this->guardarMeta($archivo, $programado, $metodo);

        return ['archivo' => $archivo, 'metodo' => $metodo];
    }

    /** @param array<string, mixed> $conexion */
    private function ejecutarMysqldump(string $mysqldump, array $conexion): string
    {
        $host = $this->normalizarHostMysql((string) ($conexion['host'] ?? '127.0.0.1'));
        $port = (string) ($conexion['port'] ?? '3306');
        $database = (string) ($conexion['database'] ?? '');
        $username = (string) ($conexion['username'] ?? '');
        $password = (string) ($conexion['password'] ?? '');
        $socket = trim((string) ($conexion['unix_socket'] ?? ''));

        $defaults = $this->crearArchivoCredencialesMysql($host, $port, $username, $password, $socket);
        $tmpSql = $this->directorio().DIRECTORY_SEPARATOR.'_tmp_'.uniqid('dump_', true).'.sql';

        try {
            $opciones = [
                '--defaults-extra-file='.$defaults,
                '--single-transaction',
                '--quick',
                '--lock-tables=false',
                '--routines',
                '--triggers',
                '--column-statistics=0',
            ];

            if (PHP_OS_FAMILY === 'Windows') {
                $comando = '"'.str_replace('"', '""', $mysqldump).'" '
                    .implode(' ', $opciones)
                    .' --result-file="'.str_replace('"', '""', $tmpSql).'" '
                    .'"'.str_replace('"', '""', $database).'"';

                $process = Process::fromShellCommandline($comando, base_path());
                $process->setTimeout(600);
                $process->run();

                if (! $process->isSuccessful()) {
                    throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput() ?: 'mysqldump falló.'));
                }

                if (! File::exists($tmpSql) || File::size($tmpSql) === 0) {
                    throw new RuntimeException('mysqldump no generó el archivo de salida.');
                }

                return File::get($tmpSql);
            }

            $process = new Process(array_merge([$mysqldump], $opciones, [$database]));
            $process->setTimeout(600);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput() ?: 'mysqldump falló.'));
            }

            $sql = $process->getOutput();
            if ($sql === '') {
                throw new RuntimeException('mysqldump no devolvió datos.');
            }

            return $sql;
        } finally {
            @unlink($defaults);
            if (File::exists($tmpSql)) {
                File::delete($tmpSql);
            }
        }
    }

    private function volcarMysqlPdo(string $database): string
    {
        $pdo = DB::connection()->getPdo();
        $pdo->exec('SET NAMES utf8mb4');

        $tablas = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')
            ->fetchAll(PDO::FETCH_NUM);

        $sql = "-- VentaPOS backup (PDO)\n"
            .'-- Base: '.$database."\n"
            .'-- Fecha: '.now()->toDateTimeString()."\n\n"
            ."SET FOREIGN_KEY_CHECKS=0;\n"
            ."SET NAMES utf8mb4;\n\n";

        foreach ($tablas as [$tabla]) {
            $create = $pdo->query('SHOW CREATE TABLE `'.str_replace('`', '``', $tabla).'`')
                ->fetch(PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
            $sql .= ($create[1] ?? '').";\n\n";

            $filas = $pdo->query('SELECT * FROM `'.str_replace('`', '``', $tabla).'`');
            while ($fila = $filas->fetch(PDO::FETCH_ASSOC)) {
                $columnas = array_map(fn ($c) => '`'.$c.'`', array_keys($fila));
                $valores = array_map(function ($v) use ($pdo) {
                    if ($v === null) {
                        return 'NULL';
                    }

                    return $pdo->quote((string) $v);
                }, array_values($fila));

                $sql .= 'INSERT INTO `'.$tabla.'` ('.implode(', ', $columnas).') VALUES ('.implode(', ', $valores).");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function crearArchivoCredencialesMysql(
        string $host,
        string $port,
        string $username,
        string $password,
        string $socket,
    ): string {
        $lineas = [
            '[client]',
            'user='.$username,
            'password='.$password,
        ];

        if ($socket !== '') {
            $lineas[] = 'socket='.$socket;
        } else {
            $lineas[] = 'host='.$host;
            $lineas[] = 'port='.$port;
            if (PHP_OS_FAMILY === 'Windows') {
                $lineas[] = 'protocol=TCP';
            }
        }

        $ruta = storage_path('app/backups/_cnf_'.uniqid('', true).'.cnf');
        File::put($ruta, implode("\n", $lineas)."\n");
        @chmod($ruta, 0600);

        return $ruta;
    }

    private function normalizarHostMysql(string $host): string
    {
        if (in_array(strtolower($host), ['localhost', '::1'], true) && PHP_OS_FAMILY === 'Windows') {
            return '127.0.0.1';
        }

        return $host;
    }

    private function pdoMysqlDisponible(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function crearSqlite(bool $programado): string
    {
        $rutaDb = $this->rutaSqlite();
        if (! File::exists($rutaDb)) {
            throw new RuntimeException('No se encontró el archivo SQLite.');
        }

        $nombreDb = basename($rutaDb, '.sqlite');
        $archivo = $this->nombreArchivo($nombreDb, 'sqlite.gz');
        $contenido = File::get($rutaDb);
        $comprimido = gzencode($contenido, 9);
        if ($comprimido === false) {
            throw new RuntimeException('No se pudo comprimir la base SQLite.');
        }

        File::put($this->directorio().DIRECTORY_SEPARATOR.$archivo, $comprimido);
        $this->guardarMeta($archivo, $programado);

        return $archivo;
    }

    private function rutaSqlite(): string
    {
        $database = config('database.connections.sqlite.database');

        return $database && $database !== ':memory:'
            ? $database
            : database_path('database.sqlite');
    }

    private function resolverMysqldump(): ?string
    {
        $configurado = config('backup.mysqldump_bin', 'mysqldump');
        if ($configurado !== 'mysqldump' && File::exists($configurado)) {
            return $configurado;
        }

        $process = new Process([$configurado, '--version']);
        $process->run();

        if ($process->isSuccessful()) {
            return $configurado;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidatos = glob('C:\\laragon\\bin\\mysql\\mysql-*\\bin\\mysqldump.exe') ?: [];
            rsort($candidatos);
            foreach ($candidatos as $ruta) {
                if (File::exists($ruta)) {
                    return $ruta;
                }
            }
        }

        return null;
    }

    private function nombreArchivo(string $database, string $extension): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $database) ?: 'db';

        return 'ventapos_'.$slug.'_'.now()->format('Y-m-d_His').'.'.$extension;
    }

    private function nombreValido(string $archivo): bool
    {
        return (bool) preg_match('/^ventapos_[a-zA-Z0-9_-]+_\d{4}-\d{2}-\d{2}_\d{6}\.(sql|sqlite)\.gz$/', $archivo);
    }

    /** @param array<string, mixed> $datos */
    private function registrarEjecucion(array $datos): void
    {
        File::put(storage_path('app/'.self::ULTIMA_EJECUCION), json_encode(array_merge($datos, [
            'fecha' => now()->toIso8601String(),
        ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function metaRuta(): string
    {
        return $this->directorio().DIRECTORY_SEPARATOR.'meta.json';
    }

    /** @return array<string, array{origen: string}> */
    private function metaPorArchivo(): array
    {
        $ruta = $this->metaRuta();
        if (! File::exists($ruta)) {
            return [];
        }

        $datos = json_decode(File::get($ruta), true);

        return is_array($datos) ? $datos : [];
    }

    private function guardarMeta(string $archivo, bool $programado, string $metodo = 'mysqldump'): void
    {
        $meta = $this->metaPorArchivo();
        $meta[$archivo] = [
            'origen' => $programado ? 'automatico' : 'manual',
            'metodo' => $metodo,
        ];
        File::put($this->metaRuta(), json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function quitarMeta(string $archivo): void
    {
        $meta = $this->metaPorArchivo();
        unset($meta[$archivo]);
        File::put($this->metaRuta(), json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
