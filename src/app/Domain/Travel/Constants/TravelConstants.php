<?php

namespace App\Domain\Travel\Constants;

class TravelConstants
{
    /**
     * 全定数を取得
     */
    public static function getAllConstants(): array
    {
        return [
            'location_category' => LocationConstants::getLocationCategories(),
            'prefecture' => LocationConstants::getPrefectures(),
            'country' => LocationConstants::getCountries(),
            'visibility' => VisibilityConstants::getVisibilityOptions(),
        ];
    }

    /**
     * 場所関連定数を取得
     */
    public static function getLocationConstants(): array
    {
        return [
            'location_category' => LocationConstants::getLocationCategories(),
            'prefecture' => LocationConstants::getPrefectures(),
            'country' => LocationConstants::getCountries(),
        ];
    }

    /**
     * 旅行設定定数を取得
     */
    public static function getTravelSettingsConstants(): array
    {
        return [
            'visibility' => VisibilityConstants::getVisibilityOptions(),
        ];
    }

    /**
     * 定数名から値を取得
     */
    public static function getConstantValue(string $constantName, int $id): ?string
    {
        return match ($constantName) {
            'location_category' => LocationConstants::getLocationCategoryName($id),
            'prefecture' => LocationConstants::getPrefectureName($id),
            'country' => LocationConstants::getCountryName($id),
            'visibility' => VisibilityConstants::getVisibilityName($id),
            default => null,
        };
    }
}
