<?php

namespace App\Infrastructure\Travel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelSpot extends Model
{
    protected $fillable = [
        'travel_id',
        'day_number',
        'visit_date',
        'visit_time',
        'name',
        'latitude',
        'longitude',
        'order_index',
        'memo',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }
}
