<?php

namespace App\Domain\Travel\Constants;

class LocationConstants
{
    /**
     * 場所カテゴリ定数
     */
    public const LOCATION_CATEGORY_DOMESTIC = 0;
    public const LOCATION_CATEGORY_OVERSEAS = 1;

    /**
     * 場所カテゴリ一覧を取得
     */
    public static function getLocationCategories(): array
    {
        return [
            self::LOCATION_CATEGORY_DOMESTIC => '日本国内',
            self::LOCATION_CATEGORY_OVERSEAS => '海外',
        ];
    }

    /**
     * 都道府県一覧を取得
     */
    public static function getPrefectures(): array
    {
        return [
            1 => '北海道',
            2 => '青森県',
            3 => '岩手県',
            4 => '宮城県',
            5 => '秋田県',
            6 => '山形県',
            7 => '福島県',
            8 => '茨城県',
            9 => '栃木県',
            10 => '群馬県',
            11 => '埼玉県',
            12 => '千葉県',
            13 => '東京都',
            14 => '神奈川県',
            15 => '新潟県',
            16 => '富山県',
            17 => '石川県',
            18 => '福井県',
            19 => '山梨県',
            20 => '長野県',
            21 => '岐阜県',
            22 => '静岡県',
            23 => '愛知県',
            24 => '三重県',
            25 => '滋賀県',
            26 => '京都府',
            27 => '大阪府',
            28 => '兵庫県',
            29 => '奈良県',
            30 => '和歌山県',
            31 => '鳥取県',
            32 => '島根県',
            33 => '岡山県',
            34 => '広島県',
            35 => '山口県',
            36 => '徳島県',
            37 => '香川県',
            38 => '愛媛県',
            39 => '高知県',
            40 => '福岡県',
            41 => '佐賀県',
            42 => '長崎県',
            43 => '熊本県',
            44 => '大分県',
            45 => '宮崎県',
            46 => '鹿児島県',
            47 => '沖縄県',
        ];
    }

    /**
     * 国名一覧を取得
     */
    public static function getCountries(): array
    {
        return [
            1 => 'アメリカ合衆国',
            2 => 'カナダ',
            3 => 'メキシコ',
            4 => 'イギリス',
            5 => 'フランス',
            6 => 'ドイツ',
            7 => 'イタリア',
            8 => 'スペイン',
            9 => 'オランダ',
            10 => 'ベルギー',
            11 => 'スイス',
            12 => 'オーストリア',
            13 => 'スウェーデン',
            14 => 'ノルウェー',
            15 => 'デンマーク',
            16 => 'フィンランド',
            17 => 'ポーランド',
            18 => 'チェコ',
            19 => 'ハンガリー',
            20 => 'ロシア',
            21 => '中国',
            22 => '韓国',
            23 => '台湾',
            24 => '香港',
            25 => 'シンガポール',
            26 => 'タイ',
            27 => 'ベトナム',
            28 => 'マレーシア',
            29 => 'インドネシア',
            30 => 'フィリピン',
            31 => 'インド',
            32 => 'オーストラリア',
            33 => 'ニュージーランド',
            34 => 'ブラジル',
            35 => 'アルゼンチン',
            36 => 'チリ',
            37 => 'ペルー',
            38 => 'コロンビア',
            39 => '南アフリカ',
            40 => 'エジプト',
            41 => 'モロッコ',
            42 => 'ケニア',
            43 => 'ナイジェリア',
            44 => 'ガーナ',
            45 => 'セネガル',
            46 => 'チュニジア',
            47 => 'アルジェリア',
            48 => 'リビア',
            49 => 'スーダン',
            50 => 'エチオピア',
        ];
    }

    /**
     * 都道府県名を取得
     */
    public static function getPrefectureName(int $id): ?string
    {
        $prefectures = self::getPrefectures();
        return $prefectures[$id] ?? null;
    }

    /**
     * 国名を取得
     */
    public static function getCountryName(int $id): ?string
    {
        $countries = self::getCountries();
        return $countries[$id] ?? null;
    }

    /**
     * 場所カテゴリ名を取得
     */
    public static function getLocationCategoryName(int $id): ?string
    {
        $categories = self::getLocationCategories();
        return $categories[$id] ?? null;
    }
}
