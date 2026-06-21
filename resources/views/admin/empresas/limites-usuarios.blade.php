@extends('layouts.admin')

@section('titulo', 'Cupos usuarios — '.$empresa->nombre)

@section('contenido')
<div style="margin-bottom:.5rem">
    <h1 style="margin-bottom:.15rem">Cupos de usuarios</h1>
    <p style="color:#64748b;font-size:.9rem">{{ $empresa->nombre }} · Plan {{ $desglose['plan_nombre'] }}</p>
</div>

@include('admin.contratacion._nav', ['tab' => 'cupos'])

<div class="stats" style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
    <div class="card" style="margin:0;min-width:140px">
        <span style="color:#64748b;font-size:.8rem">Plan (base)</span>
        <strong style="display:block;font-size:1.5rem">{{ $desglose['plan_base'] }}</strong>
    </div>
    <div class="card" style="margin:0;min-width:140px">
        <span style="color:#64748b;font-size:.8rem">Bonificados</span>
        <strong style="display:block;font-size:1.5rem;color:#0f766e">+{{ $desglose['bonificados'] }}</strong>
        <small style="color:#64748b">Sin costo</small>
    </div>
    <div class="card" style="margin:0;min-width:140px">
        <span style="color:#64748b;font-size:.8rem">Add-on pago</span>
        <strong style="display:block;font-size:1.5rem;color:#7c3aed">+{{ $desglose['extras_pago'] }}</strong>
        <small style="color:#64748b">S/ {{ number_format($desglose['precio_extra_mensual'], 0) }}/u/mes</small>
    </div>
    <div class="card" style="margin:0;min-width:140px">
        <span style="color:#64748b;font-size:.8rem">Cupo total</span>
        <strong style="display:block;font-size:1.5rem">{{ $desglose['total'] }}</strong>
    </div>
    <div class="card" style="margin:0;min-width:140px">
        <span style="color:#64748b;font-size:.8rem">Activos ahora</span>
        <strong style="display:block;font-size:1.5rem;{{ $desglose['activos'] >= $desglose['total'] ? 'color:#dc2626' : '' }}">{{ $desglose['activos'] }}</strong>
        <small style="color:#64748b">{{ $desglose['restantes'] }} disponibles</small>
    </div>
    @if($desglose['extras_pago'] > 0)
    <div class="card" style="margin:0;min-width:160px">
        <span style="color:#64748b;font-size:.8rem">Cobro add-on usuarios</span>
        <strong style="display:block;font-size:1.2rem">S/ {{ number_format($desglose['costo_extras_mensual'], 2) }}/mes</strong>
    </div>
    @endif
</div>

<div class="card" style="max-width:640px">
    <h3 style="margin-bottom:.75rem">Ajustar cupos por empresa</h3>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
        El cupo efectivo es: <strong>plan + bonificados + usuarios extra de pago</strong>.
        Use bonificados para cortesías (ej. +2 sin cobrar en plan Básico).
        Use add-on de pago cuando el cliente contrate usuarios adicionales (S/ {{ number_format($desglose['precio_extra_mensual'], 0) }}/mes c/u).
    </p>

    <form method="post" action="{{ route('admin.contratacion.cupos.actualizar', $empresa) }}">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
                <label style="font-weight:600;font-size:.85rem">Cupos bonificados (sin costo)</label>
                <input type="number" name="bonificados" min="0" max="50" required
                       value="{{ old('bonificados', $cupos['bonificados']) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
                <small style="color:#64748b">Se suman al plan sin facturar</small>
            </div>
            <div>
                <label style="font-weight:600;font-size:.85rem">Usuarios extra (add-on de pago)</label>
                <input type="number" name="extras_pago" min="0" max="50" required
                       value="{{ old('extras_pago', $cupos['extras_pago']) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">
                <small style="color:#64748b">{{ $usuarioExtra['nombre'] ?? 'Usuario adicional' }}</small>
            </div>
        </div>

        <div style="margin-bottom:1rem;padding:.75rem;background:#f8fafc;border-radius:.5rem;font-size:.88rem">
            <strong>Nuevo cupo total:</strong>
            <span id="preview-total">{{ $desglose['total'] }}</span> usuarios
            <span id="preview-costo" style="color:#64748b;margin-left:.5rem">
                @if($desglose['extras_pago'] > 0)
                · Add-on: S/ {{ number_format($desglose['costo_extras_mensual'], 2) }}/mes
                @endif
            </span>
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-weight:600;font-size:.85rem">Nota interna (solo admin)</label>
            <textarea name="nota_interna" rows="2" maxlength="500" placeholder="Ej: +2 bonificados por migración RMS"
                      style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem;margin-top:.35rem">{{ old('nota_interna', $cupos['nota_interna']) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Guardar cupos</button>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const base = {{ $desglose['plan_base'] }};
    const precioExtra = {{ $desglose['precio_extra_mensual'] }};
    const bon = document.querySelector('[name=bonificados]');
    const extra = document.querySelector('[name=extras_pago]');
    const totalEl = document.getElementById('preview-total');
    const costoEl = document.getElementById('preview-costo');
    function actualizar() {
        const b = +bon.value || 0;
        const e = +extra.value || 0;
        const t = base + b + e;
        totalEl.textContent = t;
        costoEl.textContent = e > 0 ? '· Add-on: S/ ' + (e * precioExtra).toFixed(2) + '/mes' : '';
    }
    bon.addEventListener('input', actualizar);
    extra.addEventListener('input', actualizar);
})();
</script>
@endpush
@endsection
