<?php

namespace App\Infrastructure\Travel\Models;

use App\Infrastructure\User\Models\User;
use App\Infrastructure\Comment\Models\Comment;
use App\Infrastructure\Favorite\Models\Favorite;
use App\Infrastructure\Like\Models\Like;
use App\Infrastructure\Photo\Models\Photo;
use App\Infrastructure\Tag\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Travel extends Model
{
    protected $table = 'travels';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'visibility',
        'location_category',
        'prefecture',
        'country',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function travelSpots(): HasMany
    {
        return $this->hasMany(TravelSpot::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'travel_tag');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
