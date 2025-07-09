<?php

namespace App\Domain\Travel\Repositories;

use App\Domain\Travel\Entities\TravelEntity;

interface TravelRepositoryInterface
{
    public function findById(int $id): ?TravelEntity;
    public function createWithSpots(int $userId, array $data);
}
