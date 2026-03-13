<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'icon', 'amount', 'frequency', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'    => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getMonthlyAmountAttribute(): float
    {
        return match($this->frequency) {
            'weekly'   => $this->amount * 4.33,
            'biweekly' => $this->amount * 2.17,
            'annual'   => $this->amount / 12,
            default    => $this->amount,
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
