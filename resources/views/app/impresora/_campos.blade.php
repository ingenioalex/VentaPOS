@php
    $p = $prefix ?? 'cfg';
    $cfg = $cfg ?? [];
    $disabled = $disabled ?? false;
    $metodoActual = old('metodo', $cfg['metodo'] ?? 'navegador');
@endphp

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
    <div>
        <label style="font-weight:600;display:block;margin-bottom:.35rem">Ancho de papel *</label>
        <select name="ancho_mm" @disabled($disabled) style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @foreach($anchos ?? config('impresora.anchos', []) as $val => $label)
            <option value="{{ $val }}" @selected((int) old('ancho_mm', $cfg['ancho_mm'] ?? 80) === (int) $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-weight:600;display:block;margin-bottom:.35rem">Tamaño de fuente *</label>
        <select name="tamano_fuente" @disabled($disabled) style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @foreach($tamanos ?? config('impresora.tamanos_fuente', []) as $val => $label)
            <option value="{{ $val }}" @selected(old('tamano_fuente', $cfg['tamano_fuente'] ?? 'normal') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-weight:600;display:block;margin-bottom:.35rem">Método / conexión *</label>
        <select name="metodo" id="metodo-{{ $p }}" @disabled($disabled) style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
            @foreach($metodos ?? config('impresora.metodos', []) as $val => $label)
            <option value="{{ $val }}" @selected($metodoActual === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-weight:600;display:block;margin-bottom:.35rem">Copias *</label>
        <input type="number" name="copias" min="1" max="3" value="{{ old('copias', $cfg['copias'] ?? 1) }}" @disabled($disabled)
               style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
    </div>
    <div style="grid-column:1/-1">
        <div id="ayuda-metodo-{{ $p }}" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;padding:.65rem .75rem;font-size:.82rem;color:#475569;line-height:1.45"></div>
    </div>
    <div style="grid-column:1/-1">
        <label id="lbl-nombre-imp-{{ $p }}" style="font-weight:600;display:block;margin-bottom:.35rem">Nombre impresora en Windows (opcional)</label>
        <input name="nombre_impresora_sistema" value="{{ old('nombre_impresora_sistema', $cfg['nombre_impresora_sistema'] ?? '') }}" @disabled($disabled)
               placeholder="Ej: EPSON TM-T20 Receipt"
               style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
        <small id="hint-nombre-imp-{{ $p }}" style="color:#64748b;display:block;margin-top:.25rem"></small>
    </div>
    <div style="grid-column:1/-1">
        <label style="font-weight:600;display:block;margin-bottom:.35rem">Notas al pie del ticket</label>
        <input name="notas_pie" maxlength="200" value="{{ old('notas_pie', $cfg['notas_pie'] ?? '') }}" @disabled($disabled)
               placeholder="Ej: ¡Gracias por su compra! No se aceptan devoluciones sin boleta."
               style="width:100%;padding:.55rem;border:1px solid #cbd5e1;border-radius:.5rem">
    </div>
    <div>
        <label style="display:flex;align-items:center;gap:.5rem;font-weight:600">
            <input type="checkbox" name="auto_imprimir" value="1" @checked(old('auto_imprimir', $cfg['auto_imprimir'] ?? true)) @disabled($disabled)>
            Auto-imprimir al cobrar
        </label>
    </div>
    <div>
        <label style="display:flex;align-items:center;gap:.5rem;font-weight:600">
            <input type="checkbox" name="mostrar_logo" value="1" @checked(old('mostrar_logo', $cfg['mostrar_logo'] ?? true)) @disabled($disabled)>
            Mostrar logo en ticket
        </label>
    </div>
</div>

@once('impresora-metodo-ayuda')
@push('scripts')
<script>
(function () {
    const textos = {
        navegador: {
            ayuda: 'Usa el diálogo de impresión del navegador (Chrome/Edge). Funciona con USB, red o Bluetooth si el sistema ya reconoce la impresora.',
            label: 'Nombre impresora en el sistema (opcional)',
            hint: 'Referencia para el cajero; en el diálogo de impresión elige la impresora correcta.',
        },
        termica_usb: {
            ayuda: 'Conecte la térmica por USB, instale el driver en Windows y establézcala como impresora predeterminada. VentaPOS imprime vía navegador.',
            label: 'Nombre impresora USB en Windows (opcional)',
            hint: 'Ej: EPSON TM-T20 Receipt. Debe coincidir con la impresora predeterminada de Windows.',
        },
        bluetooth: {
            ayuda: 'Empareje la impresora Bluetooth en Windows, tablet o celular (Ajustes → Bluetooth). Luego imprima desde el navegador seleccionando esa impresora. Ideal para POS móvil con modelos como Epson TM-m30II o Xprinter XP-P810.',
            label: 'Nombre impresora Bluetooth emparejada (opcional)',
            hint: 'Anote el nombre exacto que aparece al emparejar (Ej: MTP-II, TM-m30). En el diálogo de impresión elíjala como destino.',
        },
    };

    function sync(prefix) {
        const sel = document.getElementById('metodo-' + prefix);
        const ayuda = document.getElementById('ayuda-metodo-' + prefix);
        const lbl = document.getElementById('lbl-nombre-imp-' + prefix);
        const hint = document.getElementById('hint-nombre-imp-' + prefix);
        if (!sel || !ayuda) return;
        const t = textos[sel.value] || textos.navegador;
        ayuda.textContent = t.ayuda;
        if (lbl) lbl.textContent = t.label;
        if (hint) hint.textContent = t.hint;
    }

    document.querySelectorAll('[id^="metodo-"]').forEach(sel => {
        const prefix = sel.id.replace('metodo-', '');
        sync(prefix);
        sel.addEventListener('change', () => sync(prefix));
    });
})();
</script>
@endpush
@endonce
