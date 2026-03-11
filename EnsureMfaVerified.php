<?php
// routes/web.php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ── Public / Auth routes (handled by Fortify) ────────────────
// /login, /register, /forgot-password, /two-factor-challenge
// are automatically registered by Laravel Fortify

// ── Authenticated consumer routes ────────────────────────────
Route::middleware([
    'auth',
    'verified',
    'ensure.active',       // EnsureAccountActive
    'ensure.mfa',          // EnsureMfaVerified
])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])
        ->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])
        ->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])
        ->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->name('transactions.destroy');
    Route::post('/transactions/import-csv', [TransactionController::class, 'importCsv'])
        ->name('transactions.import');

    // Income sources
    Route::get('/income', [IncomeController::class, 'index'])
        ->name('income.index');
    Route::post('/income', [IncomeController::class, 'store'])
        ->name('income.store');
    Route::put('/income/{incomeSource}', [IncomeController::class, 'update'])
        ->name('income.update');
    Route::patch('/income/{incomeSource}/toggle', [IncomeController::class, 'toggle'])
        ->name('income.toggle');
    Route::delete('/income/{incomeSource}', [IncomeController::class, 'destroy'])
        ->name('income.destroy');

    // Savings goals
    Route::get('/goals', [SavingsGoalController::class, 'index'])
        ->name('goals.index');
    Route::post('/goals', [SavingsGoalController::class, 'store'])
        ->name('goals.store');
    Route::patch('/goals/{savingsGoal}/add-funds', [SavingsGoalController::class, 'addFunds'])
        ->name('goals.add-funds');
    Route::delete('/goals/{savingsGoal}', [SavingsGoalController::class, 'destroy'])
        ->name('goals.destroy');
});

// ── Admin routes ──────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'ensure.mfa', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/clients', [Admin\UserController::class, 'index'])
            ->name('clients.index');
        Route::post('/clients', [Admin\UserController::class, 'store'])
            ->name('clients.store');
        Route::get('/clients/{user}', [Admin\UserController::class, 'show'])
            ->name('clients.show');
        Route::patch('/clients/{user}/toggle-status', [Admin\UserController::class, 'toggleStatus'])
            ->name('clients.toggle-status');
        Route::delete('/clients/{user}', [Admin\UserController::class, 'destroy'])
            ->name('clients.destroy');

        Route::get('/logs', [Admin\LogController::class, 'index'])
            ->name('logs.index');

        Route::get('/settings', [Admin\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::post('/settings', [Admin\SettingsController::class, 'update'])
            ->name('settings.update');
    });
