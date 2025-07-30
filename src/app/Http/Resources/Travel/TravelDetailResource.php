<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Travel\Traits\TravelResourceTrait;

class TravelDetailResource extends JsonResource
{
    use TravelResourceTrait;

    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'location'    => $this->getLocationText(),
            'date'        => $this->startDate . ' - ' . $this->endDate,
            'duration'    => $this->getDurationText($this->startDate, $this->endDate),
            'images'      => collect($this->photos)->pluck('file_path')->all(),
            'description' => $this->description,
            'user'        => [
                'name'   => $this->user['name'] ?? '',
                'avatar' => $this->user['avatar_url'] ?? '',
                'bio'    => $this->user['bio'] ?? '',
            ],
            'likes'        => collect($this->likes)->count(),
            'commentCount' => collect($this->comments)->count(),
            'tags'         => collect($this->tags)->pluck('name')->all(),
            'locations'    => collect($this->travelSpots)->map(function ($spot) {
                return [
                    'name'        => $spot['name'] ?? '',
                    'lat'         => $spot['latitude'] ?? '',
                    'lng'         => $spot['longitude'] ?? '',
                    'description' => $spot['memo'] ?? '',
                    'orderIndex'  => $spot['order_index'] ?? 0,
                ];
            })->all(),
            'itinerary' => $this->formatItinerary(collect($this->travelSpots)),
            'comments'  => collect($this->comments)->map(function ($comment) {
                return [
                    'id'      => $comment['id'] ?? null,
                    'user'    => [
                        'name'   => $comment['user']['name'] ?? '',
                        'avatar' => $comment['user']['avatar_url'] ?? '',
                    ],
                    'content' => $comment['content'] ?? '',
                    'date'    => isset($comment['created_at']) && $comment['created_at']
                        ? \Carbon\Carbon::parse($comment['created_at'])->format('Y年m月d日')
                        : '',
                ];
            })->all(),
        ];
    }
}
