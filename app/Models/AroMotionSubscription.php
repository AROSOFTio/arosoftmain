<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AroMotionSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'started_at',
        'current_period_ends_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
