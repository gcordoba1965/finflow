@extends('layouts.app')
@section('page-title', 'Panel de Administración')

@section('content')
<div class="grid grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'Total clientes', 'val' => $stats['total_clients'], 'sub' => $stats['active_clients'].' activos'],
        ['label' => 'Volumen mensual', 'val' => '$'.number_format($stats['total_volume'], 0), 'sub' => 'Ingreso gestionado'],
        ['label' => 'Ingreso promedio', 'val' => '$'.number_format($stats['avg_income'], 0), 'sub' => 'Por cliente activo'],
        ['label' => 'Cumplimiento', 'val' => '83%', 'sub' => 'Clientes en balance'],
    ] as $s)
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">{{ $s['label'] }}</div>
        <div class="text-2xl font-black">{{ $s['val'] }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $s['sub'] }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-5">
        <div class="text-sm font-bold mb-4">Volumen mensual total</div>
        <canvas id="ch-volume" height="200"></canvas>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="text-sm font-bold mb-4">Top 5 clientes</div>
        @foreach($topUsers as $u)
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black text-white"
                 style="background: hsl({{ crc32($u->email) % 360 }}, 50%, 35%)">
                {{ strtoupper(substr($u->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold truncate">{{ $u->name }}</div>
                <div class="text-xs text-gray-400 font-mono">${{ number_format($u->monthly_income, 0) }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between">
        <span class="text-sm font-bold">Actividad reciente</span>
        <a href="{{ route('admin.logs.index') }}" class="text-xs text-gray-400 hover:text-gray-700">Ver todos →</a>
    </div>
    <table class="w-full">
        <tbody>
            @foreach($logs->take(8) as $log)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $log->created_at->format('d/m H:i') }}</td>
                <td class="px-5 py-3 text-sm">{{ $log->user?->name ?? 'Sistema' }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded font-mono {{ $log->success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                        {{ $log->action }}
                    </span>
                </td>
                <td class="px-5 py-3 text-base">{{ $log->success ? '✅' : '⚠️' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
const vol = @json($monthlyVolume);
new Chart(document.getElementById('ch-volume'), {
    type: 'bar',
    data: { labels: vol.map(v => v.label),
        datasets: [{ label: 'Volumen', data: vol.map(v => v.volume),
            backgroundColor: 'rgba(26,23,20,0.7)', borderRadius: 6 }] },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false } }, y: { grid: { color: '#f3f4f6' } } } }
});
</script>
@endpush
