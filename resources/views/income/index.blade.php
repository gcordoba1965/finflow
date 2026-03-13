@extends('layouts.app')
@section('page-title', 'Mis Ingresos')

@section('topbar-actions')
<button onclick="document.getElementById('modal-income').classList.remove('hidden')"
    class="px-4 py-2 bg-green-700 text-white text-sm font-bold rounded-lg hover:bg-green-800">
    + Nueva fuente
</button>
@endsection

@section('content')
<div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 flex justify-between items-center">
    <div>
        <div class="text-xs text-green-600 font-mono uppercase tracking-widest">Total activo mensual</div>
        <div class="text-3xl font-black text-green-700">${{ number_format($totalMonthly, 2) }}</div>
    </div>
    <div class="text-right">
        <div class="text-xs text-gray-400">Presupuesto 50/30/20</div>
        <div class="text-sm font-bold text-gray-700">
            Necesidades: ${{ number_format($totalMonthly * 0.5, 0) }} ·
            Deseos: ${{ number_format($totalMonthly * 0.3, 0) }} ·
            Ahorro: ${{ number_format($totalMonthly * 0.2, 0) }}
        </div>
    </div>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
    @forelse($sources as $source)
    <div class="bg-white border rounded-xl p-5 {{ $source->is_active ? 'border-gray-200' : 'border-gray-100 opacity-60' }}">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-3xl">{{ $source->icon }}</span>
            <div class="flex-1">
                <div class="text-sm font-bold">{{ $source->name }}</div>
                <div class="text-xs text-gray-400 font-mono">{{ ucfirst($source->frequency) }}</div>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                {{ $source->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $source->is_active ? 'Activo' : 'Pausado' }}
            </span>
        </div>
        <div class="text-2xl font-black text-green-700">${{ number_format($source->amount, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">${{ number_format($source->monthly_amount, 2) }}/mes equivalente</div>
        <div class="flex gap-2 mt-4">
            <form method="POST" action="{{ route('income.toggle', $source) }}">
                @csrf @method('PATCH')
                <button class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">
                    {{ $source->is_active ? 'Pausar' : 'Activar' }}
                </button>
            </form>
            <form method="POST" action="{{ route('income.destroy', $source) }}" onsubmit="return confirm('Eliminar?')">
                @csrf @method('DELETE')
                <button class="text-xs px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50">Eliminar</button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-12 text-center text-gray-400">No hay fuentes de ingreso.</div>
    @endforelse
</div>

<div class="bg-white border border-gray-200 rounded-xl p-5">
    <div class="text-sm font-bold mb-4">Historial de ingresos (6 meses)</div>
    <canvas id="ch-income" height="120"></canvas>
</div>

{{-- Add income modal --}}
<div id="modal-income" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
        <h2 class="text-xl font-black mb-6">Nueva fuente de ingreso</h2>
        <form method="POST" action="{{ route('income.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Nombre</label>
                <input name="name" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Monto</label>
                    <input name="amount" type="number" step="0.01" min="0.01" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Frecuencia</label>
                    <select name="frequency" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                        <option value="monthly">Mensual</option>
                        <option value="biweekly">Quincenal</option>
                        <option value="weekly">Semanal</option>
                        <option value="annual">Anual</option>
                        <option value="variable">Variable</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-income').classList.add('hidden')" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-semibold">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-green-700 text-white rounded-lg text-sm font-bold">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const trend = @json($trend);
new Chart(document.getElementById('ch-income'), {
    type: 'line',
    data: {
        labels: trend.map(t => t.label),
        datasets: [{ label: 'Ingreso', data: trend.map(t => t.income),
            borderColor: '#1a6b4a', backgroundColor: 'rgba(26,107,74,0.08)',
            borderWidth: 2, tension: 0.4, fill: true, pointRadius: 4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false } }, y: { grid: { color: '#f3f4f6' } } } }
});
</script>
@endpush
