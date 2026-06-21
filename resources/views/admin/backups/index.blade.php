@extends('layouts.admin')

@section('titulo', 'Copias de seguridad')

@section('contenido')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
    <div>
        <h1 style="margin-bottom:.25rem">Copias de seguridad</h1>
        <p style="color:#64748b;font-size:.9rem;max-width:680px">
            Volcado comprimido de la base de datos. Las copias automáticas se ejecutan en la madrugada
            si el cron del servidor está activo.
        </p>
    </div>
    @if($herramientaOk)
    <form method="post" action="{{ route('admin.backups.crear') }}"
          onsubmit="return confirm('¿Crear copia de seguridad ahora?')">
        @csrf
        <button type="submit" class="btn btn-primary">+ Crear copia ahora</button>
    </form>
    @endif
</div>

@if(!$herramientaOk)
<div class="alert-error">
    No se puede generar backup con la conexión actual ({{ $infoConexion }}).
    @if(str_contains(strtolower($infoConexion), 'mysql'))
    Configure <code>BACKUP_MYSQLDUMP_PATH</code> en <code>.env</code> apuntando a <code>mysqldump.exe</code> (Laragon).
    @endif
</div>
@endif

<div class="stats" style="margin-bottom:1rem">
    <div class="card" style="margin:0;min-width:150px">
        <span style="color:#64748b;font-size:.8rem">Conexión</span>
        <strong style="display:block;font-size:.95rem">{{ $infoConexion }}</strong>
    </div>
    <div class="card" style="margin:0;min-width:120px">
        <span style="color:#64748b;font-size:.8rem">Copias guardadas</span>
        <strong style="display:block;font-size:1.4rem">{{ count($copias) }}</strong>
    </div>
    <div class="card" style="margin:0;min-width:120px">
        <span style="color:#64748b;font-size:.8rem">Espacio usado</span>
        <strong style="display:block;font-size:1.2rem">{{ $espacioLegible }}</strong>
        <small style="color:#64748b">{{ $directorio }}</small>
    </div>
    <div class="card" style="margin:0;min-width:180px">
        <span style="color:#64748b;font-size:.8rem">Última ejecución</span>
        @if($ultimaEjecucion)
        <strong style="display:block;font-size:.9rem;color:{{ ($ultimaEjecucion['ok'] ?? false) ? '#065f46' : '#991b1b' }}">
            {{ ($ultimaEjecucion['ok'] ?? false) ? 'OK' : 'Error' }}
        </strong>
        <small style="color:#64748b">{{ \Carbon\Carbon::parse($ultimaEjecucion['fecha'])->format('d/m/Y H:i') }}</small>
        @else
        <strong style="display:block;font-size:.9rem">—</strong>
        @endif
    </div>
</div>

<div class="card" style="max-width:520px;margin-bottom:1.25rem">
    <h3 style="margin-bottom:.75rem">Programación automática</h3>
    <form method="post" action="{{ route('admin.backups.config') }}">
        @csrf
        @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
                <label style="font-weight:600;font-size:.85rem">Hora diaria</label>
                <input type="time" name="hora_automatica" required
                       value="{{ old('hora_automatica', $horaAutomatica) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
                <small style="color:#64748b">Recomendado: 03:00 (madrugada)</small>
            </div>
            <div>
                <label style="font-weight:600;font-size:.85rem">Retención (días)</label>
                <input type="number" name="retencion_dias" min="1" max="365" required
                       value="{{ old('retencion_dias', $retencionDias) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
                <small style="color:#64748b">Copias más antiguas se eliminan solas</small>
            </div>
        </div>
        <label style="display:flex;align-items:center;gap:.45rem;font-size:.9rem;margin-bottom:1rem">
            <input type="checkbox" name="automatico_activo" value="1" @checked(old('automatico_activo', $automaticoActivo))>
            Activar backup automático diario
        </label>
        <button type="submit" class="btn btn-primary">Guardar configuración</button>
    </form>
    <p style="margin-top:.85rem;font-size:.82rem;color:#64748b;line-height:1.45">
        En el VPS debe existir el cron de Laravel:<br>
        <code style="font-size:.78rem">* * * * * cd /var/www/ventapos && php artisan schedule:run</code>
    </p>
</div>

<div class="card" style="overflow:auto">
    <h3 style="margin-bottom:.75rem">Historial de copias</h3>
    <table class="data">
        <thead>
            <tr>
                <th>Archivo</th>
                <th>Fecha</th>
                <th>Origen</th>
                <th>Método</th>
                <th>Tamaño</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($copias as $c)
            <tr>
                <td><code style="font-size:.78rem">{{ $c['archivo'] }}</code></td>
                <td>{{ $c['fecha'] }}</td>
                <td>{{ $c['origen'] === 'automatico' ? 'Automático' : 'Manual' }}</td>
                <td>{{ ($c['metodo'] ?? 'mysqldump') === 'pdo' ? 'PHP (PDO)' : 'mysqldump' }}</td>
                <td>{{ $c['tamano_legible'] }}</td>
                <td style="white-space:nowrap">
                    <a href="{{ route('admin.backups.descargar', $c['archivo']) }}">Descargar</a>
                    ·
                    <form method="post" action="{{ route('admin.backups.eliminar', $c['archivo']) }}" style="display:inline"
                          onsubmit="return confirm('¿Eliminar esta copia?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:0;font:inherit">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="color:#64748b">Sin copias aún. Cree una manualmente o espere el backup automático.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <p style="margin-top:.85rem;font-size:.82rem;color:#64748b;line-height:1.5">
        <strong>Producción (VPS Linux):</strong> el mismo archivo <code>.sql.gz</code> se restaura con
        <code>gunzip -c archivo.sql.gz | mysql -u usuario -p base_datos</code>.
        En el servidor suele usarse <em>mysqldump</em> (más rápido e incluye rutinas/triggers).
        Instale <code>mysql-client</code> si no está en PATH.
    </p>
</div>
@endsection
