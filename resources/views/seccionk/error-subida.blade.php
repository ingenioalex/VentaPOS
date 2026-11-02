<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foto demasiado pesada - Sección K</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-6">
        <p class="text-sm font-semibold text-amber-700 uppercase tracking-wide">Sección K</p>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">La foto no se pudo subir</h1>
        <p class="text-gray-600 mt-3">
            El celular envió una imagen más grande de lo que acepta el servidor.
            El pago se puede registrar igual: vuelve y envía el monto <strong>sin foto</strong>,
            o elige otra imagen más liviana. La foto es opcional.
        </p>
        <a href="{{ $volver }}" class="mt-6 inline-flex w-full justify-center bg-purple-600 text-white font-semibold px-4 py-3 rounded-lg">
            Volver e intentar de nuevo
        </a>
    </div>
</body>
</html>
