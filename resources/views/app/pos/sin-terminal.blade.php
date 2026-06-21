@extends('layouts.app')

@section('titulo', 'Sin terminal asignada')

@section('contenido')
<div class="card" style="max-width:560px">
    <h1 style="margin-bottom:.75rem">Sin terminal asignada</h1>
    <p style="color:#64748b;line-height:1.6;margin-bottom:1rem">
        Su usuario de cajero no tiene terminales asignadas. Solicite al administrador que configure sus terminales
        en <strong>Usuarios y terminales</strong>.
    </p>
    <a href="{{ route('logout') }}" class="btn" style="background:#64748b;color:#fff"
       onclick="event.preventDefault(); document.getElementById('frm-salir').submit();">Cerrar sesión</a>
    <form id="frm-salir" method="post" action="{{ route('logout') }}" style="display:none">@csrf</form>
</div>
@endsection
