<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class BudgetService
{
    /**
     * Calculate 50/30/20 limits from a given income.
     */
    public function calculate(float $income): array
    {
        return [
            'needs'   => round($income * 0.50, 2),
            'wants'   => round($income * 0.30, 2),
            'savings' => round($income * 0.20, 2),
        ];
    }

    /**
     * Get or create the monthly budget for a user.
     */
    public function getOrCreateBudget(User $user, string $period = null): Budget
    {
        $period ??= now()->format('Y-m');
        return Budget::fromIncome($user->id, (float) $user->monthly_income, $period);
    }

    /**
     * Get spending totals by category for a given month.
     */
    public function getMonthlySpending(User $user, string $period = null): array
    {
        $period ??= now()->format('Y-m');

        $rows = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->forMonth($period)
            ->groupBy('category')
            ->selectRaw('category, SUM(amount) as total')
            ->pluck('total', 'category')
            ->toArray();

        return [
            'needs'   => (float) ($rows['needs']   ?? 0),
            'wants'   => (float) ($rows['wants']   ?? 0),
            'savings' => (float) ($rows['savings'] ?? 0),
        ];
    }

    /**
     * Get monthly income total from transactions.
     */
    public function getMonthlyIncome(User $user, string $period = null): float
    {
        $period ??= now()->format('Y-m');

        return (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->forMonth($period)
            ->sum('amount');
    }

    /**
     * Full dashboard summary for a user in a given month.
     */
    public function getDashboardSummary(User $user, string $period = null): array
    {
        $period  ??= now()->format('Y-m');
        $budget   = $this->getOrCreateBudget($user, $period);
        $spent    = $this->getMonthlySpending($user, $period);
        $income   = $this->getMonthlyIncome($user, $period) ?: (float) $user->monthly_income;
        $limits   = $this->calculate($income);

        $totalSpent = array_sum($spent);
        $balance    = $income - $totalSpent;

        return [
            'period'      => $period,
            'income'      => $income,
            'balance'     => $balance,
            'total_spent' => $totalSpent,
            'budget'      => $budget,
            'limits'      => $limits,
            'spent'       => $spent,
            'progress'    => [
                'needs'   => $limits['needs']   > 0 ? round($spent['needs']   / $limits['needs']   * 100, 1) : 0,
                'wants'   => $limits['wants']   > 0 ? round($spent['wants']   / $limits['wants']   * 100, 1) : 0,
                'savings' => $limits['savings'] > 0 ? round($spent['savings'] / $limits['savings'] * 100, 1) : 0,
            ],
            'alerts' => [
                'needs'   => $spent['needs']   > $limits['needs'],
                'wants'   => $spent['wants']   > $limits['wants'],
                'savings' => $spent['savings'] < $limits['savings'],
            ],
        ];
    }

    /**
     * 6-month spending trend by category.
     */
    public function getSpendingTrend(User $user, int $months = 6): Collection
    {
        return collect(range($months - 1, 0))->map(function ($i) use ($user) {
            $period = now()->subMonths($i)->format('Y-m');
            $spent  = $this->getMonthlySpending($user, $period);
            $income = $this->getMonthlyIncome($user, $period);

            return [
                'period'  => $period,
                'label'   => now()->subMonths($i)->translatedFormat('M Y'),
                'needs'   => $spent['needs'],
                'wants'   => $spent['wants'],
                'savings' => $spent['savings'],
                'income'  => $income,
                'total'   => array_sum($spent),
            ];
        });
    }

    /**
     * Admin: aggregated stats across all active consumers.
     */
    public function getAdminStats(): array
    {
        $users         = User::consumers()->active()->get();
        $totalIncome   = $users->sum('monthly_income');
        $activeCount   = $users->count();

        return [
            'total_clients'  => User::consumers()->count(),
            'active_clients' => $activeCount,
            'total_volume'   => $totalIncome,
            'avg_income'     => $activeCount > 0 ? round($totalIncome / $activeCount, 2) : 0,
            'clients'        => $users,
        ];
    }
}
