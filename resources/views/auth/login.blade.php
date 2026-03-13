<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinFlow — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-10 w-full max-w-md">
        <h1 class="text-3xl font-black text-white mb-1">Fin<span class="text-red-500">Flow</span></h1>
        <p class="text-gray-500 text-sm mb-8 font-mono">50 · 30 · 20 · finanzas personales</p>

        @if($errors->any())
            <div class="bg-red-900/30 border border-red-700 text-red-400 rounded-lg px-4 py-3 mb-5 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login">
            @csrf
            <div class="mb-4">
                <label class="block text-xs text-gray-500 font-mono uppercase tracking-widest mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500">
            </div>
            <div class="mb-6">
                <label class="block text-xs text-gray-500 font-mono uppercase tracking-widest mb-1.5">Contraseña</label>
                <input type="password" name="password" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500">
            </div>
            <button type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors">
                Iniciar sesión →
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-600 font-mono">
            🔐 MFA activo · Laravel Fortify
        </div>

        <div class="mt-6 p-4 bg-gray-800 rounded-lg text-xs font-mono text-gray-400">
            <div class="mb-1">👤 <span class="text-gray-300">maria@email.com</span> / User1234!</div>
            <div>⚙️ <span class="text-gray-300">admin@finflow.com</span> / Admin1234!</div>
        </div>
    </div>
</body>
</html>
