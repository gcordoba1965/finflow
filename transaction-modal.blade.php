@extends('layouts.app')
@section('page-title', 'Registros de Auditoría')

@section('content')
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-mono text-gray-400 uppercase">Timestamp</th>
                <th class="px-5 py-3 text-left text-xs font-mono text-gray-400 uppercase">Usuario</th>
                <th class="px-5 py-3 text-left text-xs font-mono text-gray-400 uppercase">Acción</th>
                <th class="px-5 py-3 text-left text-xs font-mono text-gray-400 uppercase">IP</th>
                <th class="px-5 py-3 text-left text-xs font-mono text-gray-400 uppercase">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-3 text-sm">{{ $log->user?->name ?? 'Sistema' }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded font-mono font-bold
                        {{ $log->success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                        {{ $log->action }}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $log->ip_address }}</td>
                <td class="px-5 py-3 text-base">{{ $log->success ? '✅' : '⚠️' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $logs->links() }}
@endsection
