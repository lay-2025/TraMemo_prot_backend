<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\Travel\Constants\LocationConstants;

class TravelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            // locationの表記を都道府県名, 日本 または 国名 で返す
            'location'    => $this->getLocationText(),
            'date'        => $this->startDate . ' - ' . $this->endDate,
            'duration'    => $this->getDurationText($this->startDate, $this->endDate),
            'images'      => collect($this->photos)->pluck('file_path')->all(),
            'description' => $this->description,
            'user'        => [
                'name'   => $this->user['name'] ?? '',
                'avatar' => $this->user['avatar_url'] ?? '',
                'bio'    => $this->user['bio'] ?? '',
            ],
            'likes'        => collect($this->likes)->count(),
            'commentCount' => collect($this->comments)->count(),
            'tags'         => collect($this->tags)->pluck('name')->all(),
            // 地図欄の表示用
            'locations'    => collect($this->travelSpots)->map(function ($spot) {
                return [
                    'name'        => $spot['name'] ?? '',
                    'lat'         => $spot['latitude'] ?? '',
                    'lng'         => $spot['longitude'] ?? '',
                    'description' => $spot['memo'] ?? '',
                    'orderIndex'  => $spot['order_index'] ?? 0,
                ];
            })->all(),
            // 旅程欄の表示用
            'itinerary' => $this->formatItinerary(collect($this->travelSpots)),
            'comments'  => collect($this->comments)->map(function ($comment) {
                return [
                    'id'      => $comment['id'] ?? null,
                    'user'    => [
                        'name'   => $comment['user']['name'] ?? '',
                        'avatar' => $comment['user']['avatar_url'] ?? '',
                    ],
                    'content' => $comment['content'] ?? '',
                    'date'    => isset($comment['created_at']) && $comment['created_at']
                        ? \Carbon\Carbon::parse($comment['created_at'])->format('Y年m月d日')
                        : '',
                ];
            })->all(),
        ];
    }

    /**
     * location表記を返す
     */
    private function getLocationText()
    {
        $category = $this->locationCategory ?? $this->location_category ?? null;
        if ($category === null) return '';
        if ((int)$category === LocationConstants::LOCATION_CATEGORY_DOMESTIC) {
            $prefectureId = $this->prefecture ?? null;
            $prefName = $prefectureId ? LocationConstants::getPrefectureName((int)$prefectureId) : null;
            return $prefName ? ($prefName . ', 日本') : '';
        } elseif ((int)$category === LocationConstants::LOCATION_CATEGORY_OVERSEAS) {
            $countryId = $this->country ?? null;
            $countryName = $countryId ? LocationConstants::getCountryName((int)$countryId) : null;
            return $countryName ?? '';
        }
        return '';
    }

    private function getDurationText($start, $end)
    {
        if (!$start || !$end) return '';
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);
        $interval = $startDate->diff($endDate)->days + 1;
        return $interval . '日間';
    }

    private function formatItinerary($spots)
    {
        // day_numberごとにスポットをまとめる
        $grouped = $spots->groupBy('day_number');
        $itinerary = [];
        foreach ($grouped as $day => $daySpots) {
            $itinerary[] = [
                'day' => (int)$day,
                'date' => $daySpots->first()['visit_date'] ?? '',
                'activities' => $daySpots->map(function ($spot) {
                    return [
                        'time' => $spot['visit_time'] ?? '',
                        'name' => $spot['name'] ?? '',
                        'description' => $spot['memo'] ?? '',
                    ];
                })->all(),
            ];
        }
        return $itinerary;
    }
}
