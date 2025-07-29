<?php

namespace App\Infrastructure\Travel\Repositories;

use App\Domain\Travel\Entities\TravelEntity;
use App\Domain\Travel\Repositories\TravelRepositoryInterface;
use App\Infrastructure\Travel\Models\Travel;
use App\Infrastructure\Travel\Models\TravelSpot;
use Illuminate\Support\Facades\DB;

class TravelRepository implements TravelRepositoryInterface
{
    public function findById(int $id): ?TravelEntity
    {
        $travel = Travel::with([
            'user',
            'travelSpots',
            'photos',
            'tags',
            'comments.user',
            'likes',
        ])->find($id);

        if (!$travel) return null;

        return new TravelEntity(
            $travel->id,
            $travel->user_id,
            $travel->title,
            $travel->description,
            $travel->start_date,
            $travel->end_date,
            $travel->visibility,
            $travel->location_category,
            $travel->prefecture,
            $travel->country,
            $travel->travelSpots->toArray(),
            $travel->photos->toArray(),
            $travel->tags->toArray(),
            $travel->user->toArray(),
            $travel->comments->toArray(),
            $travel->likes->toArray(),
            $travel->created_at,
            $travel->updated_at
        );
    }

    /**
     * Create a new travel with associated spots.
     *
     * @param int $userId
     * @param array $data
     * @return Travel $travel
     */
    public function createWithSpots(int $userId, array $data)
    {
        $travel = Travel::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'visibility' => $data['visibility'] ?? 0,
            'location_category' => $data['locationCategory'] ?? 0,
            'prefecture' => $data['prefecture'] ?? null,
            'country' => $data['country'] ?? null,
        ]);

        foreach ($data['locations'] ?? [] as $i => $loc) {
            TravelSpot::create([
                'travel_id' => $travel->id,
                'name' => $loc['name'],
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'memo' => $loc['description'] ?? null,
                'day_number' => $loc['dayNumber'] ?? null,
                'visit_date' => $loc['visitDate'] ?? null,
                'visit_time' => $loc['visitTime'] ?? null,
                'order_index' => $loc['order'],
            ]);
        }

        return $travel;
    }

    /**
     * Get travel list with filters and pagination
     *
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getTravelList(array $filters, int $page = 1, int $limit = 12): array
    {
        $query = Travel::with([
            'user',
            'photos',
            'tags',
            'likes',
            'comments'
        ]);

        // 公開設定でフィルタ（デフォルトは公開のみ）
        $visibility = $filters['visibility'] ?? 1;
        $query->where('visibility', $visibility);

        // キーワード検索
        if (!empty($filters['query'])) {
            $searchQuery = $filters['query'];
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('description', 'like', "%{$searchQuery}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchQuery) {
                        $userQuery->where('name', 'like', "%{$searchQuery}%");
                    })
                    ->orWhereHas('tags', function ($tagQuery) use ($searchQuery) {
                        $tagQuery->where('name', 'like', "%{$searchQuery}%");
                    });
            });
        }

        // 場所カテゴリフィルタ
        if (isset($filters['locationCategory'])) {
            $query->where('location_category', $filters['locationCategory']);
        }

        // 都道府県フィルタ
        if (!empty($filters['prefecture'])) {
            $query->where('prefecture', $filters['prefecture']);
        }

        // 国フィルタ
        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        // タグフィルタ
        if (!empty($filters['tags'])) {
            $tagNames = is_array($filters['tags']) ? $filters['tags'] : explode(',', $filters['tags']);
            $query->whereHas('tags', function ($tagQuery) use ($tagNames) {
                $tagQuery->whereIn('name', $tagNames);
            });
        }

        // 日付フィルタ
        if (!empty($filters['dateFrom'])) {
            $query->where('start_date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('end_date', '<=', $filters['dateTo']);
        }

        // いいね数フィルタ
        if (!empty($filters['minLikes'])) {
            $query->withCount('likes')->having('likes_count', '>=', $filters['minLikes']);
        }

        // コメント数フィルタ
        if (!empty($filters['minComments'])) {
            $query->withCount('comments')->having('comments_count', '>=', $filters['minComments']);
        }

        // 特定ユーザーの旅行記録のみ取得
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // ソート
        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortOrder = $filters['sortOrder'] ?? 'desc';

        switch ($sortBy) {
            case 'likes':
                $query->withCount('likes')->orderBy('likes_count', $sortOrder);
                break;
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'date':
                $query->orderBy('start_date', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        // ページネーション
        $travels = $query->paginate($limit, ['*'], 'page', $page);

        // TravelEntityの配列に変換
        $travelEntities = $travels->getCollection()->map(function ($travel) {
            return new TravelEntity(
                $travel->id,
                $travel->user_id,
                $travel->title,
                $travel->description,
                $travel->start_date,
                $travel->end_date,
                $travel->visibility,
                $travel->location_category,
                $travel->prefecture,
                $travel->country,
                [], // travelSpotsは一覧では不要
                $travel->photos->toArray(),
                $travel->tags->toArray(),
                $travel->user->toArray(),
                $travel->comments->toArray(),
                $travel->likes->toArray(),
                $travel->created_at,
                $travel->updated_at
            );
        })->toArray();

        return [
            'data' => $travelEntities,
            'meta' => [
                'current_page' => $travels->currentPage(),
                'last_page' => $travels->lastPage(),
                'per_page' => $travels->perPage(),
                'total' => $travels->total(),
            ]
        ];
    }
}
