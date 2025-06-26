<?php

namespace App\Application\Travel\UseCases;

use Illuminate\Support\Facades\DB;
use App\Domain\Travel\Repositories\TravelRepositoryInterface;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Domain\Tag\Repositories\TagRepositoryInterface;

class CreateTravelUseCase
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository,
        private PhotoRepositoryInterface $photoRepository,
        private TagRepositoryInterface $tagRepository,
    ) {}

    public function handle(int $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            // Travel作成
            // day_numberを自動計算するため、location.visitDateでまとめる
            // 事前にvisitDateで昇順ソートし、日付順にday_numberを振る
            if (!empty($data['locations'])) {
                // visitDateで昇順ソート
                usort($data['locations'], function ($a, $b) {
                    return strcmp($a['visitDate'] ?? '', $b['visitDate'] ?? '');
                });

                $visitDates = [];
                $dayNumberMap = [];
                $currentDay = 1;
                foreach ($data['locations'] as $index => $location) {
                    $visitDate = $location['visitDate'] ?? null;
                    if ($visitDate === null) {
                        $data['locations'][$index]['dayNumber'] = null;
                        continue;
                    }
                    if (!in_array($visitDate, $visitDates, true)) {
                        $visitDates[] = $visitDate;
                        $dayNumberMap[$visitDate] = $currentDay++;
                    }
                    $data['locations'][$index]['dayNumber'] = $dayNumberMap[$visitDate];
                }
            }
            $travel = $this->travelRepository->createWithSpots($userId, $data);

            // Photo作成
            foreach ($data['images'] ?? [] as $img) {
                $this->photoRepository->create([
                    'travel_id' => $travel->id,
                    'url' => $img,
                ]);
            }

            // Tag作成・紐付け
            $tagIds = [];
            foreach ($data['tags'] ?? [] as $tagName) {
                $tag = $this->tagRepository->firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            if ($tagIds) {
                $travel->tags()->sync($tagIds);
            }

            return $travel->fresh(['travelSpots', 'photos', 'tags']);
        });
    }
}
