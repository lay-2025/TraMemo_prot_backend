<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Application\Travel\UseCases\GetTravelDetailUseCase;
use App\Http\Resources\TravelResource;

class TravelController extends Controller
{
    public function show($id, GetTravelDetailUseCase $useCase)
    {
        $travel = $useCase->handle((int)$id);

        if (!$travel) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return new TravelResource($travel);
    }
}
