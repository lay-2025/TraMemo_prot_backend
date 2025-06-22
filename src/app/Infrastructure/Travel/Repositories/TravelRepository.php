<?php

namespace App\Infrastructure\Travel\Repositories;

use App\Domain\Travel\Entities\Travel as TravelEntity;
use App\Domain\Travel\Repositories\TravelRepositoryInterface;
use App\Infrastructure\Travel\Models\Travel;

class TravelRepository implements TravelRepositoryInterface
{
    public function findById(int $id): ?TravelEntity
    {
        $travel = Travel::with([
            'user',
            'travelSpots',
            'photos',
            'tags',
            'comments.user',
            'likes',
        ])->find($id);

        if (!$travel) return null;

        return new TravelEntity(
            $travel->id,
            $travel->user_id,
            $travel->title,
            $travel->description,
            $travel->start_date,
            $travel->end_date,
            $travel->travelSpots->toArray(),
            $travel->photos->toArray(),
            $travel->tags->toArray(),
            $travel->user->toArray(),
            $travel->comments->toArray(),
            $travel->likes->toArray()
        );
    }
}
