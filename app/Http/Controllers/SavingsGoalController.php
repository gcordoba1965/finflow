<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        $goals = $request->user()->savingsGoals()->orderBy('deadline')->get();
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

        return redirect()->route('goals.index')->with('success', 'Meta de ahorro creada.');
    }

    public function addFunds(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $savingsGoal->increment('saved_amount', $request->amount);
        if ($savingsGoal->saved_amount >= $savingsGoal->target_amount) {
            $savingsGoal->update(['is_completed' => true]);
        }

        return redirect()->route('goals.index')->with('success', 'Fondos agregados.');
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        $this->authorize('delete', $savingsGoal);
        $savingsGoal->delete();
        return redirect()->route('goals.index')->with('success', 'Meta eliminada.');
    }
}
