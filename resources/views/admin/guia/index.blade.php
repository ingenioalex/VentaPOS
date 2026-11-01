@extends('layouts.admin')

@section('titulo', 'Guía de respuesta')

@push('head')
<style>
    .guia-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .guia-header h1 { font-size:1.45rem; margin:0; }
    .guia-sub { color:#64748b; font-size:.9rem; margin-top:.25rem; max-width:52rem; }
    .guia-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
    .guia-info {
        background:#eff6ff;
        border:1px solid #bfdbfe;
        border-left:4px solid #3b82f6;
        border-radius:.65rem;
        padding:1rem 1.15rem;
        margin-bottom:1rem;
        font-size:.88rem;
        color:#1e3a5f;
        line-height:1.55;
    }
    .guia-info strong { display:block; margin-bottom:.35rem; color:#1e40af; }
    .guia-info ul { margin:.5rem 0 0 1.1rem; }
    .guia-info li { margin-bottom:.2rem; }
    .guia-vars-wrap { margin-bottom:1rem; }
    .guia-vars-wrap summary {
        cursor:pointer;
        font-weight:600;
        font-size:.88rem;
        color:#5b21b6;
        padding:.5rem 0;
    }
    .guia-vars {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));
        gap:.65rem;
        margin-top:.5rem;
        padding:1rem;
        background:#f8fafc;
        border:1px solid #e2e8f0;
        border-radius:.65rem;
    }
    .guia-vars label { font-size:.72rem; font-weight:700; text-transform:uppercase; color:#64748b; display:block; margin-bottom:.25rem; }
    .guia-vars input, .guia-vars select { width:100%; padding:.45rem .55rem; border:1px solid #cbd5e1; border-radius:.4rem; font-size:.88rem; }
    .grupo-titulo {
        font-size:.78rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.06em;
        color:#5b21b6;
        margin:1.25rem 0 .65rem;
        padding-bottom:.35rem;
        border-bottom:2px solid #e9d5ff;
    }
    .grupo-titulo:first-of-type { margin-top:0; }
    .plantilla-card {
        border:1px solid #e2e8f0;
        border-radius:.65rem;
        margin-bottom:.75rem;
        overflow:hidden;
        background:#fff;
    }
    .plantilla-head {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:.5rem;
        padding:.75rem 1rem;
        background:#faf5ff;
        border-bottom:1px solid #e9d5ff;
        flex-wrap:wrap;
    }
    .plantilla-head strong { color:#5b21b6; font-size:.92rem; }
    .plantilla-cuando { font-size:.78rem; color:#64748b; }
    .plantilla-body { padding:1rem; }
    .plantilla-texto {
        white-space:pre-wrap;
        font-size:.88rem;
        line-height:1.5;
        color:#334155;
        background:#f8fafc;
        border:1px solid #e2e8f0;
        border-radius:.5rem;
        padding:.85rem;
        margin-bottom:.65rem;
        font-family:system-ui, sans-serif;
    }
    .btn-copy { background:#7c3aed; color:#fff; font-size:.82rem; padding:.4rem .75rem; }
    .btn-copy.copied { background:#059669; }
    .btn-print { background:#0f766e; color:#fff; }
    .planes-mini { font-size:.85rem; color:#475569; margin-bottom:1rem; }
    .planes-mini strong { color:#0f766e; }
    .contador-plantillas { font-size:.82rem; color:#64748b; margin-bottom:.5rem; }
</style>
@endpush

@section('contenido')
<div class="guia-header">
    <div>
        <h1>{{ $tituloGuia }}</h1>
        <p class="guia-sub">{{ $descripcionModulo }}</p>
    </div>
    <div class="guia-actions">
        <a href="{{ route('admin.guia.imprimir') }}" target="_blank" class="btn btn-print">PDF para cliente</a>
    </div>
</div>

<div class="guia-info">
    <strong>¿Para qué sirve?</strong>
    Es tu <em>chuleta de mensajes</em> al atender prospectos por WhatsApp. No guarda datos en el sistema: tú copias el texto y lo pegas en el chat.
    <ul>
        <li>Entra desde <a href="{{ route('admin.prospectos.index') }}">Prospectos → Ver</a> con el nombre ya cargado.</li>
        <li>Elige la plantilla según la situación (primer contacto, sin RUC, plan Pro, etc.).</li>
        <li><strong>Copiar WhatsApp</strong> → pegar en WhatsApp → cambiar estado del prospecto en su ficha.</li>
        <li>Alta de empresa y contratación se hace en <a href="{{ route('admin.empresas.index') }}">Empresas</a> / <a href="{{ route('admin.contratacion.index') }}">Contratación</a>.</li>
    </ul>
</div>

<div class="card planes-mini">
    <strong>Precios actuales:</strong>
    Arranque S/ {{ config('planes.arranque.monto') }} ·
    Básico S/ {{ config('planes.basico.mensual') }}/mes ·
    Negocio S/ {{ config('planes.negocio.mensual') }}/mes ·
    Pro S/ {{ config('planes.pro.mensual') }}/mes (SUNAT) ·
    Beta: 1 mes gratis
</div>

<details class="card guia-vars-wrap">
    <summary>Personalizar textos (opcional)</summary>
    <form class="guia-vars" method="get" action="{{ route('admin.guia.index') }}">
        <div>
            <label>Nombre prospecto</label>
            <input type="text" name="nombre" value="{{ $vars['nombre'] !== '[NOMBRE]' ? $vars['nombre'] : '' }}" placeholder="Ej. Juan / Bodega El Sol">
        </div>
        <div>
            <label>Plan a ofrecer</label>
            <select name="plan" id="sel-plan">
                @foreach(['Beta' => 0, 'Básico' => config('planes.basico.mensual'), 'Negocio' => config('planes.negocio.mensual'), 'Pro' => config('planes.pro.mensual')] as $p => $m)
                <option value="{{ $p }}" data-mensual="{{ $m }}" @selected($vars['plan'] === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Mensual (mes 2)</label>
            <input type="number" name="mensual" id="inp-mensual" value="{{ $vars['mensual'] }}" min="0" step="1">
        </div>
        <div>
            <label>Usuario alta</label>
            <input type="text" name="usuario" value="{{ $vars['usuario'] !== '[usuario]' ? $vars['usuario'] : '' }}" placeholder="correo o usuario">
        </div>
        <div style="display:flex;align-items:end">
            <button type="submit" class="btn btn-primary" style="width:100%">Actualizar textos</button>
        </div>
    </form>
</details>

<p class="contador-plantillas">{{ $totalPlantillas }} plantillas en {{ count($grupos) }} secciones</p>

@foreach($grupos as $grupoId => $grupoNombre)
    @if(($plantillasPorGrupo[$grupoId] ?? collect())->isNotEmpty())
    <h2 class="grupo-titulo">{{ $grupoNombre }}</h2>
    @foreach($plantillasPorGrupo[$grupoId] as $plantilla)
    <div class="plantilla-card" id="tpl-{{ $plantilla['id'] }}">
        <div class="plantilla-head">
            <div>
                <strong>{{ $plantilla['titulo'] }}</strong>
                <div class="plantilla-cuando">{{ $plantilla['cuando'] }}</div>
            </div>
            <button type="button" class="btn btn-copy" data-copy-target="texto-{{ $plantilla['id'] }}">Copiar WhatsApp</button>
        </div>
        <div class="plantilla-body">
            <div class="plantilla-texto" id="texto-{{ $plantilla['id'] }}">{{ $plantilla['texto_resuelto'] }}</div>
        </div>
    </div>
    @endforeach
    @endif
@endforeach
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-copy-target]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const el = document.getElementById(btn.dataset.copyTarget);
        if (!el) return;
        const text = el.textContent;
        try {
            await navigator.clipboard.writeText(text);
            btn.textContent = '¡Copiado!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.textContent = 'Copiar WhatsApp';
                btn.classList.remove('copied');
            }, 2000);
        } catch (e) {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            btn.textContent = '¡Copiado!';
        }
    });
});
const selPlan = document.getElementById('sel-plan');
const inpMensual = document.getElementById('inp-mensual');
if (selPlan && inpMensual) {
    selPlan.addEventListener('change', () => {
        const m = selPlan.selectedOptions[0]?.dataset.mensual;
        if (m && m !== '0') inpMensual.value = m;
        if (selPlan.value === 'Beta') inpMensual.value = 0;
    });
}
</script>
@endpush
