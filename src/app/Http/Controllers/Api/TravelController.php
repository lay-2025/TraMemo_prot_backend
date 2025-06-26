<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Application\Travel\UseCases\CreateTravelUseCase;
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

    public function store(Request $request, CreateTravelUseCase $useCase)
    {
        info('Creating travel with data: ' . json_encode($request->all()));

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'prefecture' => 'nullable|string|max:255',
            'visibility' => 'nullable|string|max:50',
            'tags' => 'array',
            'tags.*' => 'string|max:50',
            'locations' => 'array',
            'locations.*.order' => 'required|numeric',
            'locations.*.name' => 'required|string|max:255',
            'locations.*.lat' => 'required|numeric',
            'locations.*.lng' => 'required|numeric',
            'locations.*.description' => 'nullable|string',
            'locations.*.visitDate' => 'nullable|date',
            'locations.*.visitTime' => 'nullable|string',
            'images' => 'array',
            'images.*' => 'string|max:1024',
        ]);

        $userId = $request->user()->id ?? 1; // 認証していない場合は仮で1

        try {
            $travel = $useCase->handle($userId, $validated);

            return response()->json([
                'message' => 'Travel created successfully',
                'data' => $travel->load(['travelSpots', 'photos', 'tags']),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Travel creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
