<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Travel\Traits\TravelResourceTrait;

class TravelListResource extends JsonResource
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
                'id'     => $this->user['id'] ?? null,
                'name'   => $this->user['name'] ?? '',
                'avatar' => $this->user['avatar_url'] ?? '',
            ],
            'likes'        => collect($this->likes)->count(),
            'commentCount' => collect($this->comments)->count(),
            'tags'         => collect($this->tags)->pluck('name')->all(),
            'locationCategory' => $this->locationCategory,
            'visibility'   => $this->visibility,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }
}
