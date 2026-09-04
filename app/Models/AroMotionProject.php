<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AroMotionProject extends Model
{
    protected $fillable = [
        'user_id',
        'project_uuid',
        'name',
        'duration_ms',
        'size_bytes',
        'status',
        'app_version',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'size_bytes' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
