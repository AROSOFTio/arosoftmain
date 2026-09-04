<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AroMotionDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_uuid',
        'device_name',
        'platform',
        'app_version',
        'token_hash',
        'activated_at',
        'last_seen_at',
        'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
