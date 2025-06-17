<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'name',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_tag');
    }
}
