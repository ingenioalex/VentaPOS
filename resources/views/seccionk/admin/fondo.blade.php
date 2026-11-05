@extends('seccionk.admin.layout')

@section('title', 'Ingresos y egresos - Admin Sección K')
@section('page-title', 'Ingresos / egresos del fondo')

@section('content')
<p class="text-sm text-gray-600 mb-6">
    El fondo total de la sección. Un préstamo sale del fondo (egreso) y se muestra en el dashboard
    hasta que lo devuelvan. La devolución es un ingreso. Esto no cambia cuotas, eventos ni pagos ya cargados.
</p>

@include('seccionk.admin.partials.fondo-movimientos', [
    'volver' => 'fondo',
    'mostrarHistorial' => true,
])
@endsection
