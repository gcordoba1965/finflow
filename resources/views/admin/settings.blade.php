@extends('layouts.app')
@section('page-title', 'Configuración del Sistema')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4">
            <div class="text-sm font-bold mb-4">Autenticación</div>
            @foreach([
                ['key' => 'mfa_required',       'label' => 'MFA obligatorio para todos los usuarios'],
                ['key' => 'email_verification',  'label' => 'Verificación de email al registrarse'],
                ['key' => 'login_lockout',        'label' => 'Bloqueo tras 5 intentos fallidos'],
                ['key' => 'sms_mfa',             'label' => 'Permitir SMS como método MFA'],
            ] as $s)
            <div class="flex justify-between items-center py-3 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-700">{{ $s['label'] }}</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="{{ $s['key'] }}" value="1" class="sr-only peer"
                           {{ ($settings[$s['key']] ?? false) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-600
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                                peer-checked:after:translate-x-full"></div>
                </label>
            </div>
            @endforeach
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4">
            <div class="text-sm font-bold mb-4">General</div>
            <div class="mb-4">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Moneda por defecto</label>
                <select name="default_currency" class="border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                    <option value="USD" {{ ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="MXN" {{ ($settings['default_currency'] ?? '') === 'MXN' ? 'selected' : '' }}>MXN</option>
                    <option value="EUR" {{ ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Alerta de presupuesto al</label>
                <select name="budget_alert_pct" class="border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                    <option value="80" {{ ($settings['budget_alert_pct'] ?? 80) == 80 ? 'selected' : '' }}>80%</option>
                    <option value="90" {{ ($settings['budget_alert_pct'] ?? 80) == 90 ? 'selected' : '' }}>90%</option>
                    <option value="100" {{ ($settings['budget_alert_pct'] ?? 80) == 100 ? 'selected' : '' }}>100%</option>
                </select>
            </div>
        </div>
        <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-lg hover:bg-gray-700">
            Guardar cambios
        </button>
    </form>
</div>
@endsection
