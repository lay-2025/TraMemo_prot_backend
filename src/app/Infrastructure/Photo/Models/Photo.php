<?php

namespace App\Infrastructure\Photo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Travel\Models\Travel;
use App\Infrastructure\Travel\Models\TravelSpot;

class Photo extends Model
{
    protected $fillable = [
        'travel_id',
        'travel_spot_id',
        'url',
        'thumbnail_url',
        'caption',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function travelSpot(): BelongsTo
    {
        return $this->belongsTo(TravelSpot::class);
    }
}
