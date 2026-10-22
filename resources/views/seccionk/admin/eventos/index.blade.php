@extends('seccionk.admin.layout')

@section('title', 'Eventos - Admin Sección K')
@section('page-title', 'Gestión de Eventos')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <form method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <select name="tipo" class="px-4 py-2 border rounded-lg w-full sm:w-auto">
            <option value="">Todos los tipos</option>
            <option value="cuota_mensual" {{ request('tipo') === 'cuota_mensual' ? 'selected' : '' }}>Cuotas mensuales</option>
            <option value="evento" {{ request('tipo') === 'evento' ? 'selected' : '' }}>Eventos</option>
        </select>
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="mostrar_inactivos" {{ request('mostrar_inactivos') ? 'checked' : '' }}>
            <span>Mostrar inactivos</span>
        </label>
        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Filtrar</button>
    </form>
    <a href="{{ route('seccionk.admin.eventos.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 text-center">
        <i class="fas fa-plus mr-2"></i>Nuevo Evento
    </a>
</div>

<div class="space-y-3 lg:hidden">
    @forelse($eventos as $evento)
        <div class="bg-white rounded-xl shadow p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 break-words">{{ $evento->nombre }}</p>
                    <p class="text-sm text-gray-500">{{ $evento->fecha->format('d/m/Y') }}</p>
                </div>
                @if($evento->tipo === 'cuota_mensual')
                    <span class="px-2 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-semibold flex-shrink-0">Cuota</span>
                @else
                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold flex-shrink-0">Evento</span>
                @endif
            </div>
            <div class="grid grid-cols-3 gap-2 mt-3 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Sugerido</p>
                    <p class="font-semibold">S/ {{ number_format($evento->monto, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Recaudado</p>
                    <p class="font-semibold text-green-700">S/ {{ number_format($resumenPorEvento[$evento->id]['recaudado'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Participan</p>
                    <p class="font-semibold">{{ $resumenPorEvento[$evento->id]['participantes'] ?? 0 }}/{{ $resumenPorEvento[$evento->id]['total_promociones'] ?? 0 }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 mt-3 text-sm">
                <a href="{{ route('seccionk.admin.eventos.detalle', $evento) }}" class="text-green-700 font-semibold">Detalle</a>
                <a href="{{ route('seccionk.admin.eventos.participantes', $evento) }}" class="text-purple-700 font-semibold">Participantes</a>
                <a href="{{ route('seccionk.admin.eventos.edit', $evento) }}" class="text-blue-700 font-semibold">Editar</a>
                <button
                    type="button"
                    class="text-amber-700 font-semibold"
                    onclick="abrirCierre({{ $evento->id }}, @js($evento->nombre), {{ $resumenPorEvento[$evento->id]['recaudado'] ?? 0 }}, {{ $resumenPorEvento[$evento->id]['utilizado'] ?? 0 }}, {{ ($resumenPorEvento[$evento->id]['cerrado'] ?? false) ? 'true' : 'false' }})"
                >
                    {{ ($resumenPorEvento[$evento->id]['cerrado'] ?? false) ? 'Actualizar uso' : 'Cerrar' }}
                </button>
            </div>
            @if($resumenPorEvento[$evento->id]['cerrado'] ?? false)
                <p class="text-xs text-amber-700 mt-2">Cerrado · usado S/ {{ number_format($resumenPorEvento[$evento->id]['utilizado'] ?? 0, 2) }} · saldo S/ {{ number_format($resumenPorEvento[$evento->id]['saldo'] ?? 0, 2) }}</p>
            @endif
        </div>
    @empty
        <p class="text-center text-gray-500 py-8">No hay eventos registrados</p>
    @endforelse
</div>

<div class="hidden lg:block bg-white rounded-xl shadow-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tipo</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Sugerido</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Recaudado</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Participantes</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Cierre</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($eventos as $evento)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">{{ $evento->fecha->format('d/m/Y') }}</td>
                <td class="px-6 py-4">
                    <div class="font-semibold">{{ $evento->nombre }}</div>
                    @if($evento->mes_cuota)
                        <div class="text-xs text-gray-500">{{ $evento->mes_cuota }}</div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($evento->tipo === 'cuota_mensual')
                        <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-semibold">Cuota mensual</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Evento</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right font-semibold">S/ {{ number_format($evento->monto, 2) }}</td>
                <td class="px-6 py-4 text-right font-semibold text-green-700">S/ {{ number_format($resumenPorEvento[$evento->id]['recaudado'] ?? 0, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="font-semibold">{{ $resumenPorEvento[$evento->id]['participantes'] ?? 0 }}/{{ $resumenPorEvento[$evento->id]['total_promociones'] ?? 0 }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($resumenPorEvento[$evento->id]['cerrado'] ?? false)
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">Cerrado</span>
                        <div class="text-xs text-gray-500 mt-1">Usado S/ {{ number_format($resumenPorEvento[$evento->id]['utilizado'] ?? 0, 2) }}</div>
                    @else
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">Abierto</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    @if($evento->activo)
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Activo</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Inactivo</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center space-x-3">
                    <button
                        type="button"
                        class="text-amber-600 hover:text-amber-800"
                        title="Cerrar y declarar uso"
                        onclick="abrirCierre({{ $evento->id }}, @js($evento->nombre), {{ $resumenPorEvento[$evento->id]['recaudado'] ?? 0 }}, {{ $resumenPorEvento[$evento->id]['utilizado'] ?? 0 }}, {{ ($resumenPorEvento[$evento->id]['cerrado'] ?? false) ? 'true' : 'false' }})"
                    >
                        <i class="fas fa-lock"></i>
                    </button>
                    <a href="{{ route('seccionk.admin.eventos.detalle', $evento) }}" class="text-green-600 hover:text-green-800" title="Ver detalle">
                        <i class="fas fa-list-ol"></i>
                    </a>
                    <a href="{{ route('seccionk.admin.eventos.participantes', $evento) }}" class="text-purple-600 hover:text-purple-800" title="Participantes">
                        <i class="fas fa-users"></i>
                    </a>
                    <a href="{{ route('seccionk.admin.eventos.edit', $evento) }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('seccionk.admin.eventos.destroy', $evento) }}" method="POST" class="inline" onsubmit="return confirm('¿Ocultar este evento?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Ocultar">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-12 text-center text-gray-500">No hay eventos registrados</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $eventos->withQueryString()->links() }}
</div>

<div id="modal-cierre" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="cerrarCierre()"></div>
    <div class="relative max-w-md mx-auto mt-24 bg-white rounded-xl shadow-xl p-5">
        <h3 id="cierre-titulo" class="text-lg font-bold text-gray-900 mb-4">Cerrar evento</h3>
        <form id="form-cierre" method="POST">
            @csrf
            <label class="block text-sm font-semibold text-gray-700 mb-1">Monto utilizado (S/)</label>
            <input id="cierre-monto" type="number" step="0.01" min="0" name="monto_utilizado" required class="w-full px-3 py-2 border rounded-lg mb-2">
            <p id="cierre-recaudado" class="text-xs text-gray-500 mb-3"></p>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Observación (opcional)</label>
            <input type="text" name="observaciones" maxlength="1000" class="w-full px-3 py-2 border rounded-lg mb-4" placeholder="En qué se usó el dinero">
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="cerrarCierre()" class="px-4 py-2 rounded-lg border">Cancelar</button>
                <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold">Cerrar y declarar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function abrirCierre(id, nombre, recaudado, utilizado, cerrado) {
    document.getElementById('cierre-titulo').textContent = (cerrado ? 'Actualizar uso · ' : 'Cerrar · ') + nombre;
    document.getElementById('form-cierre').action = '{{ url('seccionk/admin/eventos') }}/' + id + '/cerrar';
    document.getElementById('cierre-monto').value = cerrado ? utilizado : recaudado;
    document.getElementById('cierre-recaudado').textContent = 'Recaudado: S/ ' + Number(recaudado).toFixed(2);
    document.getElementById('modal-cierre').classList.remove('hidden');
}
function cerrarCierre() {
    document.getElementById('modal-cierre').classList.add('hidden');
}
</script>
@endsection
