<x-mail::message>
# Restablecer clave de acceso

Hola {{ $nombre }},

Recibió este correo porque solicitó restablecer la clave del panel de administración de VentasÁgil.

<x-mail::button :url="$url">
Crear nueva clave
</x-mail::button>

El enlace expira en **{{ $expiraMinutos }} minutos** y solo puede usarse una vez.

Si no solicitó este cambio, ignore este mensaje. Su clave actual seguirá siendo válida.

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
