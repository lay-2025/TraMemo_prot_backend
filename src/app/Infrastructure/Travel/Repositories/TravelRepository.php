<?php

namespace App\Infrastructure\Travel\Repositories;

use App\Domain\Travel\Entities\TravelEntity;
use App\Domain\Travel\Repositories\TravelRepositoryInterface;
use App\Infrastructure\Travel\Models\Travel;
use App\Infrastructure\Travel\Models\TravelSpot;

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
            $travel->visibility,
            $travel->location_category,
            $travel->prefecture,
            $travel->country,
            $travel->travelSpots->toArray(),
            $travel->photos->toArray(),
            $travel->tags->toArray(),
            $travel->user->toArray(),
            $travel->comments->toArray(),
            $travel->likes->toArray()
        );
    }

    /**
     * Create a new travel with associated spots.
     *
     * @param int $userId
     * @param array $data
     * @return Travel $travel
     */
    public function createWithSpots(int $userId, array $data)
    {
        $travel = Travel::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'prefecture' => $data['prefecture'] ?? null,
            'visibility' => $data['visibility'] ?? 'public',
        ]);

        foreach ($data['locations'] ?? [] as $i => $loc) {
            TravelSpot::create([
                'travel_id' => $travel->id,
                'name' => $loc['name'],
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'memo' => $loc['description'] ?? null,
                'day_number' => $loc['dayNumber'] ?? null,
                'visit_date' => $loc['visitDate'] ?? null,
                'visit_time' => $loc['visitTime'] ?? null,
                'order_index' => $loc['order'],
            ]);
        }

        return $travel;
    }
}
