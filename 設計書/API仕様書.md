# API仕様書

## 概要

本プロジェクトのRESTful API仕様を定義します。  
認証にはClerkを使用し、旅行記録の作成・取得機能を提供します。

### 基本情報
- **ベースURL**: `/api`
- **認証方式**: Clerk認証（JWTトークン）
- **レスポンス形式**: JSON
- **文字エンコーディング**: UTF-8

---

## 認証

### Clerk認証
リクエストヘッダーにClerkのJWTトークンを含める必要があります。

```http
Authorization: Bearer <clerk_jwt_token>
```

**認証必須エンドポイント**: `clerk.auth` ミドルウェアで保護

---

## 共通レスポンス

### 成功レスポンス
```json
{
    "success": true,
    "data": { ... },
    "message": "操作が成功しました"
}
```

### エラーレスポンス
```json
{
    "success": false,
    "message": "エラーメッセージ",
    "errors": {
        "field": ["エラー詳細"]
    }
}
```

### ステータスコード
| コード | 説明 |
|--------|------|
| 200 | OK（成功） |
| 201 | Created（作成成功） |
| 400 | Bad Request（リクエストエラー） |
| 401 | Unauthorized（認証エラー） |
| 404 | Not Found（リソースが見つからない） |
| 422 | Unprocessable Entity（バリデーションエラー） |
| 500 | Internal Server Error（サーバーエラー） |

---

## エンドポイント

### ユーザー関連

#### ユーザー情報取得
```http
GET /api/user
```

**認証**: 必須

**レスポンス**:
```json
{
    "success": true,
    "data": {
        "clerk_user_id": "user_xxxxxxxxxxxxx"
    }
}
```

---

### 旅行記録関連

#### 旅行記録一覧取得
```http
GET /api/travels
```

**認証**: 不要

**クエリパラメータ**:
| パラメータ | 型 | 必須 | 説明 |
|-----------|----|------|------|
| page | integer | × | ページ番号（デフォルト: 1） |
| limit | integer | × | 1ページあたりの件数（デフォルト: 20） |
| user_id | integer | × | ユーザーIDでフィルター |

**レスポンス**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "京都の古都を巡る旅",
            "location": "京都駅到着",
            "date": "2024-10-15 - 2024-10-20",
            "duration": "6日間",
            "images": ["https://example.com/photo1.jpg"],
            "user": {
                "name": "ユーザー名",
                "avatar": "https://example.com/avatar.jpg"
            },
            "likes": 5,
            "commentCount": 2,
            "tags": ["京都", "紅葉", "寺院"]
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 100,
        "per_page": 20
    }
}
```

#### 旅行記録詳細取得
```http
GET /api/travels/{id}
```

**認証**: 不要

**パスパラメータ**:
| パラメータ | 型 | 必須 | 説明 |
|-----------|----|------|------|
| id | integer | ○ | 旅行記録ID |

**レスポンス**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "京都の古都を巡る旅",
        "location": "京都駅到着",
        "date": "2024-10-15 - 2024-10-20",
        "duration": "6日間",
        "images": ["https://example.com/photo1.jpg"],
        "description": "京都の伝統的な寺院や庭園を訪れ...",
        "user": {
            "name": "ユーザー名",
            "avatar": "https://example.com/avatar.jpg",
            "bio": "自己紹介"
        },
        "likes": 5,
        "commentCount": 2,
        "tags": ["京都", "紅葉", "寺院"],
        "locations": [
            {
                "name": "京都駅到着",
                "lat": 34.985849,
                "lng": 135.758766,
                "description": "みんなでここに集合",
                "orderIndex": 1
            }
        ],
        "itinerary": [
            {
                "day": 1,
                "spots": [
                    {
                        "name": "京都駅到着",
                        "time": "10:00"
                    }
                ]
            }
        ]
    }
}
```

#### 旅行記録作成
```http
POST /api/travels
```

**認証**: 必須

**概要**: 新しい旅行記録を作成します。旅行の基本情報、スポット情報、タグ、画像を登録できます。

---

### リクエスト

#### リクエストボディ例
```json
{
    "title": "京都の古都を巡る旅",
    "description": "京都の伝統的な寺院や庭園を訪れる旅",
    "startDate": "2024-10-15",
    "endDate": "2024-10-20",
    "visibility": 1,
    "location_category": 0,
    "prefecture": 26,
    "country": null,
    "tags": ["京都", "紅葉", "寺院"],
    "locations": [
        {
            "order": 1,
            "name": "京都駅",
            "lat": 34.985849,
            "lng": 135.758766,
            "description": "集合場所",
            "visitDate": "2024-10-15",
            "visitTime": "10:00"
        },
        {
            "order": 2,
            "name": "金閣寺",
            "lat": 35.039370,
            "lng": 135.729243,
            "description": "世界遺産の寺院",
            "visitDate": "2024-10-15",
            "visitTime": "14:00"
        }
    ],
    "images": [
        "https://example.com/photo1.jpg",
        "https://example.com/photo2.jpg"
    ]
}
```

#### 基本パラメータ
| パラメータ | 型 | 必須 | 説明 | 例 |
|-----------|----|------|------|-----|
| title | string | ○ | 旅行タイトル（最大255文字） | "京都の古都を巡る旅" |
| description | string | × | 旅行の説明 | "京都の伝統的な寺院や庭園を訪れる旅" |
| startDate | string | ○ | 開始日（YYYY-MM-DD） | "2024-10-15" |
| endDate | string | ○ | 終了日（YYYY-MM-DD、開始日以降） | "2024-10-20" |
| visibility | integer | ○ | 公開設定（0：private、1：public） | 1 |
| locationCategory | integer | × | 場所カテゴリ（0：日本国内、1：海外） | 0 |
| prefecture | integer | × | 都道府県No（定数ファイルに従う） | 26（京都府） |
| country | integer | × | 国No（定数ファイルに従う） | null |
| tags | array | × | タグ一覧（文字列配列） | ["京都", "紅葉"] |
| locations | array | ○ | スポット情報の配列 | 下記参照 |
| images | array | × | 画像URLの配列 | ["https://example.com/photo1.jpg"] |

#### locations配列の要素
| パラメータ | 型 | 必須 | 説明 | 例 |
|-----------|----|------|------|-----|
| order | integer | ○ | スポットの表示順 | 1 |
| name | string | ○ | スポット名（最大255文字） | "京都駅" |
| lat | numeric | × | 緯度 | 34.985849 |
| lng | numeric | × | 経度 | 135.758766 |
| description | string | × | スポットの説明・メモ | "集合場所" |
| visitDate | string | ○ | 訪問日（YYYY-MM-DD） | "2024-10-15" |
| visitTime | string | × | 訪問時刻（HH:MM） | "10:00" |

---

### レスポンス

#### 成功レスポンス（201 Created）
```json
{
    "success": true,
    "message": "Travel created successfully",
    "data": {
        "id": 1,
        "title": "京都の古都を巡る旅",
        "user_id": 1,
        "description": "京都の伝統的な寺院や庭園を訪れる旅",
        "start_date": "2024-10-15",
        "end_date": "2024-10-20",
        "visibility": 1,
        "location_category": 0,
        "prefecture": 26,
        "country": null,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z",
        "travelSpots": [
            {
                "id": 1,
                "travel_id": 1,
                "day_number": null,
                "visit_date": "2024-10-15",
                "visit_time": "10:00",
                "name": "京都駅",
                "latitude": 34.985849,
                "longitude": 135.758766,
                "order_index": 1,
                "memo": "集合場所"
            }
        ],
        "photos": [
            {
                "id": 1,
                "travel_id": 1,
                "travel_spot_id": 1,
                "url": "https://example.com/photo1.jpg",
                "thumbnail_url": null,
                "caption": null
            }
        ],
        "tags": [
            {
                "id": 1,
                "name": "京都"
            }
        ]
    }
}
```

#### エラーレスポンス

**バリデーションエラー（422 Unprocessable Entity）**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "startDate": ["The start date field is required."],
        "endDate": ["The end date must be a date after or equal to start date."],
        "visibility": ["The visibility field is required."],
        "locations.0.visitDate": ["The visit date field is required."],
        "locations.0.name": ["The name field is required."]
    }
}
```

**認証エラー（401 Unauthorized）**
```json
{
    "success": false,
    "message": "Unauthorized"
}
```

---

### 注意事項

- **認証**: 有効なClerk JWTトークンが必要です
- **日付形式**: `YYYY-MM-DD` 形式で指定してください
- **時刻形式**: `HH:MM` 形式で指定してください
- **緯度経度**: 数値で指定してください（例：34.985849）
- **定数値**: visibility、location_category、prefecture、countryは定数ファイルの値を使用してください
- **画像URL**: 外部の有効なURLを指定してください

---

### Webhook関連

#### Clerk Webhook
```http
POST /api/webhook/clerk
```

**認証**: 不要（Clerkの署名検証）

**用途**:
- ユーザー作成・更新・削除イベントの処理
- 認証状態の同期

---

## データ型定義

### 共通オブジェクト

#### User
| フィールド | 型 | 必須 | 説明 |
|-----------|----|------|------|
| name | string | ○ | ユーザー名 |
| avatar | string | × | アバター画像URL |
| bio | string | × | 自己紹介 |

#### Location
| フィールド | 型 | 必須 | 説明 |
|-----------|----|------|------|
| name | string | ○ | スポット名 |
| lat | float | ○ | 緯度 |
| lng | float | ○ | 経度 |
| description | string | × | 説明・メモ |

---

## エラーコード

### バリデーションエラー（422）
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "startDate": ["The start date field is required."]
    }
}
```

### 認証エラー（401）
```json
{
    "success": false,
    "message": "Unauthorized"
}
```

### リソース未発見（404）
```json
{
    "success": false,
    "message": "Not Found"
}
```

---

## 注意事項

- 認証必須のエンドポイントは、有効なClerk JWTトークンが必要です
- 日付形式は `YYYY-MM-DD` 形式で指定してください
- 画像URLは外部の有効なURLを指定してください
- 緯度・経度は数値で指定してください

---

## 今後の拡張予定

- 旅行記録の更新・削除API
- いいね・お気に入り機能
- コメント機能
- 検索・フィルター機能
- 画像アップロード機能 