<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(private BudgetService $budget) {}

    // ── List all consumers ───────────────────────────────────
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::consumers()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.clients.index', compact('users', 'search'));
    }

    // ── Show single consumer detail ──────────────────────────
    public function show(User $user)
    {
        $this->ensureConsumer($user);

        $summary   = $this->budget->getDashboardSummary($user);
        $trend     = $this->budget->getSpendingTrend($user, 6);
        $recent    = $user->transactions()->limit(10)->get();
        $sources   = $user->incomeSources()->active()->get();
        $goals     = $user->savingsGoals()->where('is_completed', false)->get();
        $auditLogs = $user->auditLogs()->limit(20)->get();

        return view('admin.clients.show', compact(
            'user', 'summary', 'trend', 'recent', 'sources', 'goals', 'auditLogs'
        ));
    }

    // ── Create consumer account ──────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'monthly_income' => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|size:3',
            'is_active'      => 'boolean',
        ]);

        $tempPassword = Str::password(12);

        $user = User::create([
            ...$data,
            'role'     => 'user',
            'password' => Hash::make($tempPassword),
        ]);

        // Send welcome email with temp password
        $user->notify(new WelcomeNotification($tempPassword));

        AuditLog::record('USER_CREATE', $request->user()->id, [
            'created_user_id' => $user->id,
            'email'           => $user->email,
        ]);

        return redirect()
            ->route('admin.clients.show', $user)
            ->with('success', "Cuenta creada. Email enviado a {$user->email}");
    }

    // ── Toggle active/inactive ───────────────────────────────
    public function toggleStatus(User $user)
    {
        $this->ensureConsumer($user);

        $user->update(['is_active' => ! $user->is_active]);

        $action = $user->is_active ? 'USER_ACTIVATE' : 'USER_DEACTIVATE';
        AuditLog::record($action, request()->user()->id, [
            'affected_user_id' => $user->id,
        ]);

        $status = $user->is_active ? 'activada' : 'desactivada';
        return redirect()
            ->back()
            ->with('success', "Cuenta {$status} correctamente.");
    }

    // ── Delete consumer ──────────────────────────────────────
    public function destroy(User $user)
    {
        $this->ensureConsumer($user);

        AuditLog::record('USER_DELETE', request()->user()->id, [
            'deleted_user_id' => $user->id,
            'email'           => $user->email,
        ]);

        $user->delete(); // SoftDelete

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Cuenta eliminada.');
    }

    private function ensureConsumer(User $user): void
    {
        abort_if($user->isAdmin(), 403, 'Cannot manage admin accounts here.');
    }
}


// ─────────────────────────────────────────────────────────────────
// Admin Dashboard Controller
// ─────────────────────────────────────────────────────────────────

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\BudgetService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private BudgetService $budget) {}

    public function index()
    {
        $stats    = $this->budget->getAdminStats();
        $logs     = AuditLog::with('user')->latest('created_at')->limit(15)->get();
        $topUsers = $stats['clients']->sortByDesc('monthly_income')->take(5);

        // Platform-wide monthly trend (last 6 months)
        $monthlyVolume = collect(range(5, 0))->map(function ($i) {
            $period = now()->subMonths($i)->format('Y-m');
            $label  = now()->subMonths($i)->translatedFormat('M');
            // Sum of all users' income for that period
            $volume = \App\Models\Transaction::where('type', 'income')
                ->whereYear('date', substr($period, 0, 4))
                ->whereMonth('date', substr($period, 5, 2))
                ->sum('amount');

            return ['period' => $period, 'label' => $label, 'volume' => $volume];
        });

        return view('admin.dashboard', compact('stats', 'logs', 'topUsers', 'monthlyVolume'));
    }
}
