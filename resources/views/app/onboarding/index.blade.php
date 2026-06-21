@extends('layouts.onboarding')

@section('titulo', 'Configuración inicial')

@push('head')
<style>
    .wizard { max-width: 720px; margin: 0 auto; }
    .wizard-steps { display: flex; gap: .5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .wizard-step { flex: 1; min-width: 120px; text-align: center; padding: .55rem .4rem; border-radius: .5rem; font-size: .78rem; font-weight: 600; background: #e2e8f0; color: #64748b; }
    .wizard-step.activo { background: #0f766e; color: #fff; }
    .wizard-step.hecho { background: #d1fae5; color: #065f46; }
    .wizard-panel { display: none; }
    .wizard-panel.activo { display: block; }
    .hint { color: #64748b; font-size: .88rem; margin-bottom: 1rem; }
    .chip-list { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: 1rem; }
    .chip { background: #f0fdfa; color: #0f766e; padding: .25rem .55rem; border-radius: 999px; font-size: .78rem; font-weight: 600; }
</style>
@endpush

@section('contenido')
<div class="wizard">
    <h1 style="margin-bottom:.35rem">Bienvenido a VentaPOS</h1>
    <p class="hint">Configure su negocio en pocos pasos para empezar a vender.</p>

    <div class="wizard-steps">
        @foreach([1 => 'Negocio', 2 => 'Terminal', 3 => 'Productos', 4 => 'Cajero'] as $n => $label)
        <div class="wizard-step {{ $paso === $n ? 'activo' : ($paso > $n ? 'hecho' : '') }}">{{ $n }}. {{ $label }}</div>
        @endforeach
    </div>

    @if($paso > 1)
    <div style="margin-bottom:1rem">
        <a href="{{ route('onboarding.index') }}" style="font-size:.85rem;color:#64748b">← Volver al asistente</a>
    </div>
    @endif

    <div class="wizard-panel {{ $paso === 1 ? 'activo' : '' }}">
        <div class="card">
            <h3 style="margin-bottom:1rem">Paso 1 — Datos del negocio</h3>
            <p class="hint">Al guardar se crearán categorías sugeridas según su rubro.</p>
            <form method="post" action="{{ route('onboarding.paso-empresa') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre comercial *</label>
                        <input name="nombre" required value="{{ old('nombre', $empresa->nombre) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">RUC</label>
                        <input name="ruc" maxlength="11" value="{{ old('ruc', $empresa->ruc) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Tipo de negocio *</label>
                        <select name="tipo_negocio" id="tipo_negocio" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                            @foreach(['bodega','ferreteria','farmacia','otro'] as $t)
                            <option value="{{ $t }}" @selected(old('tipo_negocio', $empresa->tipo_negocio) === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Teléfono</label>
                        <input name="telefono" value="{{ old('telefono', $empresa->telefono) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div style="grid-column:1/-1">
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Dirección</label>
                        <input name="direccion" value="{{ old('direccion', $empresa->direccion) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Serie boleta *</label>
                        <input name="serie_boleta" required value="{{ old('serie_boleta', ($empresa->configuracion ?? [])['serie_boleta'] ?? 'B001') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Serie factura *</label>
                        <input name="serie_factura" required value="{{ old('serie_factura', ($empresa->configuracion ?? [])['serie_factura'] ?? 'F001') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar y continuar</button>
            </form>
        </div>
    </div>

    <div class="wizard-panel {{ $paso === 2 ? 'activo' : '' }}">
        <div class="card">
            <h3 style="margin-bottom:1rem">Paso 2 — Terminal / caja principal</h3>
            <p class="hint">Identifica el punto de venta donde se emitirán los comprobantes.</p>
            <form method="post" action="{{ route('onboarding.paso-terminal') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Código *</label>
                        <input name="codigo" required maxlength="10" value="{{ old('codigo', $terminal?->codigo ?? '01') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre *</label>
                        <input name="nombre" required value="{{ old('nombre', $terminal?->nombre ?? 'Caja principal') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Local / sucursal</label>
                        <input name="local" value="{{ old('local', $terminal?->local ?? $empresa->direccion) }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Caja *</label>
                        <input name="caja" required value="{{ old('caja', $terminal?->caja ?? '01') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar y continuar</button>
            </form>
        </div>
    </div>

    <div class="wizard-panel {{ $paso === 3 ? 'activo' : '' }}">
        <div class="card">
            <h3 style="margin-bottom:1rem">Paso 3 — Productos</h3>
            @if($tiene_categorias)
            <p style="color:#065f46;margin-bottom:.75rem">✓ Categorías listas para su rubro.</p>
            <div class="chip-list">
                @foreach(($plantilla_negocio['categories'] ?? []) as $cat)
                <span class="chip">{{ $cat }}</span>
                @endforeach
            </div>
            @endif
            @if($tiene_productos)
            <p style="color:#065f46;margin-bottom:1rem">✓ Ya tiene {{ $empresa->productos()->count() }} producto(s) registrado(s).</p>
            @else
            <p class="hint">Importe su catálogo desde CSV o cree productos manualmente. Puede volver aquí después de cargar datos.</p>
            @endif
            <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                <a href="{{ route('productos.importar') }}" class="btn btn-primary">Importar CSV</a>
                <a href="{{ route('productos.crear') }}" class="btn" style="background:#64748b;color:#fff">Crear producto</a>
                <a href="{{ route('productos.plantilla') }}" class="btn" style="background:#e2e8f0;color:#334155">Descargar plantilla</a>
                <form method="post" action="{{ route('onboarding.paso-productos') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn" style="background:#2563eb;color:#fff">
                        {{ $tiene_productos ? 'Continuar' : 'Continuar sin productos' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="wizard-panel {{ $paso === 4 ? 'activo' : '' }}">
        <div class="card">
            <h3 style="margin-bottom:1rem">Paso 4 — Cajero inicial</h3>
            <p class="hint">
                Cree el primer cajero y asígnelo a la terminal
                <strong>{{ $terminal?->nombre ?? 'Caja principal' }}</strong> ({{ $terminal?->codigo ?? '01' }}).
            </p>
            <form method="post" action="{{ route('onboarding.paso-cajero') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Nombre *</label>
                        <input name="nombre" required value="{{ old('nombre') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Usuario *</label>
                        <input name="usuario" required pattern="[A-Za-z0-9_-]+" value="{{ old('usuario', 'cajero1') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Contraseña *</label>
                        <input type="password" name="password" required minlength="6" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                    <div>
                        <label style="font-weight:600;display:block;margin-bottom:.35rem">Confirmar *</label>
                        <input type="password" name="password_confirmation" required minlength="6" style="width:100%;padding:.6rem;border:1px solid #cbd5e1;border-radius:.5rem">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Finalizar configuración</button>
            </form>
            <form method="post" action="{{ route('onboarding.omitir-cajero') }}" style="margin-top:.75rem" onsubmit="return confirm('¿Omitir creación de cajero? Podrá crearlo después en Usuarios.')">
                @csrf
                <button type="submit" class="btn" style="background:#94a3b8;color:#fff">Omitir por ahora</button>
            </form>
        </div>
    </div>
</div>
@endsection
