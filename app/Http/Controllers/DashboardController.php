<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private BudgetService $budget) {}

    public function index(Request $request)
    {
        $user    = $request->user();
        $period  = $request->query('period', now()->format('Y-m'));

        $summary  = $this->budget->getDashboardSummary($user, $period);
        $trend    = $this->budget->getSpendingTrend($user, 6);
        $recent   = $user->transactions()->limit(8)->get();
        $goals    = $user->savingsGoals()->where('is_completed', false)->get();

        return view('dashboard.index', compact('summary', 'trend', 'recent', 'goals', 'period'));
    }
}
