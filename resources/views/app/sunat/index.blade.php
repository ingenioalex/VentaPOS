@extends('layouts.app')

@section('titulo', 'Facturación SUNAT')

@section('contenido')
<h1 style="margin-bottom:.5rem">Facturación electrónica SUNAT</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
    Emisión de boletas y facturas electrónicas. Use <strong>modo simulación</strong> para probar sin conectar a SUNAT.
</p>

<div class="card" style="max-width:720px">
    <form method="post" action="{{ route('sunat.actualizar') }}">
        @csrf
        @method('PUT')

        <div style="margin-bottom:1rem;padding:.75rem;background:#f0f9ff;border-radius:.5rem;font-size:.9rem">
            <strong>Empresa:</strong> {{ $empresa->nombre }}
            · <strong>RUC:</strong> {{ $empresa->ruc ?: '— (configure en Configuración)' }}
        </div>

        <div style="margin-bottom:1.25rem">
            <label style="font-weight:600">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $config['activo'] ?? false))>
                Activar facturación electrónica al cobrar en POS
            </label>
            @error('activo')<br><small style="color:#991b1b">{{ $message }}</small>@enderror
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.25rem">
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Modo *</label>
                <select name="modo" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    <option value="simulacion" @selected(old('modo', $config['modo'] ?? 'simulacion') === 'simulacion')>Simulación (recomendado para pruebas)</option>
                    <option value="produccion" @selected(old('modo', $config['modo']) === 'produccion')>Producción (requiere OSE/PSE)</option>
                </select>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario SOL</label>
                <input name="usuario_sol" value="{{ old('usuario_sol', $config['usuario_sol'] ?? '') }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Clave SOL</label>
                <input type="password" name="clave_sol" placeholder="{{ ($config['clave_sol'] ?? '') !== '' ? '••••••••' : '' }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Certificado (.pem)</label>
                <input name="certificado" value="{{ old('certificado', $config['certificado'] ?? '') }}" placeholder="ruta o nombre archivo"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div style="grid-column:1/-1">
                <label style="font-weight:600;display:block;margin-bottom:.35rem">URL OSE/PSE (opcional)</label>
                <input name="ose_url" type="url" value="{{ old('ose_url', $config['ose_url'] ?? '') }}"
                       placeholder="https://..."
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
        </div>

        <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
            En simulación se genera el XML UBL, el código QR en el ticket y se marca el comprobante como aceptado sin envío real.
            En producción, configure la URL de su OSE/PSE. El sistema envía JSON con
            <code>usuario</code>, <code>clave</code>, <code>nombre_archivo</code> y <code>archivo_base64</code>,
            y espera respuesta con <code>aceptado</code>, <code>codigo</code>, <code>mensaje</code> y opcionalmente <code>cdr_base64</code>.
        </p>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('configuracion.index') }}" style="margin-left:.5rem">← Configuración</a>
    </form>
</div>

@if($comprobantesPendientes->isNotEmpty())
<div class="card" style="max-width:720px;margin-top:1rem">
    <h2 style="font-size:1rem;margin-bottom:.75rem">Comprobantes que requieren atención</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Comprobante</th>
                <th>Fecha</th>
                <th>Estado SUNAT</th>
                <th>Mensaje</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($comprobantesPendientes as $c)
            <tr>
                <td><strong>{{ $c->numero_interno }}</strong></td>
                <td>{{ $c->emitido_en->format('d/m/Y H:i') }}</td>
                <td>{{ $c->etiquetaSunat() }}</td>
                <td style="font-size:.85rem;color:#64748b">{{ $c->sunat_mensaje ? \Illuminate\Support\Str::limit($c->sunat_mensaje, 80) : '—' }}</td>
                <td><a href="{{ route('ventas.detalle', $c) }}">Ver</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
