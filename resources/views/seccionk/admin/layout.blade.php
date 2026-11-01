<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Sección K')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar-active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex-shrink-0">
            <div class="p-6 border-b border-gray-800">
                <div class="flex items-center space-x-3">
                    <div class="bg-purple-600 rounded-lg p-2">
                        <i class="fas fa-users-cog text-white text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-lg">Panel Admin</div>
                        <div class="text-xs text-gray-400">Sección K 2001</div>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('seccionk.admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition {{ request()->routeIs('seccionk.admin.dashboard') ? 'sidebar-active' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('seccionk.admin.promociones.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition {{ request()->routeIs('seccionk.admin.promociones.*') ? 'sidebar-active' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Promociones</span>
                </a>
                <a href="{{ route('seccionk.admin.eventos.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition {{ request()->routeIs('seccionk.admin.eventos.*') ? 'sidebar-active' : '' }}">
                    <i class="fas fa-calendar-alt w-5"></i>
                    <span>Eventos</span>
                </a>
                <a href="{{ route('seccionk.admin.resumen.cuotas') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition {{ request()->routeIs('seccionk.admin.resumen.cuotas') ? 'sidebar-active' : '' }}">
                    <i class="fas fa-table w-5"></i>
                    <span>Resumen cuotas</span>
                </a>
                
                <div class="pt-4 mt-4 border-t border-gray-800">
                    <a href="{{ route('seccionk.index') }}" target="_blank" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                        <i class="fas fa-external-link-alt w-5"></i>
                        <span>Ver sitio público</span>
                    </a>
                    <form action="{{ route('seccionk.admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-900 transition text-left">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ auth('seccionk')->user()->nombre }}</p>
                            <p class="text-xs text-gray-500">Administrador</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                            <p class="text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                            <p class="text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
