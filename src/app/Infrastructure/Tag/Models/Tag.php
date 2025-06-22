<?php

namespace App\Infrastructure\Tag\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Infrastructure\Travel\Models\Travel;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'name',
    ];

    public function travels(): BelongsToMany
    {
        return $this->belongsToMany(Travel::class, 'travel_tag');
    }
}
