@extends('layouts.admin')

@section('titulo', $empresa->exists ? 'Editar empresa' : 'Nueva empresa')

@section('contenido')
<h1 style="margin-bottom:1rem">{{ $empresa->exists ? 'Editar empresa' : 'Nueva empresa' }}</h1>

@php
    $onboardingOk = $empresa->exists
        ? (bool) (($empresa->configuracion ?? [])['onboarding_completado'] ?? false)
        : false;
@endphp

<div class="card" style="max-width:720px">
    <form method="post" action="{{ $empresa->exists ? route('admin.empresas.actualizar', $empresa) : route('admin.empresas.guardar') }}">
        @csrf
        @if($empresa->exists) @method('PUT') @endif

        <h3 style="margin-bottom:1rem">Datos de la empresa</h3>
        <div class="grid-2" style="margin-bottom:1rem">
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre *</label>
                <input name="nombre" required value="{{ old('nombre', $empresa->nombre) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Código de acceso *</label>
                <input name="codigo" required value="{{ old('codigo', $empresa->codigo) }}"
                       pattern="[A-Za-z0-9_-]+" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <small style="color:#64748b">Usado en el login de la empresa (ej: bodega-el-sol)</small>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Tipo negocio *</label>
                <select name="tipo_negocio" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    @foreach(['bodega','ferreteria','farmacia','otro'] as $t)
                    <option value="{{ $t }}" @selected(old('tipo_negocio', $empresa->tipo_negocio) === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            @if(!$empresa->exists)
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Plan inicial *</label>
                <select name="plan" id="sel-plan" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    @foreach(config('planes.planes_disponibles', ['beta','basico','negocio','pro']) as $p)
                    @php $pi = config('planes.'.$p, []); @endphp
                    <option value="{{ $p }}" @selected(old('plan', 'basico') === $p)>
                        @if($p === 'beta')
                        {{ $pi['nombre'] ?? 'Beta' }} — {{ $pi['nota'] ?? 'Gratis' }}
                        @else
                        {{ $pi['nombre'] ?? ucfirst($p) }} — S/ {{ $pi['mensual'] ?? 0 }}/mes
                        @endif
                    </option>
                    @endforeach
                </select>
                <small id="plan-cobro-hint" style="color:#64748b;display:block;margin-top:.35rem"></small>
            </div>
            @endif
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">RUC</label>
                <input name="ruc" maxlength="11" value="{{ old('ruc', $empresa->ruc) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Prueba hasta</label>
                <input type="date" name="prueba_hasta" value="{{ old('prueba_hasta', $empresa->prueba_hasta?->format('Y-m-d')) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Teléfono</label>
                <input name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Dirección</label>
                <input name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
        </div>

        @if($empresa->exists)
        <div style="margin-bottom:1.5rem">
            <label>
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $empresa->activo))>
                Empresa activa
            </label>
        </div>
        @else
        <hr style="border:none;border-top:1px solid #e2e8f0;margin:1.5rem 0">
        <h3 style="margin-bottom:1rem">Administrador inicial (dueño)</h3>
        <div class="grid-2" style="margin-bottom:1rem">
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre *</label>
                <input name="dueno_nombre" required value="{{ old('dueno_nombre') }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario *</label>
                <input name="dueno_usuario" required value="{{ old('dueno_usuario') }}"
                       pattern="[A-Za-z0-9_-]+" autocomplete="off"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                <small style="color:#64748b">Solo letras, números, guión (-) y guión bajo (_). Sin espacios.</small>
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Email</label>
                <input type="email" name="dueno_email" value="{{ old('dueno_email') }}"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Contraseña *</label>
                <input type="password" name="dueno_password" required minlength="6"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
            <div>
                <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar contraseña *</label>
                <input type="password" name="dueno_password_confirmation" required minlength="6"
                       style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
            </div>
        </div>
        <div style="margin-bottom:1rem">
            <label>
                <input type="checkbox" name="activo" value="1" @checked(old('activo', true))>
                Empresa activa
            </label>
        </div>
        @endif

        @if($empresa->exists)
        <p style="margin-bottom:1rem;font-size:.88rem;color:#64748b">
            Plan: <strong>{{ config('planes.'.$empresa->plan.'.nombre', ucfirst($empresa->plan)) }}</strong>
            · Wizard: {{ $onboardingOk ? 'completado' : 'paso '.(($empresa->configuracion ?? [])['onboarding_paso'] ?? 1) }}
            — <a href="{{ route('admin.contratacion.show', $empresa) }}">Contratación</a>
        </p>
        @endif

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('admin.empresas.index') }}" style="margin-left:.5rem">Cancelar</a>
        @if($empresa->exists)
        <a href="{{ route('admin.empresas.usuarios.index', $empresa) }}" style="margin-left:.5rem">Usuarios</a>
        @endif
    </form>

    @if(!$empresa->exists)
    @push('scripts')
    <script>
    (function () {
        const arranque = @json(config('planes.arranque'));
        const planes = @json(collect(config('planes.planes_disponibles', ['beta','basico','negocio','pro']))->mapWithKeys(fn ($k) => [$k => config('planes.'.$k)]));
        const sel = document.getElementById('sel-plan');
        const hint = document.getElementById('plan-cobro-hint');
        if (!sel || !hint) return;
        function actualizar() {
            const p = sel.value;
            if (p === 'beta') {
                hint.textContent = planes.beta?.nota || 'Plan Beta: sin cargo de arranque.';
                return;
            }
            const plan = planes[p];
            if (!plan) { hint.textContent = ''; return; }
            hint.textContent = 'Cobro sugerido: S/' + arranque.monto + ' al alta (instalación + mes 1). Desde mes 2: S/' + plan.mensual + '/mes.';
        }
        sel.addEventListener('change', actualizar);
        actualizar();
    })();
    </script>
    @endpush
    @endif

    @if($empresa->exists && $onboardingOk)
    <form method="post" action="{{ route('admin.empresas.reiniciar-onboarding', $empresa) }}" style="margin-top:1rem"
          onsubmit="return confirm('¿Reiniciar el asistente? El administrador deberá completarlo de nuevo.')">
        @csrf
        <button type="submit" class="btn" style="background:#94a3b8;color:#fff;font-size:.85rem">Reiniciar wizard de configuración</button>
    </form>
    @endif
</div>
@endsection
