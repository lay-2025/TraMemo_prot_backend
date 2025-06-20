<?php

namespace App\Domain\Trip\Repositories;

use App\Domain\Trip\Entities\Trip;

interface TripRepositoryInterface
{
    public function findById(int $id): ?Trip;
}
