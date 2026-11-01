@extends('layouts.app')

@section('titulo', 'Impresora')

@section('contenido')
<h1 style="margin-bottom:.35rem">Configuración de impresora</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1.25rem">
    @if($puedeGestionar)
    Defina la impresora por defecto de la empresa y, si lo necesita, una configuración distinta para cada usuario.
    @else
    Configure su impresora o use la configuración por defecto de la empresa.
    @endif
    La impresión usa el navegador (USB, red o Bluetooth emparejado).
</p>

@if(session('ok'))
<div class="alert-ok">{{ session('ok') }}</div>
@endif

<div style="display:grid;gap:1rem;max-width:920px">

    @if($esDueno)
    <div class="card">
        <h2 style="font-size:1.05rem;margin-bottom:.75rem">Por defecto de la empresa</h2>
        <p style="color:#64748b;font-size:.85rem;margin-bottom:1rem">
            Aplica a los usuarios que marquen «Usar la configuración por defecto de la empresa».
        </p>

        <form method="post" action="{{ route('impresora.empresa') }}">
            @csrf
            @method('PUT')
            @include('app.impresora._campos', [
                'prefix' => 'empresa',
                'cfg' => $empresaCfg,
                'disabled' => false,
                'anchos' => $anchos,
                'tamanos' => $tamanos,
                'metodos' => $metodos,
            ])
            <button type="submit" class="btn btn-primary" style="margin-top:1rem">Guardar defaults empresa</button>
        </form>
    </div>
    @endif

    @if($puedeGestionar && $usuariosEmpresa->isNotEmpty())
    <div class="card">
        <h2 style="font-size:1.05rem;margin-bottom:.35rem">Impresora por usuario</h2>
        <p style="color:#64748b;font-size:.85rem;margin-bottom:1rem">
            Cada cajero o supervisor puede usar el default de la empresa o tener su propia impresora (útil con varias cajas).
        </p>

        <div style="display:grid;gap:.85rem">
            @foreach($usuariosEmpresa as $item)
            @php
                $u = $item['usuario'];
                $ef = $item['efectiva'];
                $metodoLabel = $metodos[$ef['metodo']] ?? $ef['metodo'];
            @endphp
            <details class="imp-usuario-panel" {{ $u->id === auth()->id() ? 'open' : '' }}
                style="border:1px solid #e2e8f0;border-radius:.65rem;padding:.75rem 1rem;background:#fafafa">
                <summary style="cursor:pointer;font-weight:700;list-style:none;display:flex;flex-wrap:wrap;gap:.35rem 1rem;align-items:center">
                    <span>{{ $u->nombre }}</span>
                    <span style="font-weight:500;color:#64748b;font-size:.82rem">{{ $u->usuario }} · {{ $u->etiquetaRol() }}</span>
                    <span style="margin-left:auto;font-size:.78rem;font-weight:600;color:#0f766e">
                        {{ $item['usaEmpresa'] ? 'Default empresa' : 'Personalizada' }}
                        · {{ $ef['ancho_mm'] }} mm · {{ $tamanos[$ef['tamano_fuente']] ?? $ef['tamano_fuente'] }}
                    </span>
                </summary>
                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0">
                    <p style="color:#64748b;font-size:.82rem;margin-bottom:1rem">
                        Config efectiva: <strong>{{ $ef['ancho_mm'] }} mm</strong>,
                        {{ $metodoLabel }},
                        {{ $ef['auto_imprimir'] ? 'auto-imprime' : 'sin auto-imprimir' }}.
                    </p>
                    @include('app.impresora._form-usuario', [
                        'usuarioModel' => $u,
                        'usuarioCfg' => $item['cfg'],
                        'usaEmpresa' => $item['usaEmpresa'],
                        'anchos' => $anchos,
                        'tamanos' => $tamanos,
                        'metodos' => $metodos,
                    ])
                </div>
            </details>
            @endforeach
        </div>
    </div>
    @elseif(! $puedeGestionar)
    <div class="card">
        <h2 style="font-size:1.05rem;margin-bottom:.75rem">Mi impresora</h2>
        <p style="color:#64748b;font-size:.85rem;margin-bottom:1rem">
            Configuración de <strong>{{ auth()->user()->nombre }}</strong>.
            Efectiva ahora: <strong>{{ $efectiva['ancho_mm'] }} mm</strong>,
            fuente {{ $tamanos[$efectiva['tamano_fuente']] ?? $efectiva['tamano_fuente'] }},
            {{ $efectiva['auto_imprimir'] ? 'auto-imprime al cobrar' : 'no auto-imprime' }}.
        </p>

        <form method="post" action="{{ route('impresora.usuario') }}">
            @csrf
            @method('PUT')

            <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;font-weight:600">
                <input type="checkbox" name="usa_empresa" value="1" id="chk-mi-usa-empresa" @checked(old('usa_empresa', $usaEmpresa))>
                Usar la configuración por defecto de la empresa
            </label>

            <div id="bloque-mi-usuario" style="{{ old('usa_empresa', $usaEmpresa) ? 'opacity:.55' : '' }}">
                @include('app.impresora._campos', [
                    'prefix' => 'mi',
                    'cfg' => old() ? array_merge($usuarioCfg, old()) : $usuarioCfg,
                    'disabled' => old('usa_empresa', $usaEmpresa),
                    'anchos' => $anchos,
                    'tamanos' => $tamanos,
                    'metodos' => $metodos,
                ])
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem">
                <button type="submit" class="btn btn-primary">Guardar mi configuración</button>
                <a href="{{ route('impresora.prueba', ['imprimir' => 1]) }}" target="_blank" rel="noopener"
                   class="btn" style="background:#e2e8f0;color:#334155">Imprimir ticket de prueba</a>
            </div>
        </form>
    </div>
    @endif

    <div class="card" style="background:#f8fafc">
        <h2 style="font-size:1.05rem;margin-bottom:.5rem">Recomendación de compra</h2>
        <p style="font-size:.88rem;line-height:1.55;color:#475569">
            Para pruebas: <strong>Xprinter XP-80</strong> o <strong>XP-58</strong> (USB, ~S/150–280).
            Para vender a clientes: <strong>Epson TM-T20III</strong> (80 mm, USB/Ethernet, ~S/900–1,200).
            Bluetooth: <strong>Epson TM-m30II</strong> o <strong>Xprinter XP-P810</strong> para POS móvil.
        </p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function syncBloque(chk) {
        const id = chk.dataset.bloque;
        const bloque = document.getElementById(id);
        if (!bloque) return;
        const off = chk.checked;
        bloque.style.opacity = off ? '.55' : '1';
        bloque.querySelectorAll('input,select,textarea').forEach(el => {
            if (el.name === 'usa_empresa') return;
            el.disabled = off;
        });
    }

    document.querySelectorAll('.chk-usa-empresa').forEach(chk => {
        syncBloque(chk);
        chk.addEventListener('change', () => syncBloque(chk));
    });

    const miChk = document.getElementById('chk-mi-usa-empresa');
    const miBloque = document.getElementById('bloque-mi-usuario');
    if (miChk && miBloque) {
        function syncMi() {
            const off = miChk.checked;
            miBloque.style.opacity = off ? '.55' : '1';
            miBloque.querySelectorAll('input,select,textarea').forEach(el => {
                if (el.name === 'usa_empresa') return;
                el.disabled = off;
            });
        }
        miChk.addEventListener('change', syncMi);
        syncMi();
    }
})();
</script>
@endpush
@endsection
