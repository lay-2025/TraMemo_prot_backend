<?php

namespace App\Domain\Travel\Constants;

class VisibilityConstants
{
    /**
     * 公開設定定数
     */
    public const VISIBILITY_PRIVATE = 0;
    public const VISIBILITY_PUBLIC = 1;

    /**
     * 公開設定一覧を取得
     */
    public static function getVisibilityOptions(): array
    {
        return [
            self::VISIBILITY_PRIVATE => 'private',
            self::VISIBILITY_PUBLIC => 'public',
        ];
    }

    /**
     * 公開設定名を取得
     */
    public static function getVisibilityName(int $id): ?string
    {
        $options = self::getVisibilityOptions();
        return $options[$id] ?? null;
    }

    /**
     * 公開設定かどうかを判定
     */
    public static function isPublic(int $visibility): bool
    {
        return $visibility === self::VISIBILITY_PUBLIC;
    }

    /**
     * 非公開設定かどうかを判定
     */
    public static function isPrivate(int $visibility): bool
    {
        return $visibility === self::VISIBILITY_PRIVATE;
    }
}
