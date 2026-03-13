<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Notifications\BudgetExceededNotification;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct(private BudgetService $budget) {}

    // ── List ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user   = $request->user();
        $period = $request->query('period', now()->format('Y-m'));
        $cat    = $request->query('category');

        $query = $user->transactions()->forMonth($period);

        if ($cat) {
            $query->forCategory($cat);
        }

        $transactions = $query->paginate(20);
        $summary      = $this->budget->getDashboardSummary($user, $period);

        return view('transactions.index', compact('transactions', 'summary', 'period', 'cat'));
    }

    // ── Create form ───────────────────────────────────────────
    public function create()
    {
        return view('transactions.create');
    }

    // ── Store ─────────────────────────────────────────────────
    public function store(StoreTransactionRequest $request)
    {
        $user = $request->user();

        $transaction = DB::transaction(function () use ($user, $request) {
            $tx = $user->transactions()->create($request->validated());

            AuditLog::record('TX_CREATE', $user->id, [
                'transaction_id' => $tx->id,
                'amount'         => $tx->amount,
                'category'       => $tx->category,
            ]);

            return $tx;
        });

        // Check budget alert for expenses
        if ($transaction->type === 'expense') {
            $this->checkBudgetAlert($user, $transaction->category);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Movimiento registrado correctamente.');
    }

    // ── Edit form ─────────────────────────────────────────────
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        return view('transactions.edit', compact('transaction'));
    }

    // ── Update ────────────────────────────────────────────────
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->validated());

        AuditLog::record('TX_UPDATE', $request->user()->id, [
            'transaction_id' => $transaction->id,
        ]);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Movimiento actualizado.');
    }

    // ── Delete ────────────────────────────────────────────────
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $user = request()->user();

        $transaction->delete();

        AuditLog::record('TX_DELETE', $user->id, [
            'transaction_id' => $transaction->id,
        ]);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Movimiento eliminado.');
    }

    // ── CSV Import ────────────────────────────────────────────
    public function importCsv(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $user  = $request->user();
        $file  = $request->file('file');
        $rows  = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($rows);  // Remove header row

        $imported = 0;
        foreach ($rows as $row) {
            $data = array_combine($header, $row);
            if (empty($data['amount']) || empty($data['description'])) continue;

            $user->transactions()->create([
                'type'        => $data['type'] ?? 'expense',
                'category'    => $data['category'] ?? 'needs',
                'description' => $data['description'],
                'amount'      => (float) $data['amount'],
                'date'        => $data['date'] ?? now()->toDateString(),
                'icon'        => '📄',
                'reference'   => 'csv-import',
            ]);
            $imported++;
        }

        AuditLog::record('TX_CSV_IMPORT', $user->id, ['count' => $imported]);

        return redirect()
            ->route('transactions.index')
            ->with('success', "{$imported} transacciones importadas desde CSV.");
    }

    // ── Private helpers ───────────────────────────────────────
    private function checkBudgetAlert(object $user, string $category): void
    {
        $summary = $this->budget->getDashboardSummary($user);

        if ($summary['alerts'][$category] ?? false) {
            $user->notify(new BudgetExceededNotification(
                $category,
                $summary['limits'][$category],
                $summary['spent'][$category]
            ));
        }
    }
}
