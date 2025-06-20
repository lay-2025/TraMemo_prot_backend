<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Application\Trip\UseCases\GetTripDetailUseCase;
use App\Http\Resources\TravelResource;

class TripController extends Controller
{
    public function show($id, GetTripDetailUseCase $useCase)
    {
        $trip = $useCase->handle((int)$id);

        if (!$trip) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return new TravelResource($trip);
    }
}
