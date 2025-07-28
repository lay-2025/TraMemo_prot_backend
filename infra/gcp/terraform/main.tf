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