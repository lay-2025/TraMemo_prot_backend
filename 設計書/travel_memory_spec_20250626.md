# 🌍 Travel Memory - 詳細システム仕様書

## 📋 目次
1. [プロジェクト概要](#プロジェクト概要)
2. [システムアーキテクチャ](#システムアーキテクチャ)
3. [技術仕様](#技術仕様)
4. [データベース設計](#データベース設計)
5. [API仕様](#api仕様)
6. [画面仕様](#画面仕様)
7. [セキュリティ要件](#セキュリティ要件)
8. [非機能要件](#非機能要件)
9. [開発・デプロイメント仕様](#開発デプロイメント仕様)

---

## 📝 プロジェクト概要

### 🎯 目的・概要
**Travel Memory**は、個人の旅行記録を地図と写真で管理し、他のユーザーと共有できるSNS機能を持つWebアプリケーションです。地図上での旅行ルート表示、写真付きの詳細記録、他ユーザーとの旅行体験共有を通じて、旅行の思い出をより豊かに保存・活用することを目的としています。

### 🎯 ターゲットユーザー
- 国内・海外旅行愛好者
- 旅行記録をデジタル化したい人
- 他の人の旅行体験を参考にしたい人
- 写真と地図で思い出を整理したい人

### 🌟 主要機能
- **旅行記録の作成・編集・削除**（写真・地図・コメント付き）
- **地図連携**（Google Maps API使用、ルート表示）
- **SNS機能**（いいね・お気に入り・コメント・シェア）
- **画像管理**（AWS S3連携、サムネイル自動生成）
- **データエクスポート**（Excel出力）
- **AI連携**（ChatGPT APIによる旅行提案）
- **検索・フィルター機能**

---

## 🏗️ システムアーキテクチャ

### 全体構成図
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │  External APIs  │
│   (React/Vite)  │───▶│   (Laravel)     │───▶│  Google Maps    │
│   Port: 5173    │    │   Port: 8000    │    │  ChatGPT API    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
          │                        │                        
          │                        │                        
          ▼                        ▼                        
┌─────────────────┐    ┌─────────────────┐                 
│   CloudFront    │    │   AWS Services  │                 
│   (CDN)         │    │   - S3 (Images) │                 
└─────────────────┘    │   - RDS (MySQL) │                 
                        │   - EC2 (API)   │                 
                        └─────────────────┘                 
```

### アーキテクチャの特徴
- **SPA (Single Page Application)**: React + Viteによるモダンなフロントエンド
- **RESTful API**: LaravelによるAPI中心設計
- **マイクロサービス指向**: フロントエンドとバックエンドの完全分離
- **クラウドネイティブ**: AWS各種サービスを活用したスケーラブル構成

---

## 🛠️ 技術仕様

### フロントエンド技術スタック
| 技術 | バージョン | 用途 |
|------|-----------|------|
| React | ^18.0.0 | UIライブラリ |
| Vite | ^4.0.0 | ビルドツール |
| TypeScript | ^5.0.0 | 型安全性 |
| TailwindCSS | ^3.0.0 | CSSフレームワーク |
| React Router | ^6.0.0 | ルーティング |
| Axios | ^1.0.0 | HTTP通信 |
| React Query | ^4.0.0 | データフェッチング・キャッシュ |
| React Hook Form | ^7.0.0 | フォーム管理 |
| Leaflet/Google Maps | latest | 地図表示 |

### バックエンド技術スタック
| 技術 | バージョン | 用途 |
|------|-----------|------|
| Laravel | ^10.0 | PHPフレームワーク |
| PHP | ^8.1 | 実行環境 |
| MySQL | ^8.0 | データベース |
| Laravel Sanctum | ^3.0 | API認証 |
| Laravel Socialite | ^5.0 | SNSログイン |
| Laravel Excel | ^3.1 | Excel出力 |
| Intervention Image | ^2.7 | 画像処理 |
| Guzzle HTTP | ^7.0 | HTTP通信（外部API） |

### インフラ・外部サービス
| サービス | 用途 |
|----------|------|
| AWS EC2 | アプリケーションサーバー |
| AWS RDS (MySQL) | データベース |
| AWS S3 | 画像ストレージ |
| AWS CloudFront | CDN |
| Google Maps API | 地図・位置情報 |
| OpenAI API | AI機能 |

---

## 🗃️ データベース設計

### ER図の概念
```
Users ──┬── Trips ──┬── TripSpots ──── Photos
        │           └── TripTag ──── Tags
        ├── Likes
        ├── Favorites  
        └── Comments
```

### テーブル定義

#### 1. users（ユーザー）
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    provider VARCHAR(50) NULL COMMENT 'google, twitter, etc.',
    provider_id VARCHAR(255) NULL,
    avatar_url TEXT NULL,
    bio TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_provider (provider, provider_id)
);
```

#### 2. trips（旅行記録）
```sql
CREATE TABLE trips (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    is_public BOOLEAN DEFAULT TRUE,
    total_cost DECIMAL(10,2) NULL,
    currency VARCHAR(3) DEFAULT 'JPY',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_public (is_public)
);
```

#### 3. trip_spots（訪問地）
```sql
CREATE TABLE trip_spots (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    trip_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    address TEXT NULL,
    order_index INT DEFAULT 0,
    memo TEXT NULL,
    visit_date DATE NULL,
    stay_duration INT NULL COMMENT '滞在時間（分）',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    INDEX idx_trip_id (trip_id),
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_order (trip_id, order_index)
);
```

#### 4. photos（写真）
```sql
CREATE TABLE photos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    trip_id BIGINT UNSIGNED NOT NULL,
    trip_spot_id BIGINT UNSIGNED NULL,
    url TEXT NOT NULL,
    thumbnail_url TEXT NULL,
    original_filename VARCHAR(255) NULL,
    file_size INT NULL,
    caption TEXT NULL,
    taken_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_spot_id) REFERENCES trip_spots(id) ON DELETE SET NULL,
    INDEX idx_trip_id (trip_id),
    INDEX idx_spot_id (trip_spot_id)
);
```

#### 5. tags（タグ）
```sql
CREATE TABLE tags (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_name (name)
);
```

#### 6. trip_tag（旅行記録とタグの多対多）
```sql
CREATE TABLE trip_tag (
    trip_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (trip_id, tag_id)
);
```

#### 7. likes（いいね）
```sql
CREATE TABLE likes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    trip_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, trip_id)
);
```

#### 8. favorites（お気に入り）
```sql
CREATE TABLE favorites (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    trip_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, trip_id)
);
```

#### 9. comments（コメント）
```sql
CREATE TABLE comments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    trip_id BIGINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    INDEX idx_trip_id (trip_id)
);
```

---

## 🔌 API仕様

### 基本情報
- **ベースURL**: `http://localhost:8000/api`
- **認証方式**: Laravel Sanctum (Bearer Token)
- **レスポンス形式**: JSON
- **文字エンコーディング**: UTF-8

### 共通レスポンス形式
```json
{
    "success": true|false,
    "message": "メッセージ",
    "data": {}, // 成功時のデータ
    "errors": {}, // エラー時の詳細
    "meta": {
        "pagination": {
            "current_page": 1,
            "total": 100,
            "per_page": 20
        }
    }
}
```

### 認証関連API

#### POST /api/register
ユーザー登録
```json
// Request
{
    "name": "山田太郎",
    "email": "yamada@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

// Response
{
    "success": true,
    "message": "ユーザー登録が完了しました",
    "data": {
        "user": {
            "id": 1,
            "name": "山田太郎",
            "email": "yamada@example.com"
        },
        "token": "1|ABC123..."
    }
}
```

#### POST /api/login
ログイン
```json
// Request
{
    "email": "yamada@example.com",
    "password": "password123"
}

// Response
{
    "success": true,
    "message": "ログインしました",
    "data": {
        "user": {...},
        "token": "2|DEF456..."
    }
}
```

#### POST /api/logout
ログアウト（要認証）
```json
// Response
{
    "success": true,
    "message": "ログアウトしました"
}
```

### 旅行記録関連API

#### GET /api/trips
旅行記録一覧取得
```
Query Parameters:
- page: ページ番号 (default: 1)
- per_page: 1ページあたりの件数 (default: 20, max: 100)
- user_id: 特定ユーザーの記録のみ取得
- tag: タグでフィルター
- start_date: 開始日以降の記録
- end_date: 終了日以前の記録
- search: タイトル・説明文での検索
```

```json
// Response
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "京都3日間の旅",
            "description": "紅葉シーズンの京都を満喫",
            "start_date": "2024-11-15",
            "end_date": "2024-11-17",
            "user": {
                "id": 1,
                "name": "山田太郎",
                "avatar_url": "https://..."
            },
            "photos": [
                {
                    "url": "https://...",
                    "thumbnail_url": "https://..."
                }
            ],
            "tags": ["京都", "紅葉", "寺院"],
            "likes_count": 15,
            "comments_count": 3,
            "is_liked": false,
            "is_favorited": true
        }
    ],
    "meta": {
        "pagination": {...}
    }
}
```

#### POST /api/trips
旅行記録作成（要認証）
```json
// Request
{
    "title": "沖縄本島一周",
    "description": "美しい海と文化を堪能",
    "start_date": "2024-12-01",
    "end_date": "2024-12-05",
    "is_public": true,
    "spots": [
        {
            "name": "首里城",
            "latitude": 26.2175,
            "longitude": 127.7193,
            "memo": "歴史を感じる素晴らしい城跡"
        }
    ],
    "tags": ["沖縄", "海", "文化"]
}
```

#### GET /api/trips/{id}
旅行記録詳細取得
```json
// Response
{
    "success": true,
    "data": {
        "id": 1,
        "title": "京都3日間の旅",
        // ... 基本情報
        "spots": [
            {
                "id": 1,
                "name": "清水寺",
                "latitude": 34.9949,
                "longitude": 135.7851,
                "photos": [...]
            }
        ],
        "comments": [
            {
                "id": 1,
                "content": "素晴らしい旅行記録ですね！",
                "user": {...},
                "created_at": "2024-11-18T10:00:00Z"
            }
        ]
    }
}
```

#### PUT /api/trips/{id}
旅行記録更新（要認証・作成者のみ）

#### DELETE /api/trips/{id}
旅行記録削除（要認証・作成者のみ）

### 写真関連API

#### POST /api/trips/{trip_id}/photos
写真アップロード（要認証）
```
Content-Type: multipart/form-data

Files:
- photos[]: 複数ファイル対応
- caption[]: 各写真のキャプション
- trip_spot_id[]: 関連するスポットID（任意）
```

#### DELETE /api/photos/{id}
写真削除（要認証・作成者のみ）

### SNS機能API

#### POST /api/trips/{id}/like
いいね追加/削除（要認証）

#### POST /api/trips/{id}/favorite
お気に入り追加/削除（要認証）

#### POST /api/trips/{id}/comments
コメント投稿（要認証）
```json
// Request
{
    "content": "素晴らしい旅行記録ですね！"
}
```

### その他API

#### GET /api/trips/{id}/export
Excel出力（要認証・作成者のみ）
```
Response: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
```

#### POST /api/ai/suggestions
AI提案取得（要認証）
```json
// Request
{
    "prompt": "京都の次におすすめの観光地を教えて",
    "context": {
        "visited_places": ["京都", "大阪"],
        "interests": ["歴史", "文化"]
    }
}
```

---

## 🎨 画面仕様

### 画面一覧
1. **ホーム画面** (`/`)
2. **ログイン画面** (`/login`)
3. **ユーザー登録画面** (`/register`)
4. **旅行記録一覧** (`/trips`)
5. **旅行記録詳細** (`/trips/:id`)
6. **旅行記録投稿/編集** (`/trips/create`, `/trips/:id/edit`)
7. **マイページ** (`/profile`)
8. **お気に入り一覧** (`/favorites`)

### レスポンシブ対応
- **デスクトップ**: 1024px以上
- **タブレット**: 768px〜1023px
- **スマートフォン**: 767px以下

### 主要画面の詳細

#### ホーム画面
**目的**: 公開されている旅行記録のタイムライン表示

**主要コンポーネント**:
- ヘッダーナビゲーション
- 検索・フィルターバー
- 旅行記録カード一覧
- 無限スクロール
- フローティング投稿ボタン

**表示情報**:
- 投稿者情報（アバター、名前）
- 旅行タイトル・期間
- 代表写真（最大3枚のサムネイル）
- 訪問地の地図プレビュー
- いいね数・コメント数
- タグ

#### 旅行記録詳細画面
**目的**: 個別の旅行記録の詳細表示

**主要コンポーネント**:
- パンくずナビ
- 旅行基本情報
- インタラクティブ地図（ルート表示）
- 写真ギャラリー（Lightbox対応）
- スポット詳細リスト
- コメント一覧・投稿フォーム
- SNSシェアボタン

#### 投稿・編集画面
**目的**: 旅行記録の作成・編集

**主要コンポーネント**:
- 基本情報入力フォーム
- 地図上でのスポット選択
- ドラッグ&ドロップ写真アップロード
- タグ入力（自動補完）
- プレビュー機能
- 下書き保存

---

## 🔒 セキュリティ要件

### 認証・認可
- **多要素認証**: メール認証必須
- **SNSログイン**: Google, Twitter OAuth対応
- **セッション管理**: Laravel Sanctum + HTTPOnly Cookie
- **パスワードポリシー**: 最低8文字、英数字組み合わせ

### データ保護
- **個人情報暗号化**: Laravel暗号化機能使用
- **パスワードハッシュ化**: bcrypt使用
- **HTTPS通信**: 全通信SSL/TLS暗号化
- **CSRFトークン**: Laravel標準機能

### ファイルアップロード
- **ファイル形式制限**: JPEG, PNG, GIF のみ
- **ファイルサイズ制限**: 最大10MB/ファイル
- **ウイルススキャン**: ClamAV連携（本番環境）
- **画像検証**: Intervention Image使用

### アクセス制御
- **認証必須機能**: 投稿、編集、削除、いいね、コメント
- **所有者チェック**: 編集・削除は作成者のみ
- **レート制限**: API呼び出し制限（100req/min/user）
- **IPホワイトリスト**: 管理機能アクセス制限

### 入力値検証
- **SQLインジェクション対策**: Eloquent ORM使用
- **XSS対策**: HTMLエスケープ、CSPヘッダー
- **バリデーション**: Laravel Validation使用
- **サニタイゼーション**: HTMLPurifier使用

---

## ⚡ 非機能要件

### パフォーマンス要件
| 項目 | 目標値 |
|------|--------|
| ページ読み込み時間 | 3秒以内 |
| API応答時間 | 500ms以内 |
| 画像読み込み時間 | 2秒以内 |
| 同時接続ユーザー数 | 1,000人 |

### 可用性要件
- **稼働率**: 99.5%以上
- **メンテナンス時間**: 月1回2時間以内
- **障害復旧時間**: 4時間以内
- **データバックアップ**: 日次自動バックアップ

### スケーラビリティ
- **水平スケーリング**: EC2オートスケーリング対応
- **データベース**: リードレプリカ対応
- **CDN**: CloudFront使用
- **キャッシュ**: Redis使用

### 監視・ログ
- **アプリケーション監視**: Laravel Telescope
- **エラー監視**: Sentry連携
- **アクセスログ**: CloudWatch使用
- **メトリクス監視**: AWS CloudWatch

---

## 🚀 開発・デプロイメント仕様

### 開発環境
```bash
# フロントエンド
Node.js: v18+
npm: v9+
React: v18+
Vite: v4+

# バックエンド  
PHP: v8.1+
Composer: v2+
Laravel: v10+
MySQL: v8.0+
```

### ブランチ戦略
```
main (本番環境)
├── develop (開発環境)
│   ├── feature/user-authentication
│   ├── feature/trip-management
│   └── feature/map-integration
└── hotfix/security-patch
```

### CI/CDパイプライン
#### GitHub Actions設定
```yaml
# .github/workflows/deploy.yml
name: Deploy to AWS
on:
  push:
    branches: [main]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1
      - name: Run Tests
        run: |
          composer install
          php artisan test
  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to EC2
        # AWS EC2へのデプロイ処理
```

### 本番環境構成
#### AWS Infrastructure
```yaml
# infrastructure.yml
Resources:
  WebServer:
    Type: AWS::EC2::Instance
    Properties:
      InstanceType: t3.medium
      ImageId: ami-0c55b159cbfafe1d0
      
  Database:
    Type: AWS::RDS::DBInstance
    Properties:
      DBInstanceClass: db.t3.micro
      Engine: mysql
      EngineVersion: 8.0
      
  Storage:
    Type: AWS::S3::Bucket
    Properties:
      BucketName: travel-memory-images
      PublicAccessBlockConfiguration:
        BlockPublicAcls: true
```

### 環境変数管理
```bash
# .env.example
APP_ENV=production
APP_DEBUG=false
APP_URL=https://travel-memory.com

DB_CONNECTION=mysql
DB_HOST=rds-endpoint
DB_DATABASE=travel_memory
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
AWS_DEFAULT_REGION=ap-northeast-1
AWS_BUCKET=travel-memory-images

GOOGLE_MAPS_API_KEY=${GOOGLE_MAPS_API_KEY}
OPENAI_API_KEY=${OPENAI_API_KEY}
```

### デプロイ手順
1. **コードプッシュ**: GitHub mainブランチへプッシュ
2. **自動テスト**: PHPUnit + Jest実行
3. **ビルド**: フロントエンドアセットビルド
4. **デプロイ**: EC2インスタンスへデプロイ
5. **データベースマイグレーション**: 本番DBへマイグレーション実行
6. **キャッシュクリア**: アプリケーションキャッシュクリア
7. **ヘルスチェック**: API疎通確認

### モニタリング・アラート
- **エラー率**: 5%以上でSlack通知
- **レスポンス時間**: 1秒以上でアラート
- **ディスク使用率**: 80%以上で通知
- **メモリ使用率**: 85%以上で通知

---

## 📈 開発スケジュール

### Phase 1: 基盤構築（4週間）
- Week 1-2: 環境構築、認証機能
- Week 3-4: 基本CRUD、データベース設計

### Phase 2: コア機能開発（6週間）
- Week 5-6: 地図連携、写真アップロード
- Week 7-8: SNS機能（いいね、コメント）
- Week 9-10: 検索・フィルター機能

### Phase 3: 高度な機能（4週間）
- Week 11-12: AI連携、Excel出力
- Week 13-14: UI/UX改善、レスポンシブ対応

### Phase 4: テスト・デプロイ（2週間）
- Week 15: 総合テスト、パフォーマンステスト
- Week 16: 本番デプロイ、運用開始

---

## 📝 付録

### 参考資料
- [Laravel 10 Documentation](https://laravel.com/docs/10.x)
- [React 18 Documentation](https://react.dev/)
- [Google Maps JavaScript API](https://developers.google.com/maps/documentation/javascript)
- [AWS S3 Documentation](https://docs.aws.amazon.com/s3/)

### 用語集
- **SPA**: Single Page Application - 単一ページで動作するWebアプリケーション
- **REST**: Representational State Transfer - Webサービスの設計原則
- **CRUD**: Create, Read, Update, Delete - データ操作の基本4操作
- **ORM**: Object-Relational Mapping - オブジェクトとリレーショナルデータベースの変換
- **CDN**: Content Delivery Network - コンテンツ配信ネットワーク
- **JWT**: JSON Web Token - 認証情報を安全に伝送するトークン形式
- **CORS**: Cross-Origin Resource Sharing - 異なるオリジン間でのリソース共有
- **CSP**: Content Security Policy - XSS攻撃を防ぐセキュリティポリシー

### ライセンス
このプロジェクトはMITライセンスの下で公開されています。

### 変更履歴
| バージョン | 日付 | 変更内容 |
|-----------|------|----------|
| 1.0.0 | 2024-12-01 | 初版作成 |
| 1.1.0 | 2024-12-15 | AI機能追加 |
| 1.2.0 | 2025-01-01 | SNS機能強化 |

---

## 🔧 開発ガイドライン

### コーディング規約

#### PHP/Laravel
```php
<?php
// クラス名: PascalCase
class TripController extends Controller
{
    // メソッド名: camelCase
    public function createTrip(CreateTripRequest $request): JsonResponse
    {
        // 変数名: camelCase
        $tripData = $request->validated();
        
        // 定数: SNAKE_CASE
        const MAX_PHOTOS_PER_TRIP = 50;
        
        return response()->json([
            'success' => true,
            'data' => $trip
        ]);
    }
}
```

#### JavaScript/React
```javascript
// コンポーネント名: PascalCase
const TripCard = ({ trip, onLike }) => {
  // 変数名: camelCase
  const [isLiked, setIsLiked] = useState(false);
  
  // 定数: SCREAMING_SNAKE_CASE
  const MAX_TITLE_LENGTH = 100;
  
  // 関数名: camelCase
  const handleLikeClick = async () => {
    try {
      await likeTrip(trip.id);
      setIsLiked(!isLiked);
      onLike?.(trip.id);
    } catch (error) {
      console.error('Failed to like trip:', error);
    }
  };

  return (
    <div className="trip-card">
      <h3 className="trip-title">{trip.title}</h3>
      <button onClick={handleLikeClick}>
        {isLiked ? '❤️' : '🤍'} {trip.likes_count}
      </button>
    </div>
  );
};
```

### Git コミットメッセージ規約
```
<type>(<scope>): <subject>

<body>

<footer>
```

#### Type
- `feat`: 新機能
- `fix`: バグ修正
- `docs`: ドキュメント更新
- `style`: コードフォーマット
- `refactor`: リファクタリング
- `test`: テスト追加・修正
- `chore`: ビルド・設定変更

#### 例
```
feat(trip): add photo upload functionality

- Implement drag & drop photo upload
- Add image compression before upload
- Support multiple file selection

Closes #123
```

### テスト戦略

#### バックエンドテスト
```php
<?php
// tests/Feature/TripTest.php
class TripTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_trip(): void
    {
        $user = User::factory()->create();
        $tripData = [
            'title' => 'Test Trip',
            'description' => 'Test Description',
            'start_date' => '2024-12-01',
            'end_date' => '2024-12-05',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/trips', $tripData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description'
                ]
            ]);
    }

    public function test_unauthorized_user_cannot_create_trip(): void
    {
        $tripData = ['title' => 'Test Trip'];
        
        $response = $this->postJson('/api/trips', $tripData);
        
        $response->assertStatus(401);
    }
}
```

#### フロントエンドテスト
```javascript
// src/components/__tests__/TripCard.test.tsx
import { render, screen, fireEvent } from '@testing-library/react';
import { TripCard } from '../TripCard';

describe('TripCard', () => {
  const mockTrip = {
    id: 1,
    title: 'Test Trip',
    likes_count: 5,
    user: { name: 'Test User' }
  };

  test('renders trip information correctly', () => {
    render(<TripCard trip={mockTrip} />);
    
    expect(screen.getByText('Test Trip')).toBeInTheDocument();
    expect(screen.getByText('Test User')).toBeInTheDocument();
    expect(screen.getByText('5')).toBeInTheDocument();
  });

  test('calls onLike when like button is clicked', () => {
    const mockOnLike = jest.fn();
    render(<TripCard trip={mockTrip} onLike={mockOnLike} />);
    
    const likeButton = screen.getByRole('button');
    fireEvent.click(likeButton);
    
    expect(mockOnLike).toHaveBeenCalledWith(1);
  });
});
```

### パフォーマンス最適化

#### データベース最適化
```sql
-- インデックス最適化
CREATE INDEX idx_trips_user_public ON trips(user_id, is_public);
CREATE INDEX idx_trips_dates ON trips(start_date, end_date);
CREATE INDEX idx_photos_trip ON photos(trip_id);

-- クエリ最適化例
SELECT t.*, u.name as user_name, COUNT(l.id) as likes_count
FROM trips t
LEFT JOIN users u ON t.user_id = u.id
LEFT JOIN likes l ON t.id = l.trip_id
WHERE t.is_public = 1
GROUP BY t.id
ORDER BY t.created_at DESC
LIMIT 20;
```

#### Laravel最適化
```php
<?php
// Eager Loading で N+1 問題を解決
public function index()
{
    $trips = Trip::with(['user', 'photos', 'tags'])
        ->where('is_public', true)
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    
    return TripResource::collection($trips);
}

// キャッシュ利用
public function popularTrips()
{
    return Cache::remember('popular_trips', 3600, function () {
        return Trip::withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->limit(10)
            ->get();
    });
}
```

#### React最適化
```javascript
// メモ化でパフォーマンス向上
const TripCard = memo(({ trip, onLike }) => {
  // コンポーネント実装
});

// 仮想化で大量データ表示を最適化
import { FixedSizeList as List } from 'react-window';

const TripList = ({ trips }) => (
  <List
    height={600}
    itemCount={trips.length}
    itemSize={200}
    itemData={trips}
  >
    {({ index, style, data }) => (
      <div style={style}>
        <TripCard trip={data[index]} />
      </div>
    )}
  </List>
);
```

### セキュリティチェックリスト

#### 認証・認可
- [ ] パスワードの適切なハッシュ化
- [ ] セッショントークンの安全な管理
- [ ] 認可チェックの実装
- [ ] レート制限の設定

#### 入力値検証
- [ ] SQLインジェクション対策
- [ ] XSS対策
- [ ] CSRF対策
- [ ] ファイルアップロード検証

#### データ保護
- [ ] 個人情報の暗号化
- [ ] HTTPS通信の強制
- [ ] セキュリティヘッダーの設定
- [ ] ログの適切な管理

### 運用監視

#### ログ設定
```php
<?php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'error',
    ],
],
```

#### メトリクス収集
```javascript
// フロントエンド パフォーマンス計測
const measurePageLoad = () => {
  window.addEventListener('load', () => {
    const navigation = performance.getEntriesByType('navigation')[0];
    const loadTime = navigation.loadEventEnd - navigation.fetchStart;
    
    // Google Analytics や独自分析ツールに送信
    gtag('event', 'page_load_time', {
      value: Math.round(loadTime),
      custom_parameter: window.location.pathname
    });
  });
};
```

### トラブルシューティング

#### よくある問題と解決法

1. **画像アップロードが失敗する**
   ```bash
   # S3権限確認
   aws s3 ls s3://travel-memory-images
   
   # Laravelログ確認
   tail -f storage/logs/laravel.log
   ```

2. **地図が表示されない**
   ```javascript
   // Google Maps API Key確認
   console.log('API Key:', process.env.REACT_APP_GOOGLE_MAPS_API_KEY);
   
   // ブラウザコンソールでエラー確認
   ```

3. **API応答が遅い**
   ```bash
   # データベースクエリ分析
   php artisan telescope:clear
   php artisan telescope:prune --hours=1
   ```

4. **認証エラー**
   ```bash
   # Laravel Sanctumトークン確認
   php artisan sanctum:prune-expired
   ```

---

## 📊 パフォーマンスメトリクス目標

### レスポンス時間目標
| エンドポイント | 目標時間 | 現在値 | 状態 |
|---------------|----------|---------|------|
| GET /api/trips | < 300ms | 250ms | ✅ |
| POST /api/trips | < 500ms | 450ms | ✅ |
| GET /api/trips/{id} | < 200ms | 180ms | ✅ |
| POST /api/photos | < 2s | 1.5s | ✅ |

### フロントエンド メトリクス
| 指標 | 目標値 | 測定方法 |
|------|--------|----------|
| First Contentful Paint | < 1.5s | Lighthouse |
| Largest Contentful Paint | < 2.5s | Lighthouse |
| Cumulative Layout Shift | < 0.1 | Lighthouse |
| Time to Interactive | < 3s | Lighthouse |

---

## 🎯 今後の拡張計画

### Phase 5: モバイルアプリ開発
- React Native での iOS/Android アプリ開発
- プッシュ通知機能
- オフライン対応

### Phase 6: 高度なAI機能
- 画像認識による自動タグ付け
- 旅行ルート最適化AI
- パーソナライズされた旅行提案

### Phase 7: コミュニティ機能
- ユーザー間メッセージング
- グループ旅行計画機能
- 旅行仲間マッチング

### Phase 8: 収益化
- プレミアムプラン（高品質画像保存、詳細分析）
- 旅行関連商品のアフィリエイト
- 広告配信システム

---

*この仕様書は継続的に更新され、プロジェクトの進行と共に詳細化されます。*