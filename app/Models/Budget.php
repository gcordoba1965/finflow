<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'period', 'total_income',
        'needs_limit', 'wants_limit', 'savings_goal',
    ];

    protected function casts(): array
    {
        return [
            'total_income' => 'decimal:2',
            'needs_limit'  => 'decimal:2',
            'wants_limit'  => 'decimal:2',
            'savings_goal' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function fromIncome(int $userId, float $income, string $period): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'period' => $period],
            [
                'total_income' => $income,
                'needs_limit'  => round($income * 0.50, 2),
                'wants_limit'  => round($income * 0.30, 2),
                'savings_goal' => round($income * 0.20, 2),
            ]
        );
    }
}
