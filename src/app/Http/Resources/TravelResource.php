<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TravelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            // 現在Locationに関する情報は取得できないためデータベースから検討
            // 'location'    => optional($this->tripSpots->first())->name ?? '',
            'location'    => isset($this->tripSpots[0]) ? ($this->tripSpots[0]['name'] ?? '') : '',
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
            // 訪問地にあたる情報の持ち方、出し方は検討
            'locations'    => collect($this->tripSpots)->map(function ($spot) {
                return [
                    'name'        => $spot['name'] ?? '',
                    'lat'         => $spot['latitude'] ?? '',
                    'lng'         => $spot['longitude'] ?? '',
                    'description' => $spot['memo'] ?? '',
                ];
            })->all(),
            'itinerary' => $this->formatItinerary(collect($this->tripSpots)),
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
                        'description' => $spot['name'] ?? '',
                    ];
                })->all(),
            ];
        }
        return $itinerary;
    }
}
