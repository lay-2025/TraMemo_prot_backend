<?php

namespace App\Infrastructure\Tag\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Infrastructure\Trip\Models\Trip;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'name',
    ];

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_tag');
    }
}
