<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Application\Travel\UseCases\CreateTravelUseCase;
use App\Application\Travel\UseCases\GetTravelDetailUseCase;
use App\Application\Travel\UseCases\GetTravelListUseCase;
use App\Application\User\Services\AuthenticatedUserService;
use App\Http\Resources\Travel\TravelListResource;
use App\Http\Resources\Travel\TravelDetailResource;

class TravelController extends Controller
{

    private AuthenticatedUserService $authenticatedUserService;

    public function __construct(AuthenticatedUserService $authenticatedUserService)
    {
        $this->authenticatedUserService = $authenticatedUserService;
    }

    public function index(Request $request, GetTravelListUseCase $useCase)
    {
        try {
            // クエリパラメータを取得
            $filters = $request->only([
                'query',
                'locationCategory',
                'prefecture',
                'country',
                'visibility',
                'tags',
                'dateFrom',
                'dateTo',
                'minLikes',
                'minComments',
                'sortBy',
                'sortOrder',
                'user_id'
            ]);

            // ページネーションパラメータ
            $page = max(1, (int)($request->get('page', 1)));
            $limit = min(100, max(1, (int)($request->get('limit', 12))));

            // タグパラメータの処理
            if (isset($filters['tags']) && is_string($filters['tags'])) {
                $filters['tags'] = explode(',', $filters['tags']);
            }

            $result = $useCase->handle($filters, $page, $limit);

            // TravelListResourceを使用してレスポンスを整形
            $travelResources = collect($result['data'])->map(function ($travelEntity) {
                return new TravelListResource($travelEntity);
            });

            return response()->json([
                'success' => true,
                'data' => $travelResources,
                'meta' => $result['meta']
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => [
                    'validation' => [$e->getMessage()]
                ]
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
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

        return new TravelDetailResource($travel);
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
            'locationCategory' => 'nullable|integer',
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
        // $user = $this->authenticatedUserService->getUserFromRequest($request);
        // if (!$user) {
        //     // ユーザ認証はされているが、ユーザ情報がDBに存在しない場合
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Unauthorized'
        //     ], 401);
        // }
        // $userId = $user->id;

        // 本番環境でのユーザ認証機能が上手く動作しないため、一時的にユーザID1を設定
        $userId = 1;

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
