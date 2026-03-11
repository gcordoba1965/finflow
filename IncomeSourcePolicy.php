@extends('layouts.app')
@section('page-title', 'Mis Transacciones')

@section('topbar-actions')
<button onclick="document.getElementById('modal-tx').classList.remove('hidden')"
    class="flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-gray-700">
    + Nuevo movimiento
</button>
@endsection

@section('content')
<div class="flex gap-2 mb-5 flex-wrap">
    @foreach(['all' => 'Todos', 'needs' => 'Necesidades', 'wants' => 'Deseos', 'savings' => 'Ahorro'] as $val => $lbl)
    <a href="{{ route('transactions.index', ['category' => $val === 'all' ? null : $val, 'period' => $period]) }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold border transition-colors
              {{ ($cat ?? 'all') === $val ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:border-gray-400' }}">
        {{ $lbl }}
    </a>
    @endforeach
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between">
        <span class="text-sm font-bold">{{ $transactions->total() }} movimientos</span>
        <form method="POST" action="{{ route('transactions.import') }}" enctype="multipart/form-data" class="flex gap-2">
            @csrf
            <input type="file" name="file" accept=".csv" class="text-xs">
            <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded">Importar CSV</button>
        </form>
    </div>

    @forelse($transactions as $tx)
    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50 last:border-0">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-lg
            {{ match($tx->category) { 'needs' => 'bg-green-50', 'wants' => 'bg-blue-50', 'savings' => 'bg-amber-50', default => 'bg-gray-50' } }}">
            {{ $tx->icon }}
        </div>
        <div class="flex-1">
            <div class="text-sm font-semibold">{{ $tx->description }}</div>
            <div class="text-xs text-gray-400 font-mono">{{ $tx->date->format('d M Y') }}</div>
        </div>
        <span class="text-xs px-2 py-1 rounded font-semibold
            {{ match($tx->category) { 'needs' => 'bg-green-50 text-green-700', 'wants' => 'bg-blue-50 text-blue-700', 'savings' => 'bg-amber-50 text-amber-700', default => 'bg-gray-100 text-gray-600' } }}">
            {{ $tx->category_label }}
        </span>
        <div class="text-sm font-bold font-mono {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-500' }}">
            {{ $tx->type === 'income' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
        </div>
        <form method="POST" action="{{ route('transactions.destroy', $tx) }}" onsubmit="return confirm('Eliminar?')">
            @csrf @method('DELETE')
            <button class="text-xs text-gray-400 hover:text-red-600 px-2 py-1 rounded">Eliminar</button>
        </form>
    </div>
    @empty
    <div class="px-5 py-12 text-center text-sm text-gray-400">No hay transacciones.</div>
    @endforelse
</div>
{{ $transactions->links() }}
@include('components.transaction-modal')
@endsection
