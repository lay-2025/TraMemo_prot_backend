# GCPデプロイ手順書（詳細・冗長版リファクタ）

---

## 目次
1. 概要・前提条件
2. GCPプロジェクト・API・サービスアカウント準備
3. Secret Managerによる機密情報管理
4. Terraformによるインフラ構築
5. Laravel（バックエンド）デプロイ
6. React（フロントエンド）デプロイ
7. Cloud Run Jobによるマイグレーション
8. Cloud CDN/ロードバランサ設定
9. 監視・コスト最適化
10. トラブルシューティング・FAQ

---

## 1. 概要・前提条件

**目的:**
本ドキュメントは、TraMemoプロジェクト（バックエンド: Laravel、フロントエンド: React）をGoogle Cloud Platform (GCP)にデプロイするための詳細な手順を記載します。

**結果:**
GCP上で、Cloud Run, Cloud SQL, Cloud Storage, Cloud CDNを活用した安全・低コストな本番運用環境が構築できます。

**前提条件:**
- Google Cloud CLI (gcloud)
- Docker
- Terraform
- Git
- Node.js (フロントエンドビルド用)
- GCPアカウント・請求有効化済み

---

## 2. GCPプロジェクト・API・サービスアカウント準備

### 2.1 プロジェクト・API有効化

**目的:**
GCP上で必要なAPIを有効化し、プロジェクト・リージョンを設定します。

**結果:**
以降のgcloud/Terraform操作が正しいプロジェクト・リージョンで行われます。

```bash
# プロジェクトIDを設定
# 例: YOUR_PROJECT_ID=tramemo-project-xxxxx
# 必ずご自身のGCPプロジェクトIDに置き換えてください

gcloud config set project YOUR_PROJECT_ID
# リージョンを設定（例: asia-northeast1）
gcloud config set compute/region asia-northeast1

# 必要なAPIを有効化
# Cloud Run, Cloud SQL, Cloud Storage, Secret Manager, Monitoring, Logging など
# 途中でエラーが出た場合は、API名やプロジェクトIDを再確認してください
gcloud services enable \
  cloudbuild.googleapis.com \
  run.googleapis.com \
  sqladmin.googleapis.com \
  storage.googleapis.com \
  secretmanager.googleapis.com \
  monitoring.googleapis.com \
  logging.googleapis.com
```

### 2.2 サービスアカウント作成・権限付与

**目的:**
CI/CDやTerraform、Cloud Run等で利用するサービスアカウントを作成し、必要な権限を付与します。

**結果:**
以降の自動化・デプロイ作業で権限エラーが発生しません。

```bash
# デプロイ用サービスアカウントを作成
# 例: tramemo-deployer
# --display-nameは任意

gcloud iam service-accounts create tramemo-deployer --display-name="TraMemo Deployer"

# サービスアカウントにオーナー権限を付与（検証・個人開発用途。運用時は必要な権限のみ推奨）
gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
  --member="serviceAccount:tramemo-deployer@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
  --role="roles/owner"

# サービスアカウントキーの作成（CI/CDやTerraformで利用する場合）
gcloud iam service-accounts keys create \
  infra/gcp/keys/deployer-key.json \
  --iam-account=tramemo-deployer@YOUR_PROJECT_ID.iam.gserviceaccount.com
```

---

## 3. Secret Managerによる機密情報管理

### 3.1 シークレット作成

**目的:**
DBパスワードやLaravel APP_KEY、外部APIキーなどの機密情報をGCP Secret Managerで安全に管理します。

**結果:**
機密情報がgcloudコマンドやTerraformから安全に参照できるようになります。

```bash
# DBパスワードの登録
echo -n "your-db-password" | gcloud secrets create db-password --data-file=-
# Laravel APP_KEYの登録（base64:で始まる値）
echo -n "base64:your-laravel-app-key" | gcloud secrets create laravel-app-key --data-file=-
# Clerk Secret Key
echo -n "sk_test_your-clerk-secret-key" | gcloud secrets create clerk-secret-key --data-file=-
# Clerk Publishable Key
echo -n "pk_test_your-clerk-publishable-key" | gcloud secrets create clerk-publishable-key --data-file=-
```

### 3.2 サービスアカウントにSecret Manager権限付与

**目的:**
Cloud RunやTerraform等からSecret Managerの値を参照できるようにします。

**結果:**
Secret Managerの値をCloud RunやTerraformで安全に利用できます。

```bash
# サービスアカウントにSecret Managerのアクセサ権限を付与
gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
  --member="serviceAccount:tramemo-deployer@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"
```

---

## 4. Terraformによるインフラ構築

**目的:**
Cloud SQL, Cloud Storage, Cloud Run, IAM等のGCPリソースをコードで一括管理・構築します。

**結果:**
GCP上に必要なインフラリソースが一貫性を持って自動構築されます。

### 4.1 ディレクトリ・ファイル準備

```bash
# プロジェクトルートでTerraform用ディレクトリを作成
mkdir -p infra/gcp/terraform
cd infra/gcp/terraform
```

### 4.2 main.tf（元の内容をそのまま記載）
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

### 4.3 variables.tf（元の内容をそのまま記載）
```hcl
variable "project_id" {
  description = "GCP Project ID"
  type        = string
  default     = "tramemo-project-xxxxx"
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

### 4.4 outputs.tf（元の内容をそのまま記載）
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

### 4.5 Terraform実行手順

**目的:**
TerraformでGCPリソースを一括構築します。

**結果:**
Cloud SQL, Cloud Storage, Cloud Run, IAM等が自動で作成されます。

```bash
# Terraform初期化（初回のみ）
terraform init

# プラン確認（実際に何が作成・変更されるかを事前確認）
terraform plan \
  -var="container_image=gcr.io/YOUR_PROJECT_ID/tramemo-api:latest" \
  -var="db_password=$(gcloud secrets versions access latest --secret=db-password)" \
  -var="environment=production" \
  -var="enable_cost_optimization=true"

# インフラ構築（applyで実際にリソース作成）
terraform apply \
  -var="container_image=gcr.io/YOUR_PROJECT_ID/tramemo-api:latest" \
  -var="db_password=$(gcloud secrets versions access latest --secret=db-password)" \
  -var="environment=production" \
  -var="enable_cost_optimization=true" \
  -auto-approve
```

---

## 5. Laravel（バックエンド）デプロイ

### 5.1 Dockerイメージビルド・プッシュ

**目的:**
LaravelアプリをDockerイメージとしてビルドし、Google Container Registry（GCR）にアップロードします。

**結果:**
GCP上でCloud Runにデプロイ可能なDockerイメージがGCRに格納されます。

```bash
# GCR認証設定（初回のみ）
gcloud auth configure-docker

# Dockerイメージをビルド
# -tでイメージ名を指定（gcr.io/プロジェクトID/イメージ名:タグ）
docker build -t gcr.io/YOUR_PROJECT_ID/tramemo-api:latest -f infra/gcp/docker/Dockerfile .

# DockerイメージをGCRにプッシュ
docker push gcr.io/YOUR_PROJECT_ID/tramemo-api:latest
```

### 5.2 Cloud Runデプロイ（環境変数・シークレット管理）

**目的:**
GCRにアップロードしたDockerイメージをCloud Runにデプロイし、APIサーバーとして公開します。

**結果:**
GCP上でLaravel APIがCloud Runサービスとして稼働し、外部からアクセス可能になります。

```bash
# Cloud Runにデプロイ
# --set-env-varsで非機密値、--set-secretsでSecret Managerの値を環境変数として渡す
# --add-cloudsql-instancesでCloud SQLとの接続を許可

gcloud run deploy tramemo-api \
  --image gcr.io/YOUR_PROJECT_ID/tramemo-api:latest \
  --region asia-northeast1 \
  --platform managed \
  --allow-unauthenticated \
  --min-instances=0 \
  --max-instances=10 \
  --concurrency=80 \
  --timeout=300 \
  --memory=512Mi \
  --cpu=1 \
  --set-env-vars="APP_ENV=production,DB_HOST=/cloudsql/YOUR_PROJECT_ID:asia-northeast1:tramemo-db,DB_DATABASE=tramemo_production,DB_USERNAME=tramemo_user,GOOGLE_CLOUD_STORAGE_BUCKET=tramemo-images" \
  --set-secrets="DB_PASSWORD=db-password:latest,CLERK_SECRET_KEY=clerk-secret-key:latest,CLERK_PUBLISHABLE_KEY=clerk-publishable-key:latest" \
  --add-cloudsql-instances=YOUR_PROJECT_ID:asia-northeast1:tramemo-db
```

---

## 6. React（フロントエンド）デプロイ

### 6.1 ビルド

**目的:**
フロントエンド（React）の本番用環境変数を設定し、ビルドを行います。

**結果:**
本番用の静的ファイル（buildディレクトリ）が生成されます。

```bash
# .env.productionなどでAPIエンドポイント等を設定
# 例: REACT_APP_API_URL=https://tramemo-api-xxxxx-ew.a.run.app/api

cd frontend
npm ci
npm run build
```

### 6.2 Cloud Storageへアップロード

**目的:**
ReactのビルドファイルをCloud Storageバケットにアップロードし、静的サイトとして公開します。

**結果:**
フロントエンドの静的ファイルがGCPのCloud Storageバケットに配置され、公開設定・キャッシュ設定も適用されます。

```bash
# バケット作成（初回のみ）
gsutil mb -l asia-northeast1 gs://tramemo-frontend
# バケットを公開設定
gsutil iam ch allUsers:objectViewer gs://tramemo-frontend
# 静的サイト設定
gsutil web set -m index.html -e 404.html gs://tramemo-frontend
# ビルドファイルをアップロード
gsutil -m cp -r build/* gs://tramemo-frontend/
# キャッシュ設定
gsutil -m setmeta -h "Cache-Control:public, max-age=31536000" gs://tramemo-frontend/assets/**/*
gsutil -m setmeta -h "Cache-Control:public, max-age=0" gs://tramemo-frontend/index.html
# もし .map ファイルがあれば削除（なければこのコマンドはスキップ可）
gsutil -m rm gs://tramemo-frontend/**/*.map
```

---

## 7. Cloud Run Jobによるマイグレーション

### 7.1 ジョブ作成

**目的:**
Cloud Run Jobとしてマイグレーション用のジョブを作成します。

**結果:**
マイグレーションを安全に何度でも実行できるようになります。

```bash
# ジョブ作成（初回のみ）
gcloud run jobs create migrate \
  --image gcr.io/YOUR_PROJECT_ID/tramemo-api:latest \
  --command="php" \
  --args="artisan,migrate,--force" \
  --region=asia-northeast1 \
  --set-cloudsql-instances=YOUR_PROJECT_ID:asia-northeast1:tramemo-db \
  --set-env-vars="DB_HOST=/cloudsql/YOUR_PROJECT_ID:asia-northeast1:tramemo-db,DB_DATABASE=tramemo_production,DB_USERNAME=tramemo_user" \
  --set-secrets="DB_PASSWORD=db-password:latest"

# 既にジョブが存在する場合はupdateで上書き可能
gcloud run jobs update migrate ...
```

### 7.2 ジョブ実行

**目的:**
作成したジョブを実行し、DBマイグレーションを行います。

**結果:**
Cloud SQLに対してLaravelのマイグレーションが実行されます。

```bash
# マイグレーション実行
gcloud run jobs execute migrate --region=asia-northeast1
```

---

## 8. HTTPS対応・Cloud CDN/ロードバランサ構築

**概要:**
本章では、フロントエンドの静的サイトをグローバルに安全かつ高速に配信するためのHTTPS対応、GoogleマネージドSSL証明書の発行、Cloud CDNおよびHTTP(S)ロードバランサの構築手順をまとめます。これにより、独自ドメインでのHTTPS通信や、CDNによる高速配信、セキュリティ強化が実現できます。

### 8.1 GoogleマネージドSSL証明書（自動発行・自動更新）の作成
**注意:**
今回は独自ドメインを取得しないためこの工程は不要

**目的:**
HTTPS通信を実現し、ユーザーとサービス間の通信を安全に暗号化するため、GoogleマネージドSSL証明書を作成します。Googleマネージド証明書は、証明書の発行・更新・失効をGoogleが自動で管理してくれるため、運用負荷が大幅に軽減されます。

**結果:**
指定した独自ドメインに対して、Googleが自動でSSL証明書を発行し、HTTPS通信が可能となります。証明書の有効期限切れや更新作業も自動化され、セキュリティリスクを低減できます。

**手順:**
1. 事前に独自ドメインを取得し、GCPプロジェクトで利用できる状態にしておきます。
2. ドメインのDNS設定で、Googleが指定する値（AレコードやCNAMEレコード）を正しく設定します。
3. 以下のコマンドでGoogleマネージドSSL証明書リソースを作成します。

```bash
# GoogleマネージドSSL証明書を作成
# --domainsには利用する独自ドメイン名を指定
# 例: your-domain.example.com

gcloud compute ssl-certificates create tramemo-frontend-cert \
  --domains="your-domain.example.com"
```

**コマンドの説明:**
- `gcloud compute ssl-certificates create` : 新しいSSL証明書リソースを作成します。
- `tramemo-frontend-cert` : 証明書リソースの名前です。以降の手順でこの名前を参照します。
- `--domains` : 証明書を発行する対象のドメイン名を指定します。複数ドメインもカンマ区切りで指定可能です。

**注意:**
- 証明書のステータスが「ACTIVE」になるまで数分～数十分かかる場合があります。
- DNS設定が正しくない場合、証明書が「PROVISIONING」や「FAILED」状態のままになります。
- 証明書の状態はGCPコンソール「ネットワークサービス」→「SSL証明書」や、以下のコマンドで確認できます。

```bash
gcloud compute ssl-certificates list
gcloud compute ssl-certificates describe tramemo-frontend-cert
```

---

### 8.2 Cloud CDN/ロードバランサ設定（独自ドメイン・証明書発行なしの場合）

**目的:**
Cloud Storage上の静的サイトをGoogleが提供するデフォルトドメインで高速・低コストに配信するため、Cloud CDNとロードバランサを設定します。独自ドメインや証明書発行は行わず、Googleが自動で管理するドメイン・証明書を利用します。

**結果:**
フロントエンドがGoogleのデフォルトドメイン（例: `https://[ランダム名].cloudgooglereloadbalancer.com`）でグローバルに高速配信され、HTTPS通信も自動的に有効となります。DNS設定や証明書発行作業は不要です。

```bash
# 外部IPアドレスを予約
gcloud compute addresses create tramemo-frontend-ip --global
# バックエンドバケットを作成
gcloud compute backend-buckets create tramemo-frontend-backend --gcs-bucket-name=tramemo-frontend
# URLマップを作成
gcloud compute url-maps create tramemo-frontend-map --default-backend-bucket=tramemo-frontend-backend
# キャッシュポリシーを設定
gcloud compute url-maps update tramemo-frontend-map --default-backend-bucket=tramemo-frontend-backend --cache-key-include-query-string=false
# HTTPSプロキシを作成（証明書指定なし、Google管理の証明書が自動適用）
gcloud compute target-https-proxies create tramemo-frontend-https-proxy --url-map=tramemo-frontend-map
# 転送ルールを作成
gcloud compute global-forwarding-rules create tramemo-frontend-rule --address=tramemo-frontend-ip --target-https-proxy=tramemo-frontend-https-proxy --global --ports=443
```

**コマンドの説明:**
- `gcloud compute target-https-proxies create` コマンドでは `--ssl-certificates` オプションを省略します。Googleが自動で証明書を割り当て、HTTPS通信が有効になります。
- 作成後、GCPコンソール「ネットワークサービス」→「ロードバランサ」で自動割り当てされたデフォルトドメインが確認できます。

**注意:**
- 独自ドメインでのアクセスや、独自証明書の利用はできません。
- Googleが自動で管理するドメイン・証明書のみ利用可能です。
- Cloud Storageの静的サイト公開URL（`https://storage.googleapis.com/[バケット名]/index.html`）も併用可能です。

---

## 9. 監視・コスト最適化

**目的:**
Cloud Monitoringやログエクスポートを設定し、リソース消費やコスト、障害を監視できるようにします。

**結果:**
異常時やコスト超過時にアラートを受け取れる体制が整い、安定運用が可能となります。

```bash
# コスト監視用アラートポリシー作成例
cat > infra/gcp/monitoring/cost-alert-policy.yaml << 'EOF'
displayName: "TraMemo Cost Alert"
conditions:
  - displayName: "Daily cost exceeds threshold"
    conditionThreshold:
      filter: 'metric.type="billing.googleapis.com/billing/charges" AND resource.labels.project_id="YOUR_PROJECT_ID"'
      comparison: COMPARISON_GREATER_THAN
      thresholdValue: 5.0  # $5/日を超えたらアラート
      duration: 0s
      aggregations:
        - alignmentPeriod: 86400s
          perSeriesAligner: ALIGN_SUM
          crossSeriesReducer: REDUCE_SUM
EOF

gcloud alpha monitoring policies create --policy-from-file=infra/gcp/monitoring/cost-alert-policy.yaml

# Cloud Runログ確認
gcloud logging read "resource.type=cloud_run_revision AND resource.labels.service_name=tramemo-api" --limit=50
# Cloud SQLログ確認
gcloud sql logs tail tramemo-db
```

---

## 10. トラブルシューティング・FAQ

### Q. Secret Managerの権限エラー
A. サービスアカウントにroles/secretmanager.secretAccessorを付与

### Q. Cloud Run Job作成時にalready existsエラー
A. `gcloud run jobs update`で上書き、または`gcloud run jobs delete`で削除後再作成

### Q. マイグレーションが実行されない
A. `gcloud run jobs execute`で明示的に実行

### Q. ヘルスチェックの課金
A. Cloud Runのヘルスチェックも通常リクエストとして課金対象

---

## 参考リンク
- [Cloud Run公式](https://cloud.google.com/run/docs)
- [Cloud SQL公式](https://cloud.google.com/sql/docs)
- [Cloud Storage公式](https://cloud.google.com/storage/docs)
- [Terraform Google Provider](https://registry.terraform.io/providers/hashicorp/google/latest/docs) 