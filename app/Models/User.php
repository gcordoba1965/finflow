<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        TwoFactorAuthenticatable,
        Notifiable,
        SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'monthly_income',
        'currency',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'two_factor_confirmed_at'    => 'datetime',
            'two_factor_recovery_codes'  => 'encrypted:array',
            'two_factor_secret'          => 'encrypted',
            'monthly_income'             => 'decimal:2',
            'is_active'                  => 'boolean',
            'password'                   => 'hashed',
        ];
    }

    // ── Scopes ──────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConsumers($query)
    {
        return $query->where('role', 'user');
    }

    // ── Helpers ──────────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasMfaEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    // ── Relationships ─────────────────────────────────────────────
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest('date');
    }

    public function incomeSources(): HasMany
    {
        return $this->hasMany(IncomeSource::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class)->latest();
    }
}
