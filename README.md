<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FinFlow') — 50·30·20</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" defer></script>
</head>
<body class="h-full font-sans antialiased">
<div class="flex h-full">

    <aside class="w-60 bg-gray-900 flex flex-col shrink-0 fixed inset-y-0 left-0 z-40">
        <div class="px-5 py-6 border-b border-gray-800">
            <span class="text-2xl font-black text-white tracking-tight">Fin<span class="text-red-500">Flow</span></span>
            <div class="mt-1 text-xs text-gray-500 font-mono">50 · 30 · 20</div>
        </div>
        <div class="mx-3 mt-3 px-3 py-2 bg-gray-800 rounded-lg">
            <div class="text-xs text-gray-500 font-mono uppercase tracking-widest">Sesión</div>
            <div class="text-sm font-semibold text-gray-200 mt-0.5">{{ auth()->user()->name }}</div>
            @if(auth()->user()->isAdmin())
                <div class="mt-1 inline-block text-xs px-2 py-0.5 bg-red-900/50 text-red-400 rounded">Administrador</div>
            @endif
        </div>
        <nav class="flex-1 px-3 py-4 overflow-y-auto">
            @if(auth()->user()->isAdmin())
                <div class="text-xs text-gray-600 font-mono uppercase tracking-widest px-2 pt-2 pb-1">Admin</div>
                <x-nav-link href="{{ route('admin.dashboard') }}"   :active="request()->routeIs('admin.dashboard')">📊 Resumen</x-nav-link>
                <x-nav-link href="{{ route('admin.clients.index') }}" :active="request()->routeIs('admin.clients.*')">👥 Clientes</x-nav-link>
                <x-nav-link href="{{ route('admin.logs.index') }}"   :active="request()->routeIs('admin.logs.*')">📋 Audit Logs</x-nav-link>
                <x-nav-link href="{{ route('admin.settings.index') }}" :active="request()->routeIs('admin.settings.*')">⚙️ Configuración</x-nav-link>
            @else
                <div class="text-xs text-gray-600 font-mono uppercase tracking-widest px-2 pt-2 pb-1">Principal</div>
                <x-nav-link href="{{ route('dashboard') }}"           :active="request()->routeIs('dashboard')">📊 Dashboard</x-nav-link>
                <x-nav-link href="{{ route('transactions.index') }}"  :active="request()->routeIs('transactions.*')">💳 Transacciones</x-nav-link>
                <x-nav-link href="{{ route('income.index') }}"        :active="request()->routeIs('income.*')">💵 Mis Ingresos</x-nav-link>
                <x-nav-link href="{{ route('goals.index') }}"         :active="request()->routeIs('goals.*')">🎯 Metas de Ahorro</x-nav-link>
            @endif
        </nav>
        <div class="border-t border-gray-800 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-500 hover:bg-gray-800 hover:text-gray-300 text-sm font-medium transition-colors">
                    🚪 Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 ml-60 flex flex-col min-h-screen">
        <header class="bg-white border-b border-gray-200 flex items-center justify-between px-7 py-4 sticky top-0 z-30">
            <h1 class="text-base font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-3">
                @yield('topbar-actions')
                <span class="text-xs font-mono text-gray-400 bg-gray-100 px-3 py-1.5 rounded-lg">{{ now()->translatedFormat('F Y') }}</span>
            </div>
        </header>

        @if(session('success'))
        <div class="mx-7 mt-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
            ✅ {{ session('success') }}
        </div>
        @endif
        @if(session('error') || $errors->any())
        <div class="mx-7 mt-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            ⚠️ {{ session('error') ?? $errors->first() }}
        </div>
        @endif

        <main class="flex-1 p-7">@yield('content')</main>
    </div>
</div>
@stack('scripts')
</body>
</html>
