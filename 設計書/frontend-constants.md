# フロントエンド定数ファイル例

## TypeScript版: `src/constants/location.ts`

```typescript
// 場所カテゴリ定数
export const LOCATION_CATEGORY = {
  DOMESTIC: 0,
  OVERSEAS: 1,
} as const;

// 公開設定定数
export const VISIBILITY = {
  PRIVATE: 0,
  PUBLIC: 1,
} as const;

// 場所カテゴリ一覧
export const LOCATION_CATEGORIES = {
  [LOCATION_CATEGORY.DOMESTIC]: '日本国内',
  [LOCATION_CATEGORY.OVERSEAS]: '海外',
} as const;

// 都道府県一覧
export const PREFECTURES = {
  1: '北海道',
  2: '青森県',
  3: '岩手県',
  4: '宮城県',
  5: '秋田県',
  6: '山形県',
  7: '福島県',
  8: '茨城県',
  9: '栃木県',
  10: '群馬県',
  11: '埼玉県',
  12: '千葉県',
  13: '東京都',
  14: '神奈川県',
  15: '新潟県',
  16: '富山県',
  17: '石川県',
  18: '福井県',
  19: '山梨県',
  20: '長野県',
  21: '岐阜県',
  22: '静岡県',
  23: '愛知県',
  24: '三重県',
  25: '滋賀県',
  26: '京都府',
  27: '大阪府',
  28: '兵庫県',
  29: '奈良県',
  30: '和歌山県',
  31: '鳥取県',
  32: '島根県',
  33: '岡山県',
  34: '広島県',
  35: '山口県',
  36: '徳島県',
  37: '香川県',
  38: '愛媛県',
  39: '高知県',
  40: '福岡県',
  41: '佐賀県',
  42: '長崎県',
  43: '熊本県',
  44: '大分県',
  45: '宮崎県',
  46: '鹿児島県',
  47: '沖縄県',
} as const;

// 国名一覧
export const COUNTRIES = {
  1: 'アメリカ合衆国',
  2: 'カナダ',
  3: 'メキシコ',
  4: 'イギリス',
  5: 'フランス',
  6: 'ドイツ',
  7: 'イタリア',
  8: 'スペイン',
  9: 'オランダ',
  10: 'ベルギー',
  11: 'スイス',
  12: 'オーストリア',
  13: 'スウェーデン',
  14: 'ノルウェー',
  15: 'デンマーク',
  16: 'フィンランド',
  17: 'ポーランド',
  18: 'チェコ',
  19: 'ハンガリー',
  20: 'ロシア',
  21: '中国',
  22: '韓国',
  23: '台湾',
  24: '香港',
  25: 'シンガポール',
  26: 'タイ',
  27: 'ベトナム',
  28: 'マレーシア',
  29: 'インドネシア',
  30: 'フィリピン',
  31: 'インド',
  32: 'オーストラリア',
  33: 'ニュージーランド',
  34: 'ブラジル',
  35: 'アルゼンチン',
  36: 'チリ',
  37: 'ペルー',
  38: 'コロンビア',
  39: '南アフリカ',
  40: 'エジプト',
  41: 'モロッコ',
  42: 'ケニア',
  43: 'ナイジェリア',
  44: 'ガーナ',
  45: 'セネガル',
  46: 'チュニジア',
  47: 'アルジェリア',
  48: 'リビア',
  49: 'スーダン',
  50: 'エチオピア',
} as const;

// 公開設定一覧
export const VISIBILITY_OPTIONS = {
  [VISIBILITY.PRIVATE]: 'private',
  [VISIBILITY.PUBLIC]: 'public',
} as const;

// 型定義
export type LocationCategory = typeof LOCATION_CATEGORY[keyof typeof LOCATION_CATEGORY];
export type Visibility = typeof VISIBILITY[keyof typeof VISIBILITY];
export type PrefectureId = keyof typeof PREFECTURES;
export type CountryId = keyof typeof COUNTRIES;

// ユーティリティ関数
export const getPrefectureName = (id: PrefectureId): string => {
  return PREFECTURES[id] || '';
};

export const getCountryName = (id: CountryId): string => {
  return COUNTRIES[id] || '';
};

export const getLocationCategoryName = (id: LocationCategory): string => {
  return LOCATION_CATEGORIES[id] || '';
};

export const getVisibilityName = (id: Visibility): string => {
  return VISIBILITY_OPTIONS[id] || '';
};

// 全定数を取得
export const getAllConstants = () => ({
  location_category: LOCATION_CATEGORIES,
  prefecture: PREFECTURES,
  country: COUNTRIES,
  visibility: VISIBILITY_OPTIONS,
});
```

## JavaScript版: `src/constants/location.js`

```javascript
// 場所カテゴリ定数
export const LOCATION_CATEGORY = {
  DOMESTIC: 0,
  OVERSEAS: 1,
};

// 公開設定定数
export const VISIBILITY = {
  PRIVATE: 0,
  PUBLIC: 1,
};

// 場所カテゴリ一覧
export const LOCATION_CATEGORIES = {
  [LOCATION_CATEGORY.DOMESTIC]: '日本国内',
  [LOCATION_CATEGORY.OVERSEAS]: '海外',
};

// 都道府県一覧
export const PREFECTURES = {
  1: '北海道',
  2: '青森県',
  // ... 他の都道府県
  47: '沖縄県',
};

// 国名一覧
export const COUNTRIES = {
  1: 'アメリカ合衆国',
  2: 'カナダ',
  // ... 他の国
  50: 'エチオピア',
};

// 公開設定一覧
export const VISIBILITY_OPTIONS = {
  [VISIBILITY.PRIVATE]: 'private',
  [VISIBILITY.PUBLIC]: 'public',
};

// ユーティリティ関数
export const getPrefectureName = (id) => {
  return PREFECTURES[id] || '';
};

export const getCountryName = (id) => {
  return COUNTRIES[id] || '';
};

export const getLocationCategoryName = (id) => {
  return LOCATION_CATEGORIES[id] || '';
};

export const getVisibilityName = (id) => {
  return VISIBILITY_OPTIONS[id] || '';
};

// 全定数を取得
export const getAllConstants = () => ({
  location_category: LOCATION_CATEGORIES,
  prefecture: PREFECTURES,
  country: COUNTRIES,
  visibility: VISIBILITY_OPTIONS,
});
```

## 使用例

```typescript
// コンポーネントでの使用例
import { 
  LOCATION_CATEGORY, 
  PREFECTURES, 
  getPrefectureName 
} from '../constants/location';

const TravelForm = () => {
  const [locationCategory, setLocationCategory] = useState(LOCATION_CATEGORY.DOMESTIC);
  const [prefecture, setPrefecture] = useState(13); // 東京都

  return (
    <div>
      <select value={locationCategory} onChange={(e) => setLocationCategory(Number(e.target.value))}>
        <option value={LOCATION_CATEGORY.DOMESTIC}>日本国内</option>
        <option value={LOCATION_CATEGORY.OVERSEAS}>海外</option>
      </select>
      
      {locationCategory === LOCATION_CATEGORY.DOMESTIC && (
        <select value={prefecture} onChange={(e) => setPrefecture(Number(e.target.value))}>
          {Object.entries(PREFECTURES).map(([id, name]) => (
            <option key={id} value={id}>{name}</option>
          ))}
        </select>
      )}
      
      <p>選択された都道府県: {getPrefectureName(prefecture)}</p>
    </div>
  );
};
``` 