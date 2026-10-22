<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Promociones participando</p>
        <p class="text-3xl font-bold text-purple-600 mt-1">{{ $stats['participando'] }}/{{ $stats['total_promociones'] }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ $stats['participando_cuotas'] }} en cuotas · {{ $stats['participando_eventos'] }} en eventos</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Promociones aportando</p>
        <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $stats['aportando'] }}/{{ $stats['total_promociones'] }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ $stats['aportando_cuotas'] }} en cuotas · {{ $stats['aportando_eventos'] }} en eventos</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Monto cuotas</p>
        <p class="text-3xl font-bold text-green-600 mt-1">S/ {{ number_format($stats['monto_cuotas'], 2) }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ $stats['aportando_cuotas'] }} promociones · {{ $stats['total_cuotas'] }} meses</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Monto eventos</p>
        <p class="text-3xl font-bold text-blue-600 mt-1">S/ {{ number_format($stats['monto_eventos'], 2) }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ $stats['aportando_eventos'] }} promociones · {{ $stats['total_eventos'] }} eventos</p>
    </div>
</div>
