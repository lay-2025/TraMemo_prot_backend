<?php

namespace App\Application\Travel\UseCases;

use App\Domain\Travel\Repositories\TravelRepositoryInterface;

class GetTravelDetailUseCase
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository
    ) {}

    public function handle(int $travelId)
    {
        return $this->travelRepository->findById($travelId);
    }
}
