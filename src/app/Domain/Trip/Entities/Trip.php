<?php

namespace App\Domain\Trip\Entities;

class Trip
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $title,
        public ?string $description,
        public ?string $startDate,
        public ?string $endDate,
        public array $tripSpots = [],
        public array $photos = [],
        public array $tags = [],
        public array $user = [],
        public array $comments = [],
        public array $likes = [],
    ) {}
}
