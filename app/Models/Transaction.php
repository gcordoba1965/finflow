<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'category',
        'sub_category',
        'description',
        'amount',
        'icon',
        'date',
        'notes',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date'   => 'date',
        ];
    }

    // ── Scopes ──────────────────────────────────────────────────
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeIncomes($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeForMonth($query, string $yearMonth)
    {
        [$year, $month] = explode('-', $yearMonth);
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Relationships ─────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────────────────
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'needs'   => '#1a6b4a',
            'wants'   => '#1a4a8b',
            'savings' => '#8b4a1a',
            default   => '#666666',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'needs'   => 'Necesidades',
            'wants'   => 'Deseos',
            'savings' => 'Ahorro',
            'income'  => 'Ingreso',
            default   => 'Otro',
        };
    }
}
