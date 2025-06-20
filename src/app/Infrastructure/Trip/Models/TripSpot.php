<?php

namespace App\Infrastructure\Trip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripSpot extends Model
{
    protected $fillable = [
        'trip_id',
        'day_number',
        'visit_date',
        'visit_time',
        'name',
        'latitude',
        'longitude',
        'order_index',
        'memo',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
