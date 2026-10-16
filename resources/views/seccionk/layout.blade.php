<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sección K - Promoción 2001')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-bg-light {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        @media (min-width: 768px) {
            .card-hover:hover { transform: translateY(-5px); }
        }
        input, select, textarea, button { max-width: 100%; }
        @media (max-width: 640px) {
            input, select, textarea { font-size: 16px; }
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 0;
            height: 100%;
            width: 2px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
        }
        
        .timeline-item:last-child::before {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 overflow-x-hidden">
    <!-- Navbar -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 sm:h-16 gap-2">
                <div class="flex items-center min-w-0">
                    <a href="{{ route('seccionk.index') }}" class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                        <div class="bg-white rounded-lg p-1.5 sm:p-2 flex-shrink-0">
                            <i class="fas fa-users text-purple-600 text-lg sm:text-2xl"></i>
                        </div>
                        <div class="text-white min-w-0">
                            <div class="font-bold text-base sm:text-lg leading-tight">Sección K</div>
                            <div class="text-[11px] sm:text-xs text-purple-200">Promoción 2001</div>
                        </div>
                    </a>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                    @yield('nav-actions')
                    <a href="{{ route('seccionk.index') }}" class="text-white hover:text-purple-200 transition px-2 py-2 text-sm">
                        <i class="fas fa-home sm:mr-2"></i><span class="hidden sm:inline">Inicio</span>
                    </a>
                    <a href="{{ route('seccionk.registro') }}" class="bg-white text-purple-600 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg font-semibold hover:bg-purple-50 transition text-sm whitespace-nowrap">
                        <i class="fas fa-user-plus sm:mr-2"></i><span class="hidden xs:inline sm:inline">Registrarse</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-bold text-lg mb-4">Sección K - Promoción 2001</h3>
                    <p class="text-gray-400 text-sm">
                        Plataforma de gestión y seguimiento de eventos y cuotas mensuales.
                    </p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">Enlaces Rápidos</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('seccionk.index') }}" class="text-gray-400 hover:text-white transition">Consultar</a></li>
                        <li><a href="{{ route('seccionk.registro') }}" class="text-gray-400 hover:text-white transition">Registrarse</a></li>
                        <li><a href="{{ route('seccionk.admin.login') }}" class="text-gray-400 hover:text-white transition">Panel Admin</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">Contacto</h3>
                    <p class="text-gray-400 text-sm">
                        <i class="fas fa-envelope mr-2"></i>seccionk2001@gmail.com<br>
                        <i class="fas fa-phone mr-2"></i>+51 987 654 321
                    </p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Sección K - Promoción 2001. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
