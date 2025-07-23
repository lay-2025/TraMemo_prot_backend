# GCPデプロイ手順書

## 概要

本ドキュメントは、TraMemoプロジェクト（バックエンド: Laravel、フロントエンド: React）をGoogle Cloud Platform (GCP)にデプロイするための詳細な手順を記載しています。

**デプロイ対象**:
- バックエンド: Laravel API（Cloud Run）
- フロントエンド: React SPA（Cloud Storage + Cloud CDN）
- データベース: Cloud SQL (MySQL)
- 画像ストレージ: Cloud Storage

---

## 前提条件

### 必要なツール
- Google Cloud CLI (gcloud)
- Docker
- Terraform
- Git
- Node.js (フロントエンドビルド用)

### 必要なアカウント
- Google Cloud Platform アカウント
- GitHub アカウント（CI/CD用）

---

## フェーズ1: GCP環境構築

### ステップ1: GCPプロジェクト作成

#### 1.1 プロジェクト作成
```bash
# GCPコンソールでプロジェクトを作成
# プロジェクト名: traMemo-project
# プロジェクトID: traMemo-project-xxxxx

# gcloud CLIでプロジェクトを設定
gcloud config set project traMemo-project-xxxxx
gcloud config set compute/region asia-northeast1
```

#### 1.2 必要なAPIの有効化
```bash
# 必要なAPIを有効化
gcloud services enable \
  cloudbuild.googleapis.com \
  run.googleapis.com \
  sqladmin.googleapis.com \
  storage.googleapis.com \
  compute.googleapis.com \
  cloudresourcemanager.googleapis.com \
  secretmanager.googleapis.com \
  monitoring.googleapis.com \
  logging.googleapis.com
```

#### 1.3 請求の有効化
- GCPコンソールで請求を有効化
- クレジットカード情報を登録

### ステップ2: サービスアカウント作成

#### 2.1 デプロイ用サービスアカウント
```bash
# デプロイ用サービスアカウントを作成
gcloud iam service-accounts create traMemo-deployer \
  --display-name="TraMemo Deployer"

# 権限を付与
gcloud projects add-iam-policy-binding traMemo-project-xxxxx \
  --member="serviceAccount:traMemo-deployer@traMemo-project-xxxxx.iam.gserviceaccount.com" \
  --role="roles/cloudbuild.builds.builder"

gcloud projects add-iam-policy-binding traMemo-project-xxxxx \
  --member="serviceAccount:traMemo-deployer@traMemo-project-xxxxx.iam.gserviceaccount.com" \
  --role="roles/run.admin"

gcloud projects add-iam-policy-binding traMemo-project-xxxxx \
  --member="serviceAccount:traMemo-deployer@traMemo-project-xxxxx.iam.gserviceaccount.com" \
  --role="roles/storage.admin"

gcloud projects add-iam-policy-binding traMemo-project-xxxxx \
  --member="serviceAccount:traMemo-deployer@traMemo-project-xxxxx.iam.gserviceaccount.com" \
  --role="roles/secretmanager.admin"
```

#### 2.2 サービスアカウントキーの作成
```bash
# サービスアカウントキーを作成
gcloud iam service-accounts keys create \
  infra/gcp/keys/deployer-key.json \
  --iam-account=traMemo-deployer@traMemo-project-xxxxx.iam.gserviceaccount.com
```

### ステップ3: シークレット管理

#### 3.1 シークレットの作成
```bash
# データベースパスワード
echo -n "your-secure-db-password" | gcloud secrets create db-password --data-file=-

# Laravel App Key
echo -n "base64:your-laravel-app-key" | gcloud secrets create laravel-app-key --data-file=-

# Clerk Secret Key
echo -n "sk_test_your-clerk-secret-key" | gcloud secrets create clerk-secret-key --data-file=-

# Clerk Publishable Key
echo -n "pk_test_your-clerk-publishable-key" | gcloud secrets create clerk-publishable-key --data-file=-
```

---

## フェーズ2: インフラ構築

### ステップ4: Terraform設定

#### 4.1 Terraformディレクトリ作成
```bash
# プロジェクトルートで実行
mkdir -p infra/gcp/terraform
cd infra/gcp/terraform
```

#### 4.2 Terraformファイル作成

**main.tf**
```hcl
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

# Cloud SQL (MySQL) - 無料枠最適化構成
resource "google_sql_database_instance" "main" {
  name             = "tramemo-db"
  database_version = "MYSQL_8_0"
  region           = var.region

  settings {
    tier = "db-f1-micro"  # 最小構成で無料枠内
    
    backup_configuration {
      enabled    = true
      start_time = "02:00"
      # バックアップ保持期間を短縮（コスト削減）
      backup_retention_settings {
        retained_backups = 3
        retention_unit   = "COUNT"
      }
    }
    
    ip_configuration {
      ipv4_enabled    = true
      require_ssl     = true
      authorized_networks {
        name  = "all"
        value = "0.0.0.0/0"
      }
    }
    
    # ディスクサイズを最小化（コスト削減）
    disk_size = 10  # GB
    disk_type = "PD_SSD"
  }

  deletion_protection = false
}

# 開発用DB（自動停止設定）
resource "google_sql_database_instance" "dev" {
  count            = var.environment == "development" ? 1 : 0
  name             = "tramemo-db-dev"
  database_version = "MYSQL_8_0"
  region           = var.region

  settings {
    tier = "db-f1-micro"
    
    # 開発用は自動停止設定
    activation_policy = "NEVER"
    
    backup_configuration {
      enabled = false  # 開発用はバックアップ無効
    }
    
    ip_configuration {
      ipv4_enabled    = true
      require_ssl     = true
      authorized_networks {
        name  = "all"
        value = "0.0.0.0/0"
      }
    }
    
    disk_size = 5  # 開発用は最小サイズ
    disk_type = "PD_SSD"
  }

  deletion_protection = false
}

resource "google_sql_database" "database" {
  name     = "tramemo_production"
  instance = google_sql_database_instance.main.name
}

resource "google_sql_user" "users" {
  name     = "tramemo_user"
  instance = google_sql_database_instance.main.name
  password = var.db_password
}

# Cloud Storage - 無料枠最適化構成
resource "google_storage_bucket" "images" {
  name          = "tramemo-images"
  location      = var.region
  force_destroy = false

  uniform_bucket_level_access = true

  # コスト最適化のためのライフサイクルルール
  lifecycle_rule {
    condition {
      age = 180  # 6ヶ月で削除（コスト削減）
    }
    action {
      type = "Delete"
    }
  }
  
  # バージョニング無効（コスト削減）
  versioning {
    enabled = false
  }
}

# フロントエンド用バケット（静的サイト）
resource "google_storage_bucket" "frontend" {
  name          = "tramemo-frontend"
  location      = var.region
  force_destroy = false

  uniform_bucket_level_access = true
  
  # 静的サイト設定
  website {
    main_page_suffix = "index.html"
    not_found_page   = "404.html"
  }
  
  # フロントエンド用はライフサイクルルールなし（静的ファイル）
}

# Cloud Run - 無料枠最適化構成
resource "google_cloud_run_service" "main" {
  name     = "tramemo-api"
  location = var.region

  template {
    metadata {
      annotations = {
        # 無料枠最適化設定
        "autoscaling.knative.dev/minScale" = "0"   # 最小インスタンス数0（完全停止）
        "autoscaling.knative.dev/maxScale" = "10"  # 最大インスタンス数10以下
        "autoscaling.knative.dev/target"   = "80"  # CPU使用率80%でスケール
      }
    }
    
    spec {
      container_concurrency = 80  # 同時接続数80
      timeout_seconds        = 300 # タイムアウト300秒
      
      containers {
        image = var.container_image
        
        resources {
          limits = {
            cpu    = "1000m"  # 1 vCPU
            memory = "512Mi"  # 512MB RAM（最小構成）
          }
        }
        
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
```

**variables.tf**
```hcl
variable "project_id" {
  description = "GCP Project ID"
  type        = string
  default     = "traMemo-project-xxxxx"
}

variable "region" {
  description = "GCP Region"
  type        = string
  default     = "asia-northeast1"
}

variable "environment" {
  description = "Environment (production/development)"
  type        = string
  default     = "production"
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

variable "enable_cost_optimization" {
  description = "Enable cost optimization features"
  type        = bool
  default     = true
}
```

**outputs.tf**
```hcl
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
```

#### 4.3 Terraform実行（無料枠最適化）
```bash
# Terraform初期化
terraform init

# プラン確認（無料枠最適化設定）
terraform plan \
  -var="container_image=gcr.io/traMemo-project-xxxxx/tramemo-api:latest" \
  -var="db_password=your-secure-db-password" \
  -var="environment=production" \
  -var="enable_cost_optimization=true"

# インフラ構築（無料枠最適化設定）
terraform apply \
  -var="container_image=gcr.io/traMemo-project-xxxxx/tramemo-api:latest" \
  -var="db_password=your-secure-db-password" \
  -var="environment=production" \
  -var="enable_cost_optimization=true" \
  -auto-approve
```

---

## フェーズ3: バックエンド（Laravel）デプロイ

> **目的**: 本番用の環境変数ファイル（.env.production）やDockerfileを作成し、LaravelアプリをGCP上で動かすための準備を行う。
> 
> **結果**: GCP環境に最適化されたLaravelアプリの設定ファイル・Dockerfileが用意され、ビルド・デプロイが可能な状態になる。

### ステップ5: Laravel設定

> **本番環境の環境変数は、.envファイルではなくCloud Runの環境変数機能およびSecret Managerで管理します。**
> 
> - Cloud Runの「環境変数」機能でAPP_ENVやAPP_URLなどを直接設定
> - 機密値（APP_KEY, DB_PASSWORD, CLERK_SECRET_KEYなど）はSecret Managerで管理し、Cloud Runの「シークレット」機能で環境変数やファイルとしてマウント
> - Laravelは環境変数 > .envファイルの優先順で値を取得するため、.env.productionの作成・配置は不要

#### 5.1 Cloud Run/Secret Managerでの環境変数・シークレット設定例

- **Cloud Runの環境変数設定（CLI例）**
  ```bash
  gcloud run deploy tramemo-api \
    --image gcr.io/traMemo-project-xxxxx/tramemo-api:latest \
    --region asia-northeast1 \
    --set-env-vars="APP_ENV=production,APP_URL=https://traMemo-api-xxxxx-ew.a.run.app,DB_HOST=/cloudsql/traMemo-project-xxxxx:asia-northeast1:tramemo-db,DB_DATABASE=tramemo_production,DB_USERNAME=tramemo_user,GOOGLE_CLOUD_PROJECT_ID=traMemo-project-xxxxx,GOOGLE_CLOUD_STORAGE_BUCKET=tramemo-images,GOOGLE_CLOUD_STORAGE_URL=https://storage.googleapis.com/tramemo-images,CLERK_PUBLISHABLE_KEY=pk_test_your-clerk-publishable-key"
  ```

- **Secret Managerの値をCloud Runにマウント（例: DB_PASSWORD, APP_KEY, CLERK_SECRET_KEY）**
  - GCPコンソールのCloud Runサービス編集画面 > [シークレット] から、
    - 環境変数としてマウント（例: DB_PASSWORD=projects/xxx/secrets/db-password/versions/latest）
    - ファイルとしてマウントも可能

- **Laravel側では `env('DB_PASSWORD')` などで参照可能**

---

#### 5.2 Dockerfile作成
```bash
# infra/gcp/docker/Dockerfile を作成
cat > infra/gcp/docker/Dockerfile << 'EOF'
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
EOF
```

#### 5.3 .dockerignore作成
```bash
# infra/gcp/docker/.dockerignore を作成
cat > infra/gcp/docker/.dockerignore << 'EOF'
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
EOF
```

---

### ステップ6: Dockerイメージビルド・プッシュ

> **目的**: LaravelアプリをDockerイメージとしてビルドし、Google Container Registry（GCR）にアップロードする。
> 
> **結果**: GCP上でCloud Runにデプロイ可能なDockerイメージがGCRに格納される。

#### 6.1 Docker認証設定
```bash
# GCR認証設定
gcloud auth configure-docker
```

#### 6.2 イメージビルド・プッシュ
```bash
# プロジェクトルートで実行
docker build -t gcr.io/traMemo-project-xxxxx/tramemo-api:latest -f infra/gcp/docker/Dockerfile .
docker push gcr.io/traMemo-project-xxxxx/tramemo-api:latest
```

---

### ステップ7: Cloud Runデプロイ

> **目的**: GCRにアップロードしたDockerイメージをCloud Runにデプロイし、APIサーバーとして公開する。必要に応じてDBマイグレーションも実行する。
> 
> **結果**: GCP上でLaravel APIがCloud Runサービスとして稼働し、外部からアクセス可能になる。データベースの初期化・マイグレーションも完了する。

#### 7.1 サービスデプロイ（無料枠最適化）
```bash
# Cloud Runにデプロイ（無料枠最適化設定）
gcloud run deploy tramemo-api \
  --image gcr.io/traMemo-project-xxxxx/tramemo-api:latest \
  --region asia-northeast1 \
  --platform managed \
  --allow-unauthenticated \
  --min-instances=0 \
  --max-instances=10 \
  --concurrency=80 \
  --timeout=300 \
  --memory=512Mi \
  --cpu=1 \
  --set-env-vars="APP_ENV=production" \
  --set-env-vars="DB_HOST=/cloudsql/traMemo-project-xxxxx:asia-northeast1:tramemo-db" \
  --set-env-vars="DB_DATABASE=tramemo_production" \
  --set-env-vars="DB_USERNAME=tramemo_user" \
  --set-env-vars="DB_PASSWORD=your-secure-db-password" \
  --set-env-vars="GOOGLE_CLOUD_STORAGE_BUCKET=tramemo-images" \
  --set-env-vars="CLERK_SECRET_KEY=sk_test_your-clerk-secret-key" \
  --set-env-vars="CLERK_PUBLISHABLE_KEY=pk_test_your-clerk-publishable-key" \
  --add-cloudsql-instances=traMemo-project-xxxxx:asia-northeast1:tramemo-db
```

#### 7.2 データベースマイグレーション
```bash
# マイグレーション実行
gcloud run jobs create migrate \
  --image gcr.io/traMemo-project-xxxxx/tramemo-api:latest \
  --command="php" \
  --args="artisan,migrate,--force" \
  --region=asia-northeast1 \
  --set-cloudsql-instances=traMemo-project-xxxxx:asia-northeast1:tramemo-db \
  --set-env-vars="DB_HOST=/cloudsql/traMemo-project-xxxxx:asia-northeast1:tramemo-db" \
  --set-env-vars="DB_DATABASE=tramemo_production" \
  --set-env-vars="DB_USERNAME=tramemo_user" \
  --set-env-vars="DB_PASSWORD=your-secure-db-password"
```

---

## フェーズ4: フロントエンド（React）デプロイ

### ステップ8: React設定

> **目的**: フロントエンド（React）の本番用環境変数を設定し、ビルドを行う。
> 
> **結果**: 本番用の静的ファイル（buildディレクトリ）が生成される。

#### 8.1 環境変数設定
```bash
# フロントエンドプロジェクトで .env.production を作成
cat > frontend/.env.production << EOF
REACT_APP_API_URL=https://traMemo-api-xxxxx-ew.a.run.app/api
REACT_APP_CLERK_PUBLISHABLE_KEY=pk_test_your-clerk-publishable-key
REACT_APP_CLOUD_STORAGE_URL=https://storage.googleapis.com/tramemo-images
EOF
```

#### 8.2 ビルド設定
```bash
# package.json の build スクリプトを確認
# "build": "react-scripts build"

# 本番ビルド実行
cd frontend
npm run build
```

---

### ステップ9: Cloud Storage設定

> **目的**: ReactのビルドファイルをCloud Storageバケットにアップロードし、静的サイトとして公開する準備をする。
> 
> **結果**: フロントエンドの静的ファイルがGCPのCloud Storageバケットに配置され、公開設定・キャッシュ設定も適用される。

#### 9.1 静的サイト用バケット作成（無料枠最適化）
```bash
# 静的サイト用バケットを作成
gsutil mb -l asia-northeast1 gs://tramemo-frontend

# バケットを公開設定
gsutil iam ch allUsers:objectViewer gs://tramemo-frontend

# 静的サイト設定
gsutil web set -m index.html -e 404.html gs://tramemo-frontend

# コスト最適化のためのメタデータ設定
gsutil -m setmeta -h "Cache-Control:public, max-age=31536000" gs://tramemo-frontend/static/**/*
gsutil -m setmeta -h "Cache-Control:public, max-age=0" gs://tramemo-frontend/index.html
```

#### 9.2 フロントエンドファイルアップロード（無料枠最適化）
```bash
# ビルドファイルをアップロード
gsutil -m cp -r frontend/build/* gs://tramemo-frontend/

# コスト最適化のためのキャッシュ設定
gsutil -m setmeta -h "Cache-Control:public, max-age=31536000" gs://tramemo-frontend/static/**/*
gsutil -m setmeta -h "Cache-Control:public, max-age=0" gs://tramemo-frontend/index.html

# 不要なファイルの削除（コスト削減）
gsutil -m rm gs://tramemo-frontend/**/*.map  # ソースマップファイル削除
```

---

### ステップ10: Cloud CDN設定

> **目的**: Cloud Storage上の静的サイトを高速・低コストで配信するため、Cloud CDNとロードバランサを設定する。
> 
> **結果**: フロントエンドがグローバルに高速配信されるようになり、独自ドメインやSSLも利用可能となる。

#### 10.1 ロードバランサー作成（無料枠最適化）
```bash
# 外部IPアドレスを予約
gcloud compute addresses create tramemo-frontend-ip \
  --global

# バックエンドバケットを作成
gcloud compute backend-buckets create tramemo-frontend-backend \
  --gcs-bucket-name=tramemo-frontend

# URLマップを作成（コスト最適化設定）
gcloud compute url-maps create tramemo-frontend-map \
  --default-backend-bucket=tramemo-frontend-backend

# キャッシュポリシーを設定（コスト削減）
gcloud compute url-maps update tramemo-frontend-map \
  --default-backend-bucket=tramemo-frontend-backend \
  --cache-key-include-query-string=false

# HTTPSプロキシを作成
gcloud compute target-https-proxies create tramemo-frontend-https-proxy \
  --url-map=tramemo-frontend-map \
  --ssl-certificates=tramemo-frontend-cert

# 転送ルールを作成
gcloud compute global-forwarding-rules create tramemo-frontend-rule \
  --address=tramemo-frontend-ip \
  --target-https-proxy=tramemo-frontend-https-proxy \
  --global \
  --ports=443
```

---

## フェーズ5: CI/CD設定

### ステップ11: GitHub Actions設定

> **目的**: GitHub Actionsを使い、テスト・ビルド・デプロイを自動化するCI/CDパイプラインを構築する。
> 
> **結果**: コードのpushやPR時に自動でテスト・ビルド・デプロイが実行され、運用効率と品質が向上する。

#### 11.1 GitHub Secrets設定
GitHubリポジトリの Settings → Secrets and variables → Actions で以下を設定：

- `GCP_PROJECT_ID`: traMemo-project-xxxxx
- `GCP_SA_KEY`: サービスアカウントキーのJSON内容
- `DB_PASSWORD`: your-secure-db-password
- `CLERK_SECRET_KEY`: sk_test_your-clerk-secret-key
- `CLERK_PUBLISHABLE_KEY`: pk_test_your-clerk-publishable-key

#### 11.2 GitHub Actionsワークフロー作成
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
  SERVICE_NAME: tramemo-api

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: tramemo_testing
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

  deploy-backend:
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
          --set-env-vars="DB_HOST=/cloudsql/${{ secrets.GCP_PROJECT_ID }}:${{ env.REGION }}:tramemo-db" \
          --set-env-vars="DB_DATABASE=tramemo_production" \
          --set-env-vars="DB_USERNAME=tramemo_user" \
          --set-env-vars="DB_PASSWORD=${{ secrets.DB_PASSWORD }}" \
          --set-env-vars="GOOGLE_CLOUD_STORAGE_BUCKET=tramemo-images" \
          --set-env-vars="CLERK_SECRET_KEY=${{ secrets.CLERK_SECRET_KEY }}" \
          --set-env-vars="CLERK_PUBLISHABLE_KEY=${{ secrets.CLERK_PUBLISHABLE_KEY }}"
    
    - name: Run database migrations
      run: |
        gcloud run jobs create migrate-${{ github.sha }} \
          --image gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ env.SERVICE_NAME }}:${{ github.sha }} \
          --command="php" \
          --args="artisan,migrate,--force" \
          --region=${{ env.REGION }} \
          --set-cloudsql-instances=${{ secrets.GCP_PROJECT_ID }}:${{ env.REGION }}:tramemo-db

  deploy-frontend:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'
        cache-dependency-path: frontend/package-lock.json
    
    - name: Install dependencies
      run: |
        cd frontend
        npm ci
    
    - name: Build frontend
      run: |
        cd frontend
        npm run build
      env:
        REACT_APP_API_URL: https://traMemo-api-${{ secrets.GCP_PROJECT_ID }}-ew.a.run.app/api
        REACT_APP_CLERK_PUBLISHABLE_KEY: ${{ secrets.CLERK_PUBLISHABLE_KEY }}
        REACT_APP_CLOUD_STORAGE_URL: https://storage.googleapis.com/tramemo-images
    
    - name: Setup Google Cloud CLI
      uses: google-github-actions/setup-gcloud@v0
      with:
        project_id: ${{ secrets.GCP_PROJECT_ID }}
        service_account_key: ${{ secrets.GCP_SA_KEY }}
        export_default_credentials: true
    
    - name: Deploy to Cloud Storage
      run: |
        gsutil -m cp -r frontend/build/* gs://tramemo-frontend/
        gsutil -m setmeta -h "Cache-Control:public, max-age=31536000" gs://tramemo-frontend/static/**/*
        gsutil -m setmeta -h "Cache-Control:public, max-age=0" gs://tramemo-frontend/index.html
```

---

## フェーズ6: 動作確認・監視設定

### ステップ12: 動作確認

> **目的**: デプロイしたAPI・フロントエンド・DBが正しく動作しているかを確認する。
> 
> **結果**: サービスが正常に稼働していることを確認できる。

#### 12.1 バックエンドAPI確認
```bash
# APIエンドポイント確認
curl https://traMemo-api-xxxxx-ew.a.run.app/api/health

# レスポンス例
{
  "status": "ok",
  "timestamp": "2024-07-01T10:00:00Z"
}
```

#### 12.2 フロントエンド確認
```bash
# フロントエンドURL確認
echo "フロントエンドURL: https://traMemo-frontend-ip-address"
```

#### 12.3 データベース接続確認
```bash
# Cloud SQL接続確認
gcloud sql connect tramemo-db --user=tramemo_user
```

---

### ステップ13: 監視設定

> **目的**: Cloud Monitoringやログエクスポートを設定し、リソース消費やコスト、障害を監視できるようにする。
> 
> **結果**: 異常時やコスト超過時にアラートを受け取れる体制が整い、安定運用が可能となる。

#### 13.1 Cloud Monitoring設定（コスト監視含む）
```bash
# コスト監視用アラートポリシー作成
cat > infra/gcp/monitoring/cost-alert-policy.yaml << 'EOF'
displayName: "TraMemo Cost Alert"
conditions:
  - displayName: "Daily cost exceeds threshold"
    conditionThreshold:
      filter: 'metric.type="billing.googleapis.com/billing/charges" AND resource.labels.project_id="traMemo-project-xxxxx"'
      comparison: COMPARISON_GREATER_THAN
      thresholdValue: 5.0  # $5/日を超えたらアラート
      duration: 0s
      aggregations:
        - alignmentPeriod: 86400s
          perSeriesAligner: ALIGN_SUM
          crossSeriesReducer: REDUCE_SUM
EOF

gcloud alpha monitoring policies create \
  --policy-from-file=infra/gcp/monitoring/cost-alert-policy.yaml

# リソース使用量監視
gcloud alpha monitoring policies create \
  --policy-from-file=infra/gcp/monitoring/alerting-policy.yaml
```

#### 13.2 ログ設定
```bash
# ログエクスポート設定
gcloud logging sinks create tramemo-logs \
  storage.googleapis.com/traMemo-logs-bucket \
  --log-filter="resource.type=cloud_run_revision AND resource.labels.service_name=tramemo-api"
```

---

## フェーズ7: セキュリティ・最適化

### ステップ14: セキュリティ強化

> **目的**: SSL証明書やセキュリティヘッダーを設定し、通信の安全性やサービスの堅牢性を高める。
> 
> **結果**: HTTPS化やセキュリティ強化が実現し、安心してサービスを公開できる。

#### 14.1 SSL証明書設定
```bash
# カスタムドメイン用SSL証明書
gcloud compute ssl-certificates create tramemo-frontend-cert \
  --domains=your-domain.com,www.your-domain.com \
  --global
```

#### 14.2 セキュリティヘッダー設定
```bash
# Cloud Load Balancingでセキュリティヘッダー設定
gcloud compute backend-services update tramemo-frontend-backend \
  --security-policy=traMemo-security-policy
```

---

### ステップ15: パフォーマンス・コスト最適化

> **目的**: Cloud Run/Cloud CDN/Cloud SQLの設定を最適化し、無料枠を最大限活用しつつパフォーマンスも維持する。
> 
> **結果**: サービス運用コストが最小化され、パフォーマンスも安定する。

#### 15.1 Cloud Run最適化（無料枠最適化）
```bash
# Cloud Run設定最適化（無料枠内での最大効率）
gcloud run services update tramemo-api \
  --region=asia-northeast1 \
  --min-instances=0 \
  --max-instances=10 \
  --concurrency=80 \
  --timeout=300 \
  --memory=512Mi \
  --cpu=1
```

#### 15.2 CDN最適化（コスト削減）
```bash
# Cloud CDNキャッシュ設定（転送量削減）
gcloud compute url-maps update tramemo-frontend-map \
  --default-backend-bucket=tramemo-frontend-backend \
  --cache-key-include-query-string=false

# キャッシュ期間の最適化
gcloud compute backend-buckets update tramemo-frontend-backend \
  --enable-cdn \
  --cache-mode=CACHE_ALL_STATIC
```

#### 15.3 データベース最適化（コスト削減）
```bash
# Cloud SQL設定最適化
gcloud sql instances patch tramemo-db \
  --region=asia-northeast1 \
  --storage-size=10GB \
  --storage-type=PD_SSD

# 不要なログの削除（ストレージコスト削減）
gcloud sql instances patch tramemo-db \
  --region=asia-northeast1 \
  --database-flags="slow_query_log=off,general_log=off"
```

---

## コスト最適化戦略（無料枠最大活用・極小構成）

本プロジェクトはポートフォリオ用途・極小規模運用を前提とし、GCPの無料枠を最大限活用することで、原則「月額0円」での運用を目指します。

### 1. Cloud Run（APIサーバー）
- **最小インスタンス数を0に設定**（アクセスがないときは完全停止）
- **最大インスタンス数を10以下に制限**
- **同時接続数（concurrency）を80に設定**
- **タイムアウトは300秒以内**
- **メモリ512Mi、CPU 1vCPUで最小構成**

```bash
gcloud run services update tramemo-api \
  --region=asia-northeast1 \
  --min-instances=0 \
  --max-instances=10 \
  --concurrency=80 \
  --timeout=300 \
  --memory=512Mi \
  --cpu=1
```

### 2. Cloud SQL（MySQL）
- **最小構成（db-f1-micro）で作成**
- **自動停止（開発用DB）を有効化**
- **本番DBも1GB以内に抑える**
- **バックアップ保持期間を短縮（3世代）**
- **不要なログを無効化**

```bash
gcloud sql instances patch tramemo-db-dev \
  --activation-policy=NEVER

gcloud sql instances patch tramemo-db \
  --database-flags="slow_query_log=off,general_log=off"
```

### 3. Cloud Storage
- **画像は圧縮・最適化してアップロード**
- **バケットのライフサイクルポリシーで古いファイルを自動削除（6ヶ月）**
- **不要なファイルは定期的に手動削除**
- **バージョニング無効（コスト削減）**

```json
// infra/gcp/storage/lifecycle.json
{
  "rule": [
    {
      "action": {"type": "Delete"},
      "condition": {"age": 180}
    }
  ]
}
```

```bash
gsutil lifecycle set infra/gcp/storage/lifecycle.json gs://tramemo-images
```

### 4. Cloud CDN
- **キャッシュキーにクエリストリングを含めない**
- **静的ファイルは長期キャッシュ、index.htmlは即時反映**
- **CACHE_ALL_STATICモードで転送量削減**

```bash
gcloud compute url-maps update tramemo-frontend-map \
  --default-backend-bucket=tramemo-frontend-backend \
  --cache-key-include-query-string=false

gsutil -m setmeta -h "Cache-Control:public, max-age=31536000" gs://tramemo-frontend/static/**/*
gsutil -m setmeta -h "Cache-Control:public, max-age=0" gs://tramemo-frontend/index.html
```

### 5. 監視・アラート
- **コスト監視アラート（$5/日超過で通知）**
- **リソース使用量監視で無料枠超過を防止**
- **不要なリソース（テスト用DB・バケット等）は定期的に削除**

```bash
# コストアラート設定
gcloud alpha monitoring policies create \
  --policy-from-file=infra/gcp/monitoring/cost-alert-policy.yaml
```

### 6. 月間コスト予測（極小規模運用）
| サービス | 使用量 | 無料枠 | 超過分 | 月間コスト |
|----------|--------|--------|--------|-----------|
| Cloud Run | 50,000リクエスト | 200万回 | 0 | $0 |
| Cloud SQL | 500MB | 1GB | 0 | $0 |
| Cloud Storage | 2GB | 5GB | 0 | $0 |
| Cloud CDN | 5GB転送 | 1GB | 4GB × $0.08 | $0.32 |
| **合計** | | | | **$0.32/月** |

---

## トラブルシューティング

### よくある問題と解決方法

| 問題 | 原因 | 解決方法 |
|------|------|----------|
| Cloud Run起動エラー | 環境変数不足 | 必須環境変数の確認 |
| データベース接続エラー | Cloud SQL接続設定 | 接続名とIAM権限の確認 |
| フロントエンド表示エラー | API URL設定 | 環境変数の確認 |
| 画像アップロードエラー | Cloud Storage権限 | サービスアカウント権限の確認 |

### ログ確認方法
```bash
# Cloud Runログ
gcloud logging read "resource.type=cloud_run_revision AND resource.labels.service_name=tramemo-api" --limit=50

# Cloud SQLログ
gcloud sql logs tail tramemo-db

# Cloud Storageログ
gsutil logging read gs://tramemo-images
```

---

## コスト最適化（無料枠最大活用）

### 月間コスト予測（極小規模運用）
- Cloud Run: $0（無料枠内）
- Cloud SQL: $0（無料枠内）
- Cloud Storage: $0（無料枠内）
- Cloud CDN: $0.32（4GB超過分）
- **合計: $0.32/月**

### 無料枠内での運用ポイント
1. **Cloud Run**: 最小インスタンス数0、最大10以下、concurrency=80
2. **Cloud SQL**: db-f1-micro、1GB以内、不要ログ無効化
3. **Cloud Storage**: 5GB以内、ライフサイクルポリシー、バージョニング無効
4. **Cloud CDN**: 1GB以内、キャッシュ最適化、転送量削減
5. **監視**: コストアラート設定、リソース使用量監視

---

## 参考リンク

- [Google Cloud Run公式ドキュメント](https://cloud.google.com/run/docs)
- [Cloud SQL公式ドキュメント](https://cloud.google.com/sql/docs)
- [Cloud Storage公式ドキュメント](https://cloud.google.com/storage/docs)
- [Terraform Google Provider](https://registry.terraform.io/providers/hashicorp/google/latest/docs)
- [GitHub Actions公式ドキュメント](https://docs.github.com/en/actions)

--- 