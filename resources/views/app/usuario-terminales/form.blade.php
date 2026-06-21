@extends('layouts.app')

@section('titulo', 'Asignar terminales')

@section('contenido')
<h1 style="margin-bottom:.35rem">Terminales de {{ $usuario->nombre }}</h1>
<p style="color:#64748b;margin-bottom:1rem">{{ $usuario->etiquetaRol() }} · {{ $usuario->usuario }}</p>

@if($terminales->isEmpty())
<div class="alert-warning">
    No hay terminales activas. <a href="{{ route('terminales.crear') }}">Cree al menos una terminal</a> antes de asignar.
</div>
@else
<div class="card" style="max-width:640px">
    <form method="post" action="{{ route('usuario-terminales.actualizar', $usuario) }}">
        @csrf
        @method('PUT')

        <p style="font-size:.9rem;color:#64748b;margin-bottom:1rem">
            Marque las terminales que puede usar y elija cuál se activará automáticamente al iniciar sesión.
        </p>

        <table class="data">
            <thead>
                <tr>
                    <th>Usar</th>
                    <th>Terminal</th>
                    <th>Local</th>
                    <th>Por defecto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terminales as $t)
                @php $checked = in_array($t->id, old('terminales', $asignadas), true); @endphp
                <tr>
                    <td>
                        <input type="checkbox" name="terminales[]" value="{{ $t->id }}"
                               @checked($checked) class="chk-terminal" data-id="{{ $t->id }}">
                    </td>
                    <td>
                        <strong>{{ $t->codigo }}</strong> — {{ $t->nombre }}<br>
                        <small style="color:#64748b">Caja {{ $t->caja }}</small>
                    </td>
                    <td>{{ $t->local ?? '—' }}</td>
                    <td>
                        <input type="radio" name="terminal_defecto" value="{{ $t->id }}"
                               @checked((int) old('terminal_defecto', $defectoId) === $t->id)
                               class="rad-defecto" data-id="{{ $t->id }}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @error('terminales')
        <p style="color:#991b1b;margin-top:.75rem;font-size:.9rem">{{ $message }}</p>
        @enderror

        <div style="margin-top:1rem">
            <button type="submit" class="btn btn-primary">Guardar asignación</button>
            <a href="{{ route('usuario-terminales.index') }}" style="margin-left:.5rem">Cancelar</a>
        </div>
    </form>
</div>
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.chk-terminal').forEach(chk => {
    chk.addEventListener('change', () => {
        const id = chk.dataset.id;
        const radio = document.querySelector(`.rad-defecto[data-id="${id}"]`);
        if (!chk.checked && radio.checked) {
            const primero = document.querySelector('.chk-terminal:checked');
            if (primero) {
                document.querySelector(`.rad-defecto[data-id="${primero.dataset.id}"]`).checked = true;
            } else {
                radio.checked = false;
            }
        }
        if (chk.checked && !document.querySelector('.rad-defecto:checked')) {
            radio.checked = true;
        }
    });
});
</script>
@endpush
