@php
    $tab = $tab ?? 'resumen';
@endphp
<nav style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid #e2e8f0">
    <a href="{{ route('admin.contratacion.show', $empresa) }}"
       class="btn {{ $tab === 'resumen' ? 'btn-primary' : '' }}"
       style="{{ $tab === 'resumen' ? '' : 'background:#e2e8f0;color:#334155' }};padding:.4rem .75rem;font-size:.85rem">Resumen</a>
    <a href="{{ route('admin.contratacion.modulos', $empresa) }}"
       class="btn {{ $tab === 'modulos' ? 'btn-primary' : '' }}"
       style="{{ $tab === 'modulos' ? '' : 'background:#e2e8f0;color:#334155' }};padding:.4rem .75rem;font-size:.85rem">Módulos</a>
    <a href="{{ route('admin.contratacion.extras', $empresa) }}"
       class="btn {{ $tab === 'extras' ? 'btn-primary' : '' }}"
       style="{{ $tab === 'extras' ? '' : 'background:#e2e8f0;color:#334155' }};padding:.4rem .75rem;font-size:.85rem">Extras</a>
    <a href="{{ route('admin.contratacion.cupos', $empresa) }}"
       class="btn {{ $tab === 'cupos' ? 'btn-primary' : '' }}"
       style="{{ $tab === 'cupos' ? '' : 'background:#e2e8f0;color:#334155' }};padding:.4rem .75rem;font-size:.85rem">Cupos usuarios</a>
    <span style="flex:1"></span>
    <a href="{{ route('admin.contratacion.index') }}" style="font-size:.85rem;color:#64748b;align-self:center">← Contratación</a>
    <a href="{{ route('admin.empresas.editar', $empresa) }}" style="font-size:.85rem;color:#64748b;align-self:center;margin-left:.75rem">Empresa</a>
</nav>
