<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Application\Travel\UseCases\CreateTravelUseCase;
use App\Application\Travel\UseCases\GetTravelDetailUseCase;
use App\Application\User\Services\AuthenticatedUserService;
use App\Http\Resources\TravelResource;

class TravelController extends Controller
{

    private AuthenticatedUserService $authenticatedUserService;

    public function __construct(AuthenticatedUserService $authenticatedUserService)
    {
        $this->authenticatedUserService = $authenticatedUserService;
    }

    public function show($id, GetTravelDetailUseCase $useCase)
    {
        $travel = $useCase->handle((int)$id);

        if (!$travel) {
            return response()->json([
                'success' => false,
                'message' => 'Not Found'
            ], 404);
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
            'visibility' => 'required|integer',
            'location_category' => 'nullable|integer',
            'prefecture' => 'nullable|integer',
            'country' => 'nullable|integer',
            'tags' => 'array',
            'tags.*' => 'string',
            'locations' => 'array',
            'locations.*.order' => 'required|integer',
            'locations.*.name' => 'required|string|max:255',
            'locations.*.lat' => 'nullable|numeric',
            'locations.*.lng' => 'nullable|numeric',
            'locations.*.description' => 'nullable|string',
            'locations.*.visitDate' => 'required|date',
            'locations.*.visitTime' => 'nullable|string',
            'images' => 'array',
            'images.*' => 'string',
        ]);

        // ユーザーIDを取得
        $user = $this->authenticatedUserService->getUserFromRequest($request);
        if (!$user) {
            // ユーザ認証はされているが、ユーザ情報がDBに存在しない場合
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        $userId = $user->id;

        try {
            $travel = $useCase->handle($userId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Travel created successfully',
                'data' => $travel->load(['travelSpots', 'photos', 'tags']),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Travel creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
