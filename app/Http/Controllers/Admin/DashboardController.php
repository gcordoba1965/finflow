<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\BudgetService;

class DashboardController extends Controller
{
    public function __construct(private BudgetService $budget) {}

    public function index()
    {
        $stats         = $this->budget->getAdminStats();
        $logs          = AuditLog::with('user')->latest('created_at')->limit(15)->get();
        $topUsers      = $stats['clients']->sortByDesc('monthly_income')->take(5);

        $monthlyVolume = collect(range(5, 0))->map(function ($i) {
            $period = now()->subMonths($i)->format('Y-m');
            $volume = \App\Models\Transaction::where('type', 'income')
                ->whereYear('date', substr($period, 0, 4))
                ->whereMonth('date', substr($period, 5, 2))
                ->sum('amount');
            return [
                'period' => $period,
                'label'  => now()->subMonths($i)->translatedFormat('M'),
                'volume' => $volume,
            ];
        });

        return view('admin.dashboard', compact('stats', 'logs', 'topUsers', 'monthlyVolume'));
    }
}
