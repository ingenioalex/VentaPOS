<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sección K</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center mb-4 shadow-xl">
                <i class="fas fa-shield-alt text-purple-600 text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Panel de Administración</h1>
            <p class="text-purple-200">Sección K - Promoción 2001</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <p class="text-red-700 text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('seccionk.admin.login.post') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="usuario" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user text-purple-600 mr-2"></i>Usuario
                    </label>
                    <input 
                        type="text" 
                        name="usuario" 
                        id="usuario" 
                        required
                        autofocus
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition"
                        value="{{ old('usuario') }}"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-purple-600 mr-2"></i>Contraseña
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition"
                    >
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        id="remember"
                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-700">
                        Recordarme
                    </label>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition transform hover:scale-105"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <a href="{{ route('seccionk.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al sitio público
                </a>
            </div>
        </div>

        <div class="mt-6 text-center">
            <p class="text-white text-sm">
                <i class="fas fa-info-circle mr-2"></i>
                Credenciales por defecto: admin / seccionk2026
            </p>
        </div>
    </div>
</body>
</html>
