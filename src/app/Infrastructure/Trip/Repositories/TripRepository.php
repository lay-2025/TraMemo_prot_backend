<?php

namespace App\Infrastructure\Trip\Repositories;

use App\Domain\Trip\Entities\Trip as TripEntity;
use App\Domain\Trip\Repositories\TripRepositoryInterface;
use App\Infrastructure\Trip\Models\Trip;

class TripRepository implements TripRepositoryInterface
{
    public function findById(int $id): ?TripEntity
    {
        $trip = Trip::with([
            'user',
            'tripSpots',
            'photos',
            'tags',
            'comments.user',
            'likes',
        ])->find($id);

        if (!$trip) return null;

        return new TripEntity(
            $trip->id,
            $trip->user_id,
            $trip->title,
            $trip->description,
            $trip->start_date,
            $trip->end_date,
            $trip->tripSpots->toArray(),
            $trip->photos->toArray(),
            $trip->tags->toArray(),
            $trip->user->toArray(),
            $trip->comments->toArray(),
            $trip->likes->toArray()
        );
    }
}
