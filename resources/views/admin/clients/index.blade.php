{{-- resources/views/admin/clients/index.blade.php --}}
@extends('layouts.app')
@section('page-title', 'Gestión de Clientes')

@section('topbar-actions')
    <button onclick="document.getElementById('modal-new-client').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700">
        ＋ Crear cuenta
    </button>
@endsection

@section('content')

{{-- Search --}}
<form method="GET" class="mb-5">
    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o email..."
        class="w-full max-w-md border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
</form>

{{-- Clients grid --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @forelse($users as $client)
    <div class="bg-white border border-gray-200 rounded-xl p-5 hover:border-gray-400 transition-colors cursor-pointer"
         onclick="window.location='{{ route('admin.clients.show', $client) }}'">

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-black text-white"
                 style="background: hsl({{ crc32($client->email) % 360 }}, 50%, 35%)">
                {{ strtoupper(substr($client->name, 0, 1) . substr(strrchr($client->name, ' '), 1, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-bold truncate">{{ $client->name }}</div>
                <div class="text-xs text-gray-400 font-mono truncate">{{ $client->email }}</div>
            </div>
            <span class="text-xs px-2 py-1 rounded-full font-semibold
                {{ $client->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                {{ $client->is_active ? 'Activo' : 'Inactivo' }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2">
            @foreach([
                ['val' => '$'.number_format($client->monthly_income, 0), 'lbl' => 'Ingreso'],
                ['val' => '$'.number_format($client->monthly_income * 0.50, 0), 'lbl' => '50% N'],
                ['val' => '$'.number_format($client->monthly_income * 0.20, 0), 'lbl' => '20% A'],
            ] as $stat)
            <div class="bg-gray-50 rounded-lg p-2.5 text-center">
                <div class="text-sm font-black text-gray-900 font-mono">{{ $stat['val'] }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $stat['lbl'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="mt-3 text-xs text-gray-400 font-mono">
            {{ $client->transactions_count }} transacciones · MFA {{ $client->hasMfaEnabled() ? '✅' : '⚠️' }}
        </div>
    </div>
    @empty
    <div class="col-span-3 py-16 text-center text-gray-400">
        No se encontraron clientes.
    </div>
    @endforelse
</div>

{{ $users->withQueryString()->links() }}

{{-- New client modal --}}
<div id="modal-new-client" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
        <h2 class="text-xl font-black mb-1">Crear cuenta de consumidor</h2>
        <p class="text-sm text-gray-500 mb-6">El usuario recibirá un email con credenciales temporales y deberá configurar MFA.</p>

        <form method="POST" action="{{ route('admin.clients.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Nombre completo</label>
                <input type="text" name="name" required placeholder="Juan García"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Email</label>
                <input type="email" name="email" required placeholder="juan@email.com"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Ingreso mensual inicial</label>
                <input type="number" name="monthly_income" min="0" step="0.01" placeholder="3000.00"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
            </div>
            <div class="mb-6">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Moneda</label>
                <select name="currency" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                    <option value="USD">USD — Dólar</option>
                    <option value="MXN">MXN — Peso Mexicano</option>
                    <option value="EUR">EUR — Euro</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-new-client').classList.add('hidden')"
                    class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-semibold hover:bg-gray-50">Cancelar</button>
                <button type="submit"
                    class="px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700">Crear cuenta</button>
            </div>
        </form>
    </div>
</div>

@endsection
