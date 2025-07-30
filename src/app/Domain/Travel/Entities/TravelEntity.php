<?php

namespace App\Domain\Travel\Entities;

class TravelEntity
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $title,
        public ?string $description,
        public ?string $startDate,
        public ?string $endDate,
        public int $visibility,
        public int $locationCategory,
        public ?int $prefecture,
        public ?int $country,
        public array $travelSpots = [],
        public array $photos = [],
        public array $tags = [],
        public array $user = [],
        public array $comments = [],
        public array $likes = [],
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}
