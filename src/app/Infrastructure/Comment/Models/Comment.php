<?php

namespace App\Infrastructure\Comment\Models;

use App\Infrastructure\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Travel\Models\Travel;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'travel_id',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }
}
