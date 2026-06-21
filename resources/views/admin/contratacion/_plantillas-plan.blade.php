@php
    $planesLista = $planesLista ?? config('planes.planes_disponibles', ['beta', 'basico', 'negocio', 'pro']);
    $modulosPorPlan = $modulosPorPlan ?? collect($planesLista)
        ->mapWithKeys(fn ($p) => [$p => app(\App\Services\PermisoService::class)->modulosPorPlan($p)])
        ->all();
@endphp
<div class="card" style="margin-bottom:1.25rem">
    <h3 style="margin-bottom:.35rem">Plantillas de módulos por plan</h3>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem">
        Aplica el paquete de módulos de un plan sin cambiar el plan de la empresa, o use «Seleccionar» en
        <a href="{{ route('admin.contratacion.modulos', $empresa) }}">Módulos</a> para revisar antes de guardar.
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.65rem">
        @foreach($planesLista as $planKey)
        @php
            $planInfo = config('planes.'.$planKey, []);
            $modsPlan = $modulosPorPlan[$planKey] ?? [];
            $esActual = $empresa->plan === $planKey;
            $esBeta = $planKey === 'beta';
        @endphp
        <div style="border:2px solid {{ $esActual ? '#7c3aed' : ($esBeta ? '#fcd34d' : '#e2e8f0') }};border-radius:.65rem;padding:.75rem;background:{{ $esActual ? '#f5f3ff' : ($esBeta ? '#fffbeb' : '#fff') }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.35rem;margin-bottom:.35rem">
                <strong style="font-size:.9rem">{{ $planInfo['nombre'] ?? ucfirst($planKey) }}</strong>
                @if($esActual)<span style="font-size:.65rem;background:#7c3aed;color:#fff;padding:.1rem .35rem;border-radius:999px">Actual</span>@endif
            </div>
            <p style="font-size:.8rem;color:#64748b;margin-bottom:.5rem">
                @if($esBeta)
                {{ $planInfo['nota'] ?? 'Gratis' }}
                @else
                S/ {{ $planInfo['mensual'] ?? 0 }}/mes · {{ count($modsPlan) }} módulos
                @endif
            </p>
            <form method="post" action="{{ route('admin.contratacion.modulos.aplicar-plan', $empresa) }}"
                  onsubmit="return confirm('¿Aplicar módulos del plan {{ $planInfo['nombre'] ?? $planKey }}?')">
                @csrf
                <input type="hidden" name="plan" value="{{ $planKey }}">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:.35rem .5rem;font-size:.8rem">Aplicar módulos</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
