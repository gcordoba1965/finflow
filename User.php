@extends('layouts.app')
@section('page-title', 'Metas de Ahorro')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div class="text-sm text-gray-500">{{ $goals->count() }} metas activas</div>
    <button onclick="document.getElementById('modal-goal').classList.remove('hidden')"
        class="px-4 py-2 bg-amber-700 text-white text-sm font-bold rounded-lg hover:bg-amber-800">
        + Nueva meta
    </button>
</div>

<div class="grid grid-cols-2 gap-4">
    @forelse($goals as $goal)
    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-4xl">{{ $goal->icon }}</span>
            <div>
                <div class="text-base font-bold">{{ $goal->name }}</div>
                @if($goal->deadline)
                <div class="text-xs text-gray-400 font-mono">Meta: {{ $goal->deadline->format('d M Y') }}</div>
                @endif
            </div>
            @if($goal->is_completed)
            <span class="ml-auto text-xs px-2 py-1 bg-green-50 text-green-700 rounded-full font-semibold">Completada</span>
            @endif
        </div>
        <div class="flex justify-between text-sm mb-2">
            <span class="text-gray-500">Progreso</span>
            <span class="font-bold font-mono">${{ number_format($goal->saved_amount, 0) }} / ${{ number_format($goal->target_amount, 0) }}</span>
        </div>
        <div class="h-3 bg-gray-100 rounded-full overflow-hidden mb-1">
            <div class="h-full bg-amber-600 rounded-full transition-all" style="width: {{ $goal->progress_percent }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mb-4">
            <span>{{ $goal->progress_percent }}%</span>
            <span>Faltan ${{ number_format($goal->remaining, 0) }}</span>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('goals.add-funds', $goal) }}" class="flex gap-2 flex-1">
                @csrf @method('PATCH')
                <input type="number" name="amount" min="0.01" step="0.01" placeholder="Monto" class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-gray-900">
                <button type="submit" class="px-4 py-1.5 bg-amber-600 text-white text-xs font-bold rounded-lg">+ Fondos</button>
            </form>
            <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Eliminar meta?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 border border-red-200 text-red-500 text-xs rounded-lg hover:bg-red-50">Eliminar</button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-2 py-16 text-center text-gray-400">No hay metas. Crea tu primera meta de ahorro.</div>
    @endforelse
</div>

<div id="modal-goal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
        <h2 class="text-xl font-black mb-6">Nueva meta de ahorro</h2>
        <form method="POST" action="{{ route('goals.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Nombre de la meta</label>
                <input name="name" required placeholder="ej. Vacaciones, Auto nuevo..." class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Monto objetivo</label>
                    <input name="target_amount" type="number" min="1" step="0.01" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Fecha límite</label>
                    <input name="deadline" type="date" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-goal').classList.add('hidden')" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-semibold">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-amber-700 text-white rounded-lg text-sm font-bold">Crear meta</button>
            </div>
        </form>
    </div>
</div>
@endsection
