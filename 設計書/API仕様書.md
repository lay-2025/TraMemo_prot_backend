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
                "description": "みんなでここに集合"
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

**リクエストボディ**:
```json
{
    "title": "旅行タイトル",
    "description": "旅行の説明（任意）",
    "startDate": "2024-10-15",
    "endDate": "2024-10-20",
    "prefecture": "京都府",
    "visibility": "public",
    "tags": ["京都", "紅葉"],
    "locations": [
        {
            "order": 1,
            "name": "京都駅",
            "lat": 34.985849,
            "lng": 135.758766,
            "description": "集合場所",
            "visitDate": "2024-10-15",
            "visitTime": "10:00"
        }
    ],
    "images": ["https://example.com/photo1.jpg"]
}
```

**リクエストパラメータ**:
| パラメータ | 型 | 必須 | 説明 |
|-----------|----|------|------|
| title | string | ○ | 旅行タイトル（最大255文字） |
| description | string | × | 旅行の説明 |
| startDate | string | ○ | 開始日（YYYY-MM-DD） |
| endDate | string | ○ | 終了日（YYYY-MM-DD、開始日以降） |
| prefecture | string | × | 都道府県名（最大255文字） |
| visibility | string | × | 公開設定（public/private） |
| tags | array | × | タグ一覧（文字列配列） |
| locations | array | × | スポット情報の配列 |
| images | array | × | 画像URLの配列 |

**locations配列の要素**:
| パラメータ | 型 | 必須 | 説明 |
|-----------|----|------|------|
| order | integer | ○ | スポットの表示順 |
| name | string | ○ | スポット名（最大255文字） |
| lat | float | ○ | 緯度 |
| lng | float | ○ | 経度 |
| description | string | × | スポットの説明・メモ |
| visitDate | string | × | 訪問日（YYYY-MM-DD） |
| visitTime | string | × | 訪問時刻（HH:MM） |

**成功レスポンス**:
```json
{
    "success": true,
    "message": "Travel created successfully",
    "data": {
        "id": 1,
        "title": "旅行タイトル",
        "user_id": 1,
        "travelSpots": [...],
        "photos": [...],
        "tags": [...]
    }
}
```

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