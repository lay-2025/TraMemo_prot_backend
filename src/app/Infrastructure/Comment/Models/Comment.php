<?php

namespace App\Infrastructure\Comment\Models;

use App\Infrastructure\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Trip\Models\Trip;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'trip_id',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
