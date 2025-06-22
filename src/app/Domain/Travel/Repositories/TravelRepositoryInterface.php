<?php

namespace App\Domain\Travel\Repositories;

use App\Domain\Travel\Entities\Travel;

interface TravelRepositoryInterface
{
    public function findById(int $id): ?Travel;
}
