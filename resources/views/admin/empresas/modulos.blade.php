@extends('layouts.admin')

@section('titulo', 'Módulos — '.$empresa->nombre)

@section('contenido')
<div style="margin-bottom:.5rem">
    <h1 style="margin-bottom:.15rem">Módulos contratados</h1>
    <p style="color:#64748b;font-size:.9rem">{{ $empresa->nombre }} · {{ count($contratados) }} activos · ref. S/ {{ number_format($totalMensual, 0) }}/mes</p>
</div>

@include('admin.contratacion._nav', ['tab' => 'modulos'])

<p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
    Marque los módulos a contratar. Para aplicar un paquete completo según plan (Beta, Básico, Negocio, Pro), use
    <a href="{{ route('admin.contratacion.show', $empresa) }}">Resumen → Plantillas</a>.
</p>

<form method="post" action="{{ route('admin.contratacion.modulos.actualizar', $empresa) }}" id="form-modulos">
    @csrf
    @method('PUT')

    @foreach($porGrupo as $grupo => $items)
    <div class="card">
        <h3 style="margin-bottom:1rem">{{ $grupo }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:.75rem">
            @foreach($items as $item)
            <label class="modulo-check-label" style="display:flex;align-items:flex-start;gap:.6rem;padding:.65rem;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer;background:{{ in_array($item['clave'], $contratados, true) ? '#f5f3ff' : '#fff' }}">
                <input type="checkbox" name="modulos[]" value="{{ $item['clave'] }}" class="modulo-checkbox"
                       @checked(in_array($item['clave'], old('modulos', $contratados), true))
                       style="margin-top:.2rem">
                <span>
                    <strong style="display:block">{{ $item['nombre'] }}</strong>
                    <small style="color:#64748b">S/ {{ number_format($item['precio_mensual'], 0) }} / mes</small>
                    @if(!empty($item['requiere']))
                    <small style="display:block;color:#94a3b8">Requiere {{ config('permisos.modulos_contratables.'.$item['requiere'].'.nombre', $item['requiere']) }}</small>
                    @endif
                </span>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach

    <div style="margin-top:1rem">
        <button type="submit" class="btn btn-primary">Guardar módulos</button>
        <a href="{{ route('admin.contratacion.show', $empresa) }}" style="margin-left:.5rem">Volver al resumen</a>
    </div>
</form>

@push('scripts')
<script>
document.querySelectorAll('.modulo-checkbox').forEach(function (cb) {
    cb.addEventListener('change', function () {
        const label = cb.closest('.modulo-check-label');
        if (label) label.style.background = cb.checked ? '#f5f3ff' : '#fff';
    });
});
</script>
@endpush
@endsection
