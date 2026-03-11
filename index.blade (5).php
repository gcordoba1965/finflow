{{-- resources/views/admin/clients/show.blade.php --}}
@extends('layouts.app')
@section('page-title', $user->name . ' — Detalle de cliente')

@section('topbar-actions')
    <form method="POST" action="{{ route('admin.clients.toggle-status', $user) }}" class="inline">
        @csrf @method('PATCH')
        <button type="submit"
            class="px-4 py-2 text-sm font-bold rounded-lg border transition-colors
                {{ $user->is_active
                    ? 'border-red-200 text-red-600 hover:bg-red-50'
                    : 'border-green-200 text-green-700 hover:bg-green-50' }}">
            {{ $user->is_active ? '⛔ Desactivar' : '✅ Activar' }}
        </button>
    </form>
@endsection

@section('content')

{{-- Back link --}}
<a href="{{ route('admin.clients.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 mb-5">
    ← Volver a clientes
</a>

{{-- ── Client header ── --}}
<div class="bg-white border border-gray-200 rounded-xl p-6 mb-5">
    <div class="flex items-center gap-5 mb-5">
        <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-black text-white"
             style="background: hsl({{ crc32($user->email) % 360 }}, 50%, 35%)">
            {{ strtoupper(substr($user->name, 0, 1) . substr(strrchr($user->name, ' '), 1, 1)) }}
        </div>
        <div>
            <h2 class="text-xl font-black">{{ $user->name }}</h2>
            <div class="flex items-center gap-3 mt-1">
                <span class="text-sm text-gray-500 font-mono">{{ $user->email }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                    {{ $user->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                    {{ $user->is_active ? '✅ Activo' : '⛔ Inactivo' }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                    MFA {{ $user->hasMfaEnabled() ? '🔐 Activo' : '⚠️ Sin configurar' }}
                </span>
            </div>
        </div>
        <div class="ml-auto text-right">
            <div class="text-xs text-gray-400 font-mono">Miembro desde</div>
            <div class="text-sm font-bold">{{ $user->created_at->format('d M Y') }}</div>
        </div>
    </div>

    {{-- Budget stats --}}
    <div class="grid grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Ingreso mensual',  'val' => $summary['income'],         'color' => 'text-green-700', 'border' => 'border-t-green-600'],
            ['label' => '50% Necesidades',  'val' => $summary['limits']['needs'],   'color' => 'text-green-800', 'border' => 'border-t-green-400'],
            ['label' => '30% Deseos',       'val' => $summary['limits']['wants'],   'color' => 'text-blue-700',  'border' => 'border-t-blue-500'],
            ['label' => '20% Ahorro',       'val' => $summary['limits']['savings'], 'color' => 'text-amber-700', 'border' => 'border-t-amber-500'],
        ] as $s)
        <div class="bg-gray-50 rounded-xl p-4 border-t-4 {{ $s['border'] }}">
            <div class="text-xs text-gray-400 font-mono uppercase tracking-widest mb-1">{{ $s['label'] }}</div>
            <div class="text-2xl font-black {{ $s['color'] }}">
                ${{ number_format($s['val'], 0) }}
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Budget progress ── --}}
<div class="bg-white border border-gray-200 rounded-xl p-6 mb-5">
    <h3 class="text-sm font-bold mb-4">Progreso del presupuesto — {{ now()->translatedFormat('F Y') }}</h3>
    @foreach([
        ['key' => 'needs',   'label' => '🏠 Necesidades', 'color' => 'bg-green-600'],
        ['key' => 'wants',   'label' => '🎉 Deseos',       'color' => 'bg-blue-600'],
        ['key' => 'savings', 'label' => '💰 Ahorro',       'color' => 'bg-amber-600'],
    ] as $cat)
    @php
        $sp  = $summary['spent'][$cat['key']];
        $lim = $summary['limits'][$cat['key']];
        $pct = $lim > 0 ? min(round($sp / $lim * 100, 1), 100) : 0;
        $ov  = $sp > $lim;
    @endphp
    <div class="mb-4">
        <div class="flex justify-between text-xs mb-1.5">
            <span class="font-semibold">{{ $cat['label'] }}</span>
            <span class="font-mono {{ $ov ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                ${{ number_format($sp, 0) }} / ${{ number_format($lim, 0) }} ({{ $pct }}%)
            </span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full {{ $ov ? 'bg-red-500' : $cat['color'] }}"
                 style="width: {{ $pct }}%"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Charts ── --}}
<div class="grid grid-cols-2 gap-4 mb-5">
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-xs font-mono text-gray-400 uppercase tracking-widest mb-4">Evolución ingresos (6m)</h3>
        <canvas id="ch-client-line" height="200"></canvas>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-xs font-mono text-gray-400 uppercase tracking-widest mb-4">Distribución actual</h3>
        <canvas id="ch-client-donut" height="200"></canvas>
    </div>
</div>

{{-- ── Recent transactions ── --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-gray-100 text-sm font-bold">
        Transacciones recientes
    </div>
    @forelse($recent as $tx)
    <div class="flex items-center gap-4 px-5 py-3 border-b border-gray-50 last:border-0 text-sm">
        <span class="text-base">{{ $tx->icon }}</span>
        <div class="flex-1">{{ $tx->description }}</div>
        <div class="text-xs text-gray-400 font-mono">{{ $tx->date->format('d M') }}</div>
        <span class="text-xs px-2 py-0.5 rounded font-semibold
            {{ match($tx->category) { 'needs' => 'bg-green-50 text-green-700', 'wants' => 'bg-blue-50 text-blue-700', 'savings' => 'bg-amber-50 text-amber-700', default => 'bg-gray-100 text-gray-500' } }}">
            {{ $tx->category_label }}
        </span>
        <div class="font-mono font-bold {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-500' }}">
            {{ $tx->type === 'income' ? '+' : '-' }}${{ number_format($tx->amount, 0) }}
        </div>
    </div>
    @empty
        <div class="px-5 py-8 text-center text-sm text-gray-400">Sin transacciones.</div>
    @endforelse
</div>

{{-- ── Audit logs ── --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 text-sm font-bold">Registros de auditoría</div>
    <table class="w-full text-xs">
        <thead>
            <tr class="text-gray-400 font-mono uppercase border-b border-gray-100">
                <th class="px-5 py-3 text-left">Fecha</th>
                <th class="px-5 py-3 text-left">Acción</th>
                <th class="px-5 py-3 text-left">IP</th>
                <th class="px-5 py-3 text-left">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditLogs as $log)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 font-mono text-gray-400">{{ $log->created_at->format('d/m H:i') }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded font-bold font-mono
                        {{ $log->success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                        {{ $log->action }}
                    </span>
                </td>
                <td class="px-5 py-3 font-mono text-gray-400">{{ $log->ip_address }}</td>
                <td class="px-5 py-3">{{ $log->success ? '✅' : '⚠️' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
const trend = @json($trend);
const summary = @json($summary);

new Chart(document.getElementById('ch-client-line'), {
    type: 'line',
    data: {
        labels: trend.map(t => t.label),
        datasets: [{
            label: 'Gasto total',
            data: trend.map(t => t.total),
            borderColor: '#1a1714',
            backgroundColor: 'rgba(26,23,20,0.06)',
            borderWidth: 2, tension: 0.4, fill: true, pointRadius: 4
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false } }, y: { grid: { color: '#f3f4f6' } } } }
});

new Chart(document.getElementById('ch-client-donut'), {
    type: 'doughnut',
    data: {
        labels: ['Necesidades', 'Deseos', 'Ahorro'],
        datasets: [{ data: [summary.spent.needs, summary.spent.wants, summary.spent.savings],
            backgroundColor: ['#1a6b4a', '#1a4a8b', '#8b4a1a'], borderWidth: 0 }]
    },
    options: { cutout: '68%', plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 11 } } } } }
});
</script>
@endpush
