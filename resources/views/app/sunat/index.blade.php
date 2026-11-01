@extends('layouts.app')

@section('titulo', 'Facturación SUNAT')

@section('contenido')
<h1 style="margin-bottom:.5rem">Facturación electrónica SUNAT</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
    Emisión de boletas y facturas electrónicas. Use <strong>simulación</strong> para probar; para facturación real active
    <strong>producción</strong> con <strong>Nubefact</strong> (recomendado) u otro OSE/PSE.
</p>

<div class="card" style="max-width:820px">
    <form method="post" action="{{ route('sunat.actualizar') }}" id="form-sunat">
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
                    <option value="simulacion" @selected(old('modo', $config['modo'] ?? 'simulacion') === 'simulacion')>Simulación (pruebas)</option>
                    <option value="produccion" @selected(old('modo', $config['modo']) === 'produccion')>Producción (SUNAT real)</option>
                </select>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Proveedor OSE/PSE *</label>
                <select name="proveedor_ose" id="proveedor_ose" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    @foreach(config('sunat.proveedores_ose', []) as $clave => $etiqueta)
                    <option value="{{ $clave }}" @selected(old('proveedor_ose', $config['proveedor_ose'] ?? 'generico') === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="panel-nubefact" style="display:none;margin-bottom:1.25rem;padding:1rem;background:#ecfdf5;border:1px solid #6ee7b7;border-radius:.65rem">
            <h3 style="font-size:.95rem;margin:0 0 .75rem;color:#047857">Nubefact</h3>
            <p style="font-size:.85rem;color:#475569;margin-bottom:.75rem">
                Obtenga ruta y token en <a href="https://www.nubefact.com" target="_blank" rel="noopener">nubefact.com</a>
                → API → credenciales. Nubefact firma y envía a SUNAT por usted.
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Ruta API</label>
                    <input name="nubefact_ruta" value="{{ old('nubefact_ruta', $config['nubefact_ruta'] ?? '') }}"
                           placeholder="uuid-de-su-ruta"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    @error('nubefact_ruta')<small style="color:#991b1b">{{ $message }}</small>@enderror
                </div>
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Token API</label>
                    <input type="password" name="nubefact_token"
                           placeholder="{{ ($config['nubefact_token'] ?? '') !== '' ? '••••••••' : '' }}"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                </div>
            </div>
        </div>

        <div id="panel-generico" style="margin-bottom:1.25rem;padding:1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.65rem">
            <h3 style="font-size:.95rem;margin:0 0 .75rem">OSE/PSE genérico</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario SOL / OSE</label>
                    <input name="usuario_sol" value="{{ old('usuario_sol', $config['usuario_sol'] ?? '') }}"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                </div>
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Clave SOL / OSE</label>
                    <input type="password" name="clave_sol" placeholder="{{ ($config['clave_sol'] ?? '') !== '' ? '••••••••' : '' }}"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                </div>
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Certificado (.pem / .pfx)</label>
                    <input name="certificado" value="{{ old('certificado', $config['certificado'] ?? '') }}"
                           placeholder="ruta en storage/app/sunat/{id}/"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                </div>
                <div>
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">Clave del certificado</label>
                    <input type="password" name="clave_certificado" placeholder="Si el .pfx tiene contraseña"
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                </div>
                <div style="grid-column:1/-1">
                    <label style="font-weight:600;display:block;margin-bottom:.35rem">URL OSE/PSE</label>
                    <input name="ose_url" type="url" value="{{ old('ose_url', $config['ose_url'] ?? '') }}"
                           placeholder="https://..."
                           style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    @error('ose_url')<small style="color:#991b1b">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
            Las claves se guardan cifradas. En simulación se genera XML UBL, QR en ticket y estado <em>simulado</em>.
            En producción con Nubefact el hash del QR proviene de la respuesta SUNAT real.
            Reintentos automáticos cada 30 min para comprobantes en error (<code>ventapos:reintentar-sunat</code>).
        </p>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('configuracion.index') }}" style="margin-left:.5rem">← Configuración</a>
    </form>
</div>

@if($comprobantesPendientes->isNotEmpty())
<div class="card" style="max-width:820px;margin-top:1rem">
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

@push('scripts')
<script>
(function () {
    const sel = document.getElementById('proveedor_ose');
    const panelNubefact = document.getElementById('panel-nubefact');
    const panelGenerico = document.getElementById('panel-generico');
    function toggle() {
        const esNubefact = sel.value === 'nubefact';
        panelNubefact.style.display = esNubefact ? 'block' : 'none';
        panelGenerico.style.display = esNubefact ? 'none' : 'block';
    }
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
@endpush
