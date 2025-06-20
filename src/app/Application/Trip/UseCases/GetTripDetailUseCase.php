<?php

namespace App\Application\Trip\UseCases;

use App\Domain\Trip\Repositories\TripRepositoryInterface;

class GetTripDetailUseCase
{
    public function __construct(
        private TripRepositoryInterface $tripRepository
    ) {}

    public function handle(int $tripId)
    {
        return $this->tripRepository->findById($tripId);
    }
}
