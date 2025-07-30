<?php

namespace App\Http\Resources\Travel\Traits;

use App\Domain\Travel\Constants\LocationConstants;

trait TravelResourceTrait
{
    protected function getLocationText(): string
    {
        if ($this->locationCategory === 0) {
            // 国内の場合
            if ($this->prefecture) {
                $prefectureName = LocationConstants::getPrefectureName($this->prefecture);
                return $prefectureName ? "{$prefectureName}, 日本" : "日本";
            }
            return "日本";
        } else {
            // 海外の場合
            if ($this->country) {
                $countryName = LocationConstants::getCountryName($this->country);
                return $countryName ?: "海外";
            }
            return "海外";
        }
    }

    protected function getDurationText(?string $startDate, ?string $endDate): string
    {
        if (!$startDate || !$endDate) {
            return '';
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        $days = $interval->days + 1; // 開始日も含める

        return "{$days}日間";
    }

    protected function formatItinerary($spots)
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
