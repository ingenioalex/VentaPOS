@extends('layouts.app')

@section('titulo', 'Permisos por rol')

@section('contenido')
<h1 style="margin-bottom:.5rem">Permisos por rol</h1>
<p style="color:#64748b;font-size:.9rem;margin-bottom:1.5rem">
    Defina qué módulos puede ver cada rol dentro de los contratados por su empresa.
    Los módulos <strong>Usuarios</strong>, <strong>Permisos</strong> y <strong>Configuración</strong> son exclusivos del administrador.
    Si necesita activar más módulos, contacte al soporte de VentaPOS.
</p>

<div class="card" style="overflow:auto">
    <form method="post" action="{{ route('permisos.actualizar') }}">
        @csrf
        @method('PUT')

        <table class="data">
            <thead>
                <tr>
                    <th>Módulo</th>
                    @foreach($roles as $rol)
                    <th style="text-align:center">{{ config('permisos.etiquetas_rol.'.$rol, $rol) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($modulosConfigurables as $clave => $etiqueta)
                <tr>
                    <td>{{ $etiqueta }}</td>
                    @foreach($roles as $rol)
                    <td style="text-align:center">
                        <input type="checkbox"
                               name="permisos[{{ $rol }}][]"
                               value="{{ $clave }}"
                               @checked(in_array($clave, $permisos[$rol] ?? [], true))>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:1rem">
            <button type="submit" class="btn btn-primary">Guardar permisos</button>
        </div>
    </form>
</div>
@endsection
