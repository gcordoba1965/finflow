<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'ip_address', 'user_agent', 'metadata', 'success',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'success'    => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $action,
        ?int $userId = null,
        array $metadata = [],
        bool $success = true
    ): void {
        static::create([
            'user_id'    => $userId,
            'action'     => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata'   => $metadata,
            'success'    => $success,
            'created_at' => now(),
        ]);
    }
}
