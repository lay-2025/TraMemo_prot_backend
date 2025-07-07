<?php

namespace App\Infrastructure\Like\Models;

use App\Infrastructure\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Travel\Models\Travel;

class Like extends Model
{
    protected $fillable = [
        'user_id',
        'travel_id',
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
