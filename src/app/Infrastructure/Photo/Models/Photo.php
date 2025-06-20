<?php

namespace App\Infrastructure\Photo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Trip\Models\Trip;
use App\Infrastructure\Trip\Models\TripSpot;

class Photo extends Model
{
    protected $fillable = [
        'trip_id',
        'trip_spot_id',
        'url',
        'thumbnail_url',
        'caption',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripSpot(): BelongsTo
    {
        return $this->belongsTo(TripSpot::class);
    }
}
