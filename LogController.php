<?php

namespace App\Providers;

use App\Models\IncomeSource;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Policies\IncomeSourcePolicy;
use App\Policies\SavingsGoalPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Transaction::class,  TransactionPolicy::class);
        Gate::policy(IncomeSource::class, IncomeSourcePolicy::class);
        Gate::policy(SavingsGoal::class,  SavingsGoalPolicy::class);
        Gate::define('admin', fn ($user) => $user->isAdmin());

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(3)->by($request->session()->get('login.id'));
        });
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
