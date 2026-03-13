<?php

namespace App\Http\Controllers;

use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Models\AuditLog;
use App\Models\IncomeSource;
use App\Services\BudgetService;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(private BudgetService $budget) {}

    public function index(Request $request)
    {
        $user    = $request->user();
        $sources = $user->incomeSources()->get();
        $trend   = $this->budget->getSpendingTrend($user, 6);

        $totalMonthly = $sources->where('is_active', true)
            ->sum('monthly_amount');

        return view('income.index', compact('sources', 'totalMonthly', 'trend'));
    }

    public function store(StoreIncomeRequest $request)
    {
        $user   = $request->user();
        $source = $user->incomeSources()->create($request->validated());

        // Update user's monthly_income aggregate
        $this->recalculateIncome($user);

        AuditLog::record('INCOME_CREATE', $user->id, [
            'income_source_id' => $source->id,
            'name'             => $source->name,
            'amount'           => $source->amount,
        ]);

        return redirect()
            ->route('income.index')
            ->with('success', 'Fuente de ingreso agregada.');
    }

    public function update(UpdateIncomeRequest $request, IncomeSource $incomeSource)
    {
        $this->authorize('update', $incomeSource);

        $incomeSource->update($request->validated());
        $this->recalculateIncome($request->user());

        AuditLog::record('INCOME_UPDATE', $request->user()->id, [
            'income_source_id' => $incomeSource->id,
        ]);

        return redirect()
            ->route('income.index')
            ->with('success', 'Ingreso actualizado.');
    }

    public function toggle(IncomeSource $incomeSource)
    {
        $this->authorize('update', $incomeSource);

        $incomeSource->update(['is_active' => ! $incomeSource->is_active]);
        $this->recalculateIncome(request()->user());

        return redirect()
            ->route('income.index')
            ->with('success', 'Estado actualizado.');
    }

    public function destroy(IncomeSource $incomeSource)
    {
        $this->authorize('delete', $incomeSource);
        $user = request()->user();

        $incomeSource->delete();
        $this->recalculateIncome($user);

        AuditLog::record('INCOME_DELETE', $user->id, [
            'income_source_id' => $incomeSource->id,
        ]);

        return redirect()
            ->route('income.index')
            ->with('success', 'Fuente de ingreso eliminada.');
    }

    private function recalculateIncome(object $user): void
    {
        $total = $user->incomeSources()->active()->get()->sum('monthly_amount');
        $user->update(['monthly_income' => $total]);
    }
}


// ─────────────────────────────────────────────────────────────────
// SavingsGoalController
// ─────────────────────────────────────────────────────────────────

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        $goals = $request->user()
            ->savingsGoals()
            ->orderBy('deadline')
            ->get();

        return view('goals.index', compact('goals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'icon'          => 'nullable|string|max:10',
            'target_amount' => 'required|numeric|min:1',
            'deadline'      => 'nullable|date|after:today',
        ]);

        $user = $request->user();
        $goal = $user->savingsGoals()->create($data);

        AuditLog::record('GOAL_CREATE', $user->id, ['goal_id' => $goal->id]);

        return redirect()
            ->route('goals.index')
            ->with('success', 'Meta de ahorro creada.');
    }

    public function addFunds(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);

        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $savingsGoal->increment('saved_amount', $request->amount);

        if ($savingsGoal->saved_amount >= $savingsGoal->target_amount) {
            $savingsGoal->update(['is_completed' => true]);
        }

        return redirect()
            ->route('goals.index')
            ->with('success', 'Fondos agregados a la meta.');
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        $this->authorize('delete', $savingsGoal);
        $savingsGoal->delete();

        return redirect()
            ->route('goals.index')
            ->with('success', 'Meta eliminada.');
    }
}
