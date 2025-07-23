# GCPデプロイ設計書

## 1. 概要

本ドキュメントは、TraMemoプロジェクトをGoogle Cloud Platform (GCP)にデプロイするための設計・実装フローをまとめたものです。Laravelアプリケーションの本番環境構築を対象とします。

**採用サービス**:
- **コンピューティング**: Cloud Run
- **データベース**: Cloud SQL (MySQL)
- **ストレージ**: Cloud Storage
- **ロードバランサー**: Cloud Load Balancing
- **CDN**: Cloud CDN
- **監視**: Cloud Monitoring

---

## 2. アーキテクチャ概要

### 2.1 システム構成図

```
[ユーザー] → [Cloud CDN] → [Cloud Load Balancing] → [Cloud Run]
                                                    ↓
[Cloud Storage] ← [Cloud Functions] ← [Cloud SQL (MySQL)]
```

### 2.2 採用サービス詳細

| サービス | 用途 | 料金（概算） |
|----------|------|-------------|
| Cloud Run | Laravelアプリケーション実行 | 月200万リクエスト無料 |
| Cloud SQL | MySQLデータベース | 月1GB無料 |
| Cloud Storage | 画像ファイル保存 | 月5GB無料 |
| Cloud CDN | 静的ファイル配信 | 月1GB無料 |
| Cloud Load Balancing | ロードバランサー | 月5ルール無料 |

---

## 3. 環境設定

### 3.1 プロジェクト構成

```
TraMemo_prot_backend/
├── src/                    # Laravelアプリケーション
├── infra/
│   ├── gcp/               # GCP設定ファイル
│   │   ├── terraform/     # Terraform設定
│   │   ├── docker/        # Docker設定
│   │   └── scripts/       # デプロイスクリプト
│   └── docker-compose.yml # 開発環境用
└── 設計書/
```

### 3.2 環境変数設定

#### 本番環境（Cloud Run）
```env
# アプリケーション設定
APP_NAME=TraMemo
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# データベース設定
DB_CONNECTION=mysql
DB_HOST=/cloudsql/your-project:asia-northeast1:traMemo-db
DB_PORT=3306
DB_DATABASE=traMemo_production
DB_USERNAME=traMemo_user
DB_PASSWORD=your-db-password

# GCP設定
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE=/secrets/google-cloud-key.json
GOOGLE_CLOUD_STORAGE_BUCKET=traMemo-images
GOOGLE_CLOUD_STORAGE_URL=https://storage.googleapis.com/traMemo-images

# Clerk認証設定
CLERK_SECRET_KEY=your-clerk-secret-key
CLERK_PUBLISHABLE_KEY=your-clerk-publishable-key

# キャッシュ設定
CACHE_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

# セッション設定
SESSION_DRIVER=redis
SESSION_LIFETIME=120
```

#### 開発環境
```env
# アプリケーション設定
APP_NAME=TraMemo
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

# データベース設定
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=traMemo_development
DB_USERNAME=traMemo_user
DB_PASSWORD=traMemo_password

# GCP設定（開発用）
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE=storage/app/google-cloud-key.json
GOOGLE_CLOUD_STORAGE_BUCKET=traMemo-images-dev
GOOGLE_CLOUD_STORAGE_URL=https://storage.googleapis.com/traMemo-images-dev

# Clerk認証設定
CLERK_SECRET_KEY=your-clerk-secret-key
CLERK_PUBLISHABLE_KEY=your-clerk-publishable-key
```

---

## 4. Docker設定

### 4.1 Dockerfile

```dockerfile
# infra/gcp/docker/Dockerfile
FROM php:8.2-fpm

# システムパッケージのインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリの設定
WORKDIR /var/www/html

# アプリケーションファイルのコピー
COPY src/ .

# 依存関係のインストール
RUN composer install --optimize-autoloader --no-dev

# 権限の設定
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# 環境変数の設定
ENV APP_ENV=production
ENV APP_DEBUG=false

# ポートの公開
EXPOSE 8080

# 起動コマンド
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
```

### 4.2 .dockerignore

```dockerignore
# infra/gcp/docker/.dockerignore
.git
.gitignore
README.md
.env
.env.example
node_modules
vendor
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
tests
phpunit.xml
.dockerignore
Dockerfile
docker-compose.yml
```

---

## 5. Terraform設定

### 5.1 メイン設定

```hcl
# infra/gcp/terraform/main.tf
terraform {
  required_version = ">= 1.0"
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "~> 4.0"
    }
  }
}

provider "google" {
  project = var.project_id
  region  = var.region
}

# Cloud SQL (MySQL)
resource "google_sql_database_instance" "main" {
  name             = "traMemo-db"
  database_version = "MYSQL_8_0"
  region           = var.region

  settings {
    tier = "db-f1-micro"
    
    backup_configuration {
      enabled    = true
      start_time = "02:00"
    }
    
    ip_configuration {
      ipv4_enabled    = true
      require_ssl     = true
      authorized_networks {
        name  = "all"
        value = "0.0.0.0/0"
      }
    }
  }

  deletion_protection = false
}

resource "google_sql_database" "database" {
  name     = "traMemo_production"
  instance = google_sql_database_instance.main.name
}

resource "google_sql_user" "users" {
  name     = "traMemo_user"
  instance = google_sql_database_instance.main.name
  password = var.db_password
}

# Cloud Storage
resource "google_storage_bucket" "images" {
  name          = "traMemo-images"
  location      = var.region
  force_destroy = false

  uniform_bucket_level_access = true

  lifecycle_rule {
    condition {
      age = 365
    }
    action {
      type = "Delete"
    }
  }
}

# Cloud Run
resource "google_cloud_run_service" "main" {
  name     = "traMemo-api"
  location = var.region

  template {
    spec {
      containers {
        image = var.container_image
        
        env {
          name  = "APP_ENV"
          value = "production"
        }
        
        env {
          name  = "DB_HOST"
          value = "/cloudsql/${google_sql_database_instance.main.connection_name}"
        }
        
        env {
          name  = "DB_DATABASE"
          value = google_sql_database.database.name
        }
        
        env {
          name  = "DB_USERNAME"
          value = google_sql_user.users.name
        }
        
        env {
          name  = "DB_PASSWORD"
          value = var.db_password
        }
        
        env {
          name  = "GOOGLE_CLOUD_STORAGE_BUCKET"
          value = google_storage_bucket.images.name
        }
      }
    }
  }

  traffic {
    percent         = 100
    latest_revision = true
  }
}

# IAM for Cloud Run
resource "google_cloud_run_service_iam_member" "public" {
  location = google_cloud_run_service.main.location
  service  = google_cloud_run_service.main.name
  role     = "roles/run.invoker"
  member   = "allUsers"
}

# Cloud Load Balancing
resource "google_compute_global_address" "default" {
  name = "traMemo-ip"
}

resource "google_compute_global_forwarding_rule" "default" {
  name       = "traMemo-forwarding-rule"
  target     = google_compute_target_https_proxy.default.id
  port_range = "443"
  ip_address = google_compute_global_address.default.address
}

resource "google_compute_target_https_proxy" "default" {
  name             = "traMemo-https-proxy"
  url_map          = google_compute_url_map.default.id
  ssl_certificates = [google_compute_managed_ssl_certificate.default.id]
}

resource "google_compute_url_map" "default" {
  name            = "traMemo-url-map"
  default_service = google_compute_backend_service.default.id
}

resource "google_compute_backend_service" "default" {
  name        = "traMemo-backend"
  protocol    = "HTTP"
  port_name   = "http"
  timeout_sec = 30

  backend {
    group = google_compute_region_network_endpoint_group.cloudrun.id
  }
}

resource "google_compute_region_network_endpoint_group" "cloudrun" {
  name                  = "traMemo-neg"
  region                = var.region
  network_endpoint_type = "SERVERLESS"

  cloud_run {
    service = google_cloud_run_service.main.name
  }
}

resource "google_compute_managed_ssl_certificate" "default" {
  name = "traMemo-ssl-cert"

  managed {
    domains = [var.domain]
  }
}
```

### 5.2 変数定義

```hcl
# infra/gcp/terraform/variables.tf
variable "project_id" {
  description = "GCP Project ID"
  type        = string
}

variable "region" {
  description = "GCP Region"
  type        = string
  default     = "asia-northeast1"
}

variable "domain" {
  description = "Domain name for SSL certificate"
  type        = string
}

variable "container_image" {
  description = "Container image URL"
  type        = string
}

variable "db_password" {
  description = "Database password"
  type        = string
  sensitive   = true
}
```

### 5.3 出力定義

```hcl
# infra/gcp/terraform/outputs.tf
output "cloud_run_url" {
  description = "Cloud Run service URL"
  value       = google_cloud_run_service.main.status[0].url
}

output "database_connection_name" {
  description = "Cloud SQL connection name"
  value       = google_sql_database_instance.main.connection_name
}

output "storage_bucket" {
  description = "Cloud Storage bucket name"
  value       = google_storage_bucket.images.name
}

output "load_balancer_ip" {
  description = "Load balancer IP address"
  value       = google_compute_global_address.default.address
}
```

---

## 6. デプロイスクリプト

### 6.1 デプロイスクリプト

```bash
#!/bin/bash
# infra/gcp/scripts/deploy.sh

set -e

# 環境変数の読み込み
source .env

# 変数設定
PROJECT_ID="your-project-id"
REGION="asia-northeast1"
SERVICE_NAME="traMemo-api"
IMAGE_NAME="gcr.io/${PROJECT_ID}/${SERVICE_NAME}"

echo "🚀 TraMemo デプロイ開始..."

# 1. Dockerイメージのビルド
echo "📦 Dockerイメージをビルド中..."
docker build -t ${IMAGE_NAME} -f infra/gcp/docker/Dockerfile .

# 2. GCRにプッシュ
echo "⬆️ GCRにプッシュ中..."
docker push ${IMAGE_NAME}

# 3. Terraformの初期化
echo "🔧 Terraformを初期化中..."
cd infra/gcp/terraform
terraform init

# 4. Terraformの実行
echo "🏗️ インフラを構築中..."
terraform apply \
  -var="project_id=${PROJECT_ID}" \
  -var="region=${REGION}" \
  -var="domain=${DOMAIN}" \
  -var="container_image=${IMAGE_NAME}" \
  -var="db_password=${DB_PASSWORD}" \
  -auto-approve

# 5. データベースマイグレーション
echo "🗄️ データベースマイグレーション実行中..."
gcloud run jobs create migrate \
  --image=${IMAGE_NAME} \
  --command="php" \
  --args="artisan,migrate,--force" \
  --region=${REGION} \
  --set-cloudsql-instances=${PROJECT_ID}:${REGION}:traMemo-db

# 6. デプロイ完了
echo "✅ デプロイ完了！"
echo "🌐 アプリケーションURL: $(terraform output -raw cloud_run_url)"
echo "🗄️ データベース接続名: $(terraform output -raw database_connection_name)"
echo "📦 ストレージバケット: $(terraform output -raw storage_bucket)"
```

### 6.2 環境構築スクリプト

```bash
#!/bin/bash
# infra/gcp/scripts/setup.sh

set -e

echo "🔧 GCP環境構築開始..."

# 1. gcloud CLIの設定
echo "📋 gcloud CLIを設定中..."
gcloud config set project ${PROJECT_ID}
gcloud config set compute/region ${REGION}

# 2. 必要なAPIの有効化
echo "🔌 必要なAPIを有効化中..."
gcloud services enable \
  cloudbuild.googleapis.com \
  run.googleapis.com \
  sqladmin.googleapis.com \
  storage.googleapis.com \
  compute.googleapis.com \
  cloudresourcemanager.googleapis.com

# 3. サービスアカウントの作成
echo "👤 サービスアカウントを作成中..."
gcloud iam service-accounts create traMemo-deployer \
  --display-name="TraMemo Deployer"

# 4. 権限の付与
echo "🔑 権限を付与中..."
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
  --member="serviceAccount:traMemo-deployer@${PROJECT_ID}.iam.gserviceaccount.com" \
  --role="roles/cloudbuild.builds.builder"

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
  --member="serviceAccount:traMemo-deployer@${PROJECT_ID}.iam.gserviceaccount.com" \
  --role="roles/run.admin"

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
  --member="serviceAccount:traMemo-deployer@${PROJECT_ID}.iam.gserviceaccount.com" \
  --role="roles/storage.admin"

# 5. サービスアカウントキーの作成
echo "🔐 サービスアカウントキーを作成中..."
gcloud iam service-accounts keys create \
  infra/gcp/keys/deployer-key.json \
  --iam-account=traMemo-deployer@${PROJECT_ID}.iam.gserviceaccount.com

echo "✅ 環境構築完了！"
```

---

## 7. CI/CD設定

### 7.1 GitHub Actions

```yaml
# .github/workflows/deploy.yml
name: Deploy to GCP

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

env:
  PROJECT_ID: ${{ secrets.GCP_PROJECT_ID }}
  REGION: asia-northeast1
  SERVICE_NAME: traMemo-api

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: traMemo_testing
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 3306:3306

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, gd, zip
        coverage: xdebug
    
    - name: Install dependencies
      run: |
        cd src
        composer install --prefer-dist --no-progress
    
    - name: Copy environment file
      run: |
        cd src
        cp .env.example .env
    
    - name: Generate app key
      run: |
        cd src
        php artisan key:generate
    
    - name: Run tests
      run: |
        cd src
        php artisan test

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup Google Cloud CLI
      uses: google-github-actions/setup-gcloud@v0
      with:
        project_id: ${{ secrets.GCP_PROJECT_ID }}
        service_account_key: ${{ secrets.GCP_SA_KEY }}
        export_default_credentials: true
    
    - name: Configure Docker for GCR
      run: gcloud auth configure-docker
    
    - name: Build and push Docker image
      run: |
        docker build -t gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ env.SERVICE_NAME }}:${{ github.sha }} -f infra/gcp/docker/Dockerfile .
        docker push gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ env.SERVICE_NAME }}:${{ github.sha }}
    
    - name: Deploy to Cloud Run
      run: |
        gcloud run deploy ${{ env.SERVICE_NAME }} \
          --image gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ env.SERVICE_NAME }}:${{ github.sha }} \
          --region ${{ env.REGION }} \
          --platform managed \
          --allow-unauthenticated \
          --set-env-vars="APP_ENV=production" \
          --set-env-vars="DB_HOST=/cloudsql/${{ secrets.GCP_PROJECT_ID }}:${{ env.REGION }}:traMemo-db" \
          --set-env-vars="DB_DATABASE=traMemo_production" \
          --set-env-vars="DB_USERNAME=traMemo_user" \
          --set-env-vars="DB_PASSWORD=${{ secrets.DB_PASSWORD }}" \
          --set-env-vars="GOOGLE_CLOUD_STORAGE_BUCKET=traMemo-images" \
          --set-env-vars="CLERK_SECRET_KEY=${{ secrets.CLERK_SECRET_KEY }}" \
          --set-env-vars="CLERK_PUBLISHABLE_KEY=${{ secrets.CLERK_PUBLISHABLE_KEY }}"
    
    - name: Run database migrations
      run: |
        gcloud run jobs create migrate-${{ github.sha }} \
          --image gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ env.SERVICE_NAME }}:${{ github.sha }} \
          --command="php" \
          --args="artisan,migrate,--force" \
          --region=${{ env.REGION }} \
          --set-cloudsql-instances=${{ secrets.GCP_PROJECT_ID }}:${{ env.REGION }}:traMemo-db
```

---

## 8. 監視・ログ設定

### 8.1 Cloud Monitoring

```yaml
# infra/gcp/monitoring/alerting-policy.yaml
displayName: "TraMemo API Error Rate"
conditions:
  - displayName: "Error rate is high"
    conditionThreshold:
      filter: 'resource.type="cloud_run_revision" AND resource.labels.service_name="traMemo-api"'
      comparison: COMPARISON_GREATER_THAN
      thresholdValue: 0.05
      duration: 300s
      aggregations:
        - alignmentPeriod: 60s
          perSeriesAligner: ALIGN_RATE
          crossSeriesReducer: REDUCE_MEAN
          groupByFields:
            - resource.labels.revision_name
```

### 8.2 ログ設定

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'gcp'],
        'ignore_exceptions' => false,
    ],
    
    'gcp' => [
        'driver' => 'monolog',
        'level' => env('LOG_LEVEL', 'debug'),
        'handler' => Monolog\Handler\SyslogHandler::class,
        'formatter' => Monolog\Formatter\JsonFormatter::class,
    ],
],
```

---

## 9. セキュリティ設定

### 9.1 シークレット管理

```bash
# シークレットの作成
echo -n "your-db-password" | gcloud secrets create db-password --data-file=-
echo -n "your-app-key" | gcloud secrets create app-key --data-file=-
echo -n "your-clerk-secret" | gcloud secrets create clerk-secret --data-file=-
```

### 9.2 ネットワークセキュリティ

```hcl
# VPC設定
resource "google_compute_network" "vpc" {
  name                    = "traMemo-vpc"
  auto_create_subnetworks = false
}

resource "google_compute_subnetwork" "subnet" {
  name          = "traMemo-subnet"
  ip_cidr_range = "10.0.0.0/24"
  network       = google_compute_network.vpc.id
  region        = var.region
}
```

---

## 10. バックアップ・復旧

### 10.1 データベースバックアップ

```bash
#!/bin/bash
# infra/gcp/scripts/backup.sh

# 自動バックアップ設定
gcloud sql instances patch traMemo-db \
  --backup-start-time="02:00" \
  --backup-retention-days=7 \
  --enable-bin-log

# 手動バックアップ
gcloud sql export sql traMemo-db \
  gs://traMemo-backups/backup-$(date +%Y%m%d-%H%M%S).sql \
  --database=traMemo_production
```

### 10.2 ストレージバックアップ

```bash
# ストレージバックアップ
gsutil cp -r gs://traMemo-images gs://traMemo-backups/images-$(date +%Y%m%d)
```

---

## 11. パフォーマンス最適化

### 11.1 Cloud Run設定

```yaml
# Cloud Run最適化設定
spec:
  template:
    metadata:
      annotations:
        autoscaling.knative.dev/minScale: "1"
        autoscaling.knative.dev/maxScale: "10"
        autoscaling.knative.dev/target: "80"
    spec:
      containerConcurrency: 80
      timeoutSeconds: 300
      resources:
        limits:
          cpu: "1000m"
          memory: "512Mi"
```

### 11.2 キャッシュ設定

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],
```

---

## 12. トラブルシューティング

### 12.1 よくある問題と解決方法

| 問題 | 原因 | 解決方法 |
|------|------|----------|
| データベース接続エラー | Cloud SQL接続設定 | 接続名の確認、IAM権限の確認 |
| ストレージアクセスエラー | サービスアカウント権限 | Storage Object Admin権限の付与 |
| アプリケーション起動エラー | 環境変数不足 | 必須環境変数の設定確認 |
| パフォーマンス低下 | リソース不足 | Cloud Runのリソース増加 |

### 12.2 ログ確認方法

```bash
# Cloud Runログの確認
gcloud logging read "resource.type=cloud_run_revision AND resource.labels.service_name=traMemo-api" --limit=50

# データベースログの確認
gcloud sql logs tail traMemo-db
```

---

## 13. 参考リンク

- [Google Cloud Run公式ドキュメント](https://cloud.google.com/run/docs)
- [Cloud SQL公式ドキュメント](https://cloud.google.com/sql/docs)
- [Terraform Google Provider](https://registry.terraform.io/providers/hashicorp/google/latest/docs)
- [Laravel on Google Cloud](https://laravel.com/docs/10.x/deployment#google-cloud-run)

--- 

---

## 14. サービス公開後のリソース消費と無料枠の許容範囲

### 14.1 Cloud Run（APIサーバー）
- **消費アクション例**
  - APIエンドポイントへのリクエスト（Web/アプリ/外部サービスからのアクセス）
  - バッチ処理やWebhook受信
  - ヘルスチェックや監視ツールによる自動アクセス
- **リソース消費内容**
  - リクエスト数
  - CPU/メモリ使用時間（リクエスト処理中のみ課金）
  - アウトバウンド通信量（外部へのデータ送信）
- **無料枠の許容範囲**
  - 200万リクエスト/月
  - 360,000 vCPU秒/月
  - 180,000 GiB秒/月
  - 2GBのアウトバウンド通信/月（北米・欧州宛て）

---

### 14.2 Cloud SQL（MySQL）
- **消費アクション例**
  - データの登録・更新・削除・検索（APIや管理画面、バッチ処理等）
  - バックアップや自動メンテナンス
  - 外部からのDB接続
- **リソース消費内容**
  - インスタンス稼働時間
  - ストレージ使用量
  - バックアップストレージ
  - ネットワーク転送量（外部接続時）
- **無料枠の許容範囲**
  - db-f1-microインスタンス1台分の稼働（USリージョンのみ、asia-northeast1は課金対象の可能性あり）
  - 30GBのストレージ（USリージョンのみ）
  - 1GBのバックアップストレージ

---

### 14.3 Cloud Storage（画像・静的ファイル）
- **消費アクション例**
  - 画像やファイルのアップロード・ダウンロード・削除
  - フロントエンド静的ファイルの配信
  - バックアップやログの保存
- **リソース消費内容**
  - 保存容量
  - オペレーション数（PUT/GET/DELETE等）
  - アウトバウンド転送量
- **無料枠の許容範囲**
  - 5GBのストレージ
  - 5,000回/月のClass Aオペレーション（アップロード等）
  - 50,000回/月のClass Bオペレーション（ダウンロード等）
  - 1GBのネットワーク転送（北米宛て）

---

### 14.4 Cloud CDN（フロント配信）
- **消費アクション例**
  - フロントエンドサイトへのアクセス
  - 静的ファイルのキャッシュ・配信
- **リソース消費内容**
  - キャッシュヒット/ミス時の転送量
  - キャッシュストレージ使用量
  - リクエスト数
- **無料枠の許容範囲**
  - 1GB/月のキャッシュ転送量（北米・欧州宛て）
  - 1,000,000リクエスト/月

---

### 14.5 その他（Secret Manager, Monitoring, Logging, IAM等）
- **消費アクション例**
  - シークレットの保存・取得
  - ログ保存・監視データ送信
- **リソース消費内容**
  - シークレット数・取得回数
  - ログ保存量・監視データ量
- **無料枠の許容範囲**
  - 各サービスごとに無料枠あり（詳細は公式ドキュメント参照）

---

### 14.6 無料枠を超過する主なアクション例
- APIが大量に叩かれる（Bot/DoS/人気化）
- 画像・動画など大容量ファイルの大量アップロード/ダウンロード
- フロントエンドにアクセス集中
- バックエンドでバッチ処理や定期ジョブが頻繁に動作
- 外部サービス連携で大量のデータ送受信
- Cloud SQLに大容量データが蓄積

---

### 14.7 運用上の注意・対策
- コストアラート・モニタリングを必ず設定
- Cloud Run最小インスタンス数0、最大数制限
- Cloud Storageのライフサイクル・バージョニング無効化
- Cloud SQLの自動停止・ストレージ最小化
- CDNキャッシュ最適化・不要な転送抑制
- 不要なリソースは即時削除

---

### 14.8 まとめ
- 小規模なポートフォリオ・個人開発・検証用途なら、Cloud Run/Cloud SQL/Cloud Storage/Cloud CDNの無料枠内で月額ほぼ0円で運用可能。
- アクセス増加や大量データ保存・配信が発生すると、各サービスの無料枠を超過し課金される。
- Cloud SQLの無料枠はリージョン制限（USのみ）に注意。asia-northeast1では課金対象になる場合あり。

---

※最新の無料枠・料金体系は[Google Cloud公式ドキュメント](https://cloud.google.com/free)も参照してください。 

---

### 14.9 サービスアカウント設計

#### サービスアカウントの役割
- Cloud Run、Cloud Run Job、Cloud Functions等のGCPサービスがリソースへ安全にアクセスするための「実行主体」として利用します。
- 本番運用では、用途ごとに専用のサービスアカウントを作成し、必要最小限の権限のみを付与するのがベストプラクティスです。

#### 作成・命名例
- 例: `tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com`

#### 必要な権限（ロール）
| ロール名 | 用途・役割 |
|----------|------------|
| roles/cloudsql.client | Cloud SQLへの接続 |
| roles/secretmanager.secretAccessor | Secret Managerの値参照 |
| roles/storage.objectViewer | Cloud Storageのオブジェクト参照（画像等の読み取り） |
| roles/storage.objectCreator | Cloud Storageへの書き込み（画像アップロード等が必要な場合） |
| roles/logging.logWriter | Cloud Loggingへの書き込み（必要に応じて） |

#### サービスアカウントの作成・権限付与例
```bash
# サービスアカウント作成
 gcloud iam service-accounts create tramemo-app --display-name="TraMemo App Service Account"

# 権限付与
 gcloud projects add-iam-policy-binding <PROJECT_ID> \
   --member="serviceAccount:tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com" \
   --role="roles/cloudsql.client"
 gcloud projects add-iam-policy-binding <PROJECT_ID> \
   --member="serviceAccount:tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com" \
   --role="roles/secretmanager.secretAccessor"
 gcloud projects add-iam-policy-binding <PROJECT_ID> \
   --member="serviceAccount:tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com" \
   --role="roles/storage.objectViewer"
 # 書き込みも必要な場合
 gcloud projects add-iam-policy-binding <PROJECT_ID> \
   --member="serviceAccount:tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com" \
   --role="roles/storage.objectCreator"
 # Cloud Loggingも必要な場合
 gcloud projects add-iam-policy-binding <PROJECT_ID> \
   --member="serviceAccount:tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com" \
   --role="roles/logging.logWriter"
```

#### Cloud Run/Jobへの紐付け例
- Cloud RunやJobのデプロイ時に `--service-account=tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com` を指定してください。

```bash
gcloud run deploy traMemo-api \
  ... \
  --service-account=tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com

gcloud run jobs create migrate \
  ... \
  --service-account=tramemo-app@<PROJECT_ID>.iam.gserviceaccount.com
```

#### セキュリティ・運用上の注意
- サービスアカウントのキー（JSON）は原則不要です（gcloudやTerraform等の自動化用には必要な場合あり）。
- 最小権限の原則を守り、不要なロールは付与しない。
- サービスアカウントの利用状況を定期的に監査する。

--- 

---

## 15. GCPで管理するLaravel環境変数の整理（リファクタ・追加版）

### 15.1 Cloud Runの環境変数で管理する値
| 変数名                        | 用途・役割                        | 取得・設定方法例                                      |
|-------------------------------|------------------------------------|------------------------------------------------------|
| APP_ENV                       | 環境名（例: production）           | 固定値 "production"                                  |
| APP_URL                       | サイトのURL                        | 公開URLを指定                                        |
| DB_HOST                       | Cloud SQLのUNIXソケット接続名      | `/cloudsql/プロジェクトID:リージョン:DB名` 形式       |
| DB_DATABASE                   | DB名                               | Cloud SQL作成時の値                                  |
| DB_USERNAME                   | DBユーザー名                       | Cloud SQL作成時の値                                  |
| GOOGLE_CLOUD_PROJECT_ID       | GCPプロジェクトID                  | `gcloud config get-value project` で取得             |
| GOOGLE_CLOUD_STORAGE_BUCKET   | Cloud Storageバケット名            | GCPコンソールで確認                                  |
| GOOGLE_CLOUD_STORAGE_URL      | バケットの公開URL                  | `https://storage.googleapis.com/<バケット名>`         |
| CLERK_PUBLISHABLE_KEY         | Clerk公開キー                      | Clerk管理画面で取得                                  |
| CLERK_AUTHORIZED_PARTY        | Clerk認証パーティ                  | Clerk管理画面で取得                                  |
| CACHE_DRIVER, SESSION_DRIVER  | キャッシュ/セッション設定          | 必要に応じて指定                                     |
| QUEUE_CONNECTION              | キュー接続設定                     | 必要に応じて指定                                     |
| MAIL_MAILER, MAIL_HOST, ...   | メール送信設定                     | 必要に応じて指定                                     |
| LOG_CHANNEL, LOG_LEVEL        | ログ出力設定                       | 必要に応じて指定                                     |
| SANCTUM_STATEFUL_DOMAINS      | Sanctum用ドメイン設定              | 必要に応じて指定                                     |

### 15.2 Secret Managerで管理しCloud Runにマウントする値（機密情報）
| 変数名                     | 用途・役割              | 取得・設定方法例                        |
|----------------------------|------------------------|----------------------------------------|
| APP_KEY                    | Laravel暗号化キー      | `php artisan key:generate --show`      |
| DB_PASSWORD                | DBパスワード           | Cloud SQL作成時の値                    |
| CLERK_SECRET_KEY           | Clerkシークレットキー  | Clerk管理画面で取得                    |
| CLERK_WEBHOOK_SIGNING_SECRET| Clerk Webhook署名検証用| Clerk管理画面で取得                    |
| AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION | AWS連携用 | 必要に応じて指定                      |
| その他APIキー等            | 外部APIやメール等      | 各サービス管理画面で取得                |

### 15.3 Cloud Run/Secret Managerでの設定例
- Cloud Runの環境変数は `gcloud run deploy` の `--set-env-vars` で指定
- Secret Managerの値はGCPコンソールまたは `gcloud secrets create` で登録し、Cloud Runの「シークレット」機能で環境変数やファイルとしてマウント
- Laravelは `env()` でこれらの値を直接参照可能

#### 例: gcloud run deploy
```bash
gcloud run deploy traMemo-api \
  --image gcr.io/traMemo-project-xxxxx/traMemo-api:latest \
  --region asia-northeast1 \
  --set-env-vars="APP_ENV=production,APP_URL=https://traMemo-api-xxxxx-ew.a.run.app,DB_HOST=/cloudsql/traMemo-project-xxxxx:asia-northeast1:traMemo-db,DB_DATABASE=traMemo_production,DB_USERNAME=traMemo_user,GOOGLE_CLOUD_PROJECT_ID=traMemo-project-xxxxx,GOOGLE_CLOUD_STORAGE_BUCKET=traMemo-images,GOOGLE_CLOUD_STORAGE_URL=https://storage.googleapis.com/traMemo-images,CLERK_PUBLISHABLE_KEY=pk_test_xxx" \
  --set-secrets="APP_KEY=app-key:latest,DB_PASSWORD=db-password:latest,CLERK_SECRET_KEY=clerk-secret-key:latest"
```

--- 