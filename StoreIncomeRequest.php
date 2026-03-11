<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'icon', 'target_amount',
        'saved_amount', 'deadline', 'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'saved_amount'  => 'decimal:2',
            'deadline'      => 'date',
            'is_completed'  => 'boolean',
        ];
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->target_amount <= 0) return 0;
        return min(round(($this->saved_amount / $this->target_amount) * 100, 1), 100);
    }

    public function getRemainingAttribute(): float
    {
        return max($this->target_amount - $this->saved_amount, 0);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
