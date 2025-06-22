<?php

namespace App\Domain\Travel\Entities;

class Travel
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $title,
        public ?string $description,
        public ?string $startDate,
        public ?string $endDate,
        public array $travelSpots = [],
        public array $photos = [],
        public array $tags = [],
        public array $user = [],
        public array $comments = [],
        public array $likes = [],
    ) {}
}
