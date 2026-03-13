<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'mfa_required'       => config('finflow.mfa_required', true),
            'email_verification' => config('finflow.email_verification', true),
            'login_lockout'      => config('finflow.login_lockout', true),
            'sms_mfa'            => config('finflow.sms_mfa', false),
            'default_currency'   => config('finflow.default_currency', 'USD'),
            'budget_alert_pct'   => config('finflow.budget_alert_pct', 80),
        ];
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        // In a real app, persist to DB settings table or .env
        return redirect()->route('admin.settings.index')
            ->with('success', 'Configuración guardada.');
    }
}
