@php
    $u = $usuarioModel;
    $prefix = 'u'.$u->id;
    $usaEmp = old('usa_empresa', $usaEmpresa ?? true);
    $cfgLocal = old() ? array_merge($usuarioCfg ?? [], old()) : ($usuarioCfg ?? []);
@endphp
<form method="post" action="{{ route('impresora.usuario.gestionar', $u) }}" class="imp-form-usuario">
    @csrf
    @method('PUT')

    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;font-weight:600">
        <input type="checkbox" name="usa_empresa" value="1" class="chk-usa-empresa" data-bloque="bloque-u{{ $u->id }}"
            @checked($usaEmp)>
        Usar la configuración por defecto de la empresa
    </label>

    <div id="bloque-u{{ $u->id }}" class="bloque-cfg-usuario" style="{{ $usaEmp ? 'opacity:.55' : '' }}">
        @include('app.impresora._campos', [
            'prefix' => $prefix,
            'cfg' => $cfgLocal,
            'disabled' => $usaEmp,
            'anchos' => $anchos ?? null,
            'tamanos' => $tamanos ?? null,
            'metodos' => $metodos ?? null,
        ])
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('impresora.prueba.usuario', ['usuario' => $u, 'imprimir' => 1]) }}"
           target="_blank" rel="noopener" class="btn" style="background:#e2e8f0;color:#334155">
            Ticket de prueba
        </a>
    </div>
</form>
