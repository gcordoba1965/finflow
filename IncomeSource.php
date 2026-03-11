{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Dashboard — 50 · 30 · 20')

@section('topbar-actions')
    <button onclick="document.getElementById('modal-tx').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition-colors">
        ＋ Nuevo movimiento
    </button>
@endsection

@section('content')

{{-- ── Stats row ── --}}
<div class="grid grid-cols-4 gap-4 mb-6">

    <div class="bg-white border border-gray-200 rounded-xl p-5 border-t-4 border-t-green-600">
        <div class="text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Ingreso mensual</div>
        <div class="text-2xl font-black text-green-700">${{ number_format($summary['income'], 0) }}</div>
        <div class="text-xs text-gray-400 mt-1">Fuentes activas</div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5 border-t-4 border-t-red-500">
        <div class="text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Total gastado</div>
        <div class="text-2xl font-black text-red-600">${{ number_format($summary['total_spent'], 0) }}</div>
        <div class="text-xs text-gray-400 mt-1">Este mes</div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5 border-t-4 {{ $summary['balance'] >= 0 ? 'border-t-blue-500' : 'border-t-orange-500' }}">
        <div class="text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Balance</div>
        <div class="text-2xl font-black {{ $summary['balance'] >= 0 ? 'text-blue-700' : 'text-orange-600' }}">
            ${{ number_format(abs($summary['balance']), 0) }}
        </div>
        <div class="text-xs text-gray-400 mt-1">{{ $summary['balance'] >= 0 ? '✅ Positivo' : '⚠️ Déficit' }}</div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5 border-t-4 border-t-amber-600">
        <div class="text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Ahorro</div>
        <div class="text-2xl font-black text-amber-700">${{ number_format($summary['spent']['savings'], 0) }}</div>
        <div class="text-xs text-gray-400 mt-1">Meta: ${{ number_format($summary['limits']['savings'], 0) }}</div>
    </div>

</div>

{{-- ── Budget progress ── --}}
<div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-base font-bold">Progreso del Presupuesto</h2>
        <span class="text-xs font-mono text-gray-400">{{ now()->translatedFormat('F Y') }}</span>
    </div>

    @foreach([
        ['key' => 'needs',   'label' => '🏠 Necesidades', 'pct' => '50%', 'color' => 'bg-green-700'],
        ['key' => 'wants',   'label' => '🎉 Deseos',       'pct' => '30%', 'color' => 'bg-blue-700'],
        ['key' => 'savings', 'label' => '💰 Ahorro',       'pct' => '20%', 'color' => 'bg-amber-700'],
    ] as $cat)
    @php
        $spent   = $summary['spent'][$cat['key']];
        $limit   = $summary['limits'][$cat['key']];
        $pct     = $limit > 0 ? min(($spent / $limit) * 100, 100) : 0;
        $over    = $spent > $limit;
    @endphp
    <div class="mb-5">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-bold">{{ $cat['label'] }} <span class="text-xs font-normal text-gray-400">({{ $cat['pct'] }})</span></span>
            <span class="text-xs font-mono {{ $over ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                {{ $over ? '⚠️ ' : '' }}${{ number_format($spent, 0) }} / ${{ number_format($limit, 0) }}
            </span>
        </div>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-1000 {{ $over ? 'bg-red-500' : $cat['color'] }}"
                 style="width: {{ $pct }}%"></div>
        </div>
        <div class="text-xs text-gray-400 mt-1">
            {{ number_format($pct, 1) }}% utilizado ·
            @if($over)
                <span class="text-red-500">Excedido en ${{ number_format($spent - $limit, 0) }}</span>
            @else
                ${{ number_format($limit - $spent, 0) }} disponible
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ── Charts ── --}}
<div class="grid grid-cols-3 gap-4 mb-6">

    <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-bold mb-4">Tendencia — últimos 6 meses</h3>
        <canvas id="ch-trend" height="180"></canvas>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-bold mb-4">Distribución 50/30/20</h3>
        <canvas id="ch-donut" height="180"></canvas>
    </div>

</div>

{{-- ── Recent transactions ── --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="flex justify-between items-center px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold">Movimientos recientes</h3>
        <a href="{{ route('transactions.index') }}" class="text-xs text-gray-500 hover:text-gray-900">Ver todos →</a>
    </div>
    @forelse($recent as $tx)
    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50 transition-colors last:border-0">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-lg shrink-0
            {{ match($tx->category) {
                'needs'   => 'bg-green-50',
                'wants'   => 'bg-blue-50',
                'savings' => 'bg-amber-50',
                default   => 'bg-gray-50'
            } }}">{{ $tx->icon }}</div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold truncate">{{ $tx->description }}</div>
            <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $tx->date->format('d M Y') }}</div>
        </div>
        <span class="text-xs px-2 py-1 rounded font-semibold
            {{ match($tx->category) {
                'needs'   => 'bg-green-50 text-green-700',
                'wants'   => 'bg-blue-50 text-blue-700',
                'savings' => 'bg-amber-50 text-amber-700',
                default   => 'bg-gray-100 text-gray-600'
            } }}">
            {{ $tx->category_label }}
        </span>
        <div class="text-sm font-bold font-mono {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-500' }}">
            {{ $tx->type === 'income' ? '+' : '-' }}${{ number_format($tx->amount, 0) }}
        </div>
    </div>
    @empty
        <div class="px-5 py-8 text-center text-sm text-gray-400">No hay transacciones este mes.</div>
    @endforelse
</div>

{{-- ── Add Transaction Modal ── --}}
@include('components.transaction-modal')

@endsection

@push('scripts')
<script>
const trend  = @json($trend);
const summary = @json($summary);

// Trend chart
new Chart(document.getElementById('ch-trend'), {
    type: 'bar',
    data: {
        labels: trend.map(t => t.label),
        datasets: [
            { label: 'Necesidades', data: trend.map(t => t.needs),   backgroundColor: '#1a6b4a99', borderRadius: 5 },
            { label: 'Deseos',      data: trend.map(t => t.wants),   backgroundColor: '#1a4a8b99', borderRadius: 5 },
            { label: 'Ahorro',      data: trend.map(t => t.savings), backgroundColor: '#8b4a1a99', borderRadius: 5 },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { font: { family: 'JetBrains Mono', size: 11 } } } },
        scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { color: '#f3f4f6' } } }
    }
});

// Donut chart
new Chart(document.getElementById('ch-donut'), {
    type: 'doughnut',
    data: {
        labels: ['Necesidades', 'Deseos', 'Ahorro'],
        datasets: [{ data: [summary.spent.needs, summary.spent.wants, summary.spent.savings],
            backgroundColor: ['#1a6b4a', '#1a4a8b', '#8b4a1a'], borderWidth: 0, hoverOffset: 6 }]
    },
    options: { responsive: true, cutout: '70%',
        plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 11 } } } } }
});
</script>
@endpush
