GCPプロジェクトID：robotic-sky-465306-n5


#### 3.1 専用サービスアカウント作成・権限付与
```
# サービスアカウント作成
gcloud iam service-accounts create tramemo-app --display-name="TraMemo App Service Account"

# Cloud SQL Client
gcloud projects add-iam-policy-binding robotic-sky-465306-n5 --member="serviceAccount:tramemo-app@robotic-sky-465306-n5.iam.gserviceaccount.com" --role="roles/cloudsql.client"

# Secret Manager Secret Accessor
gcloud projects add-iam-policy-binding robotic-sky-465306-n5 --member="serviceAccount:tramemo-app@robotic-sky-465306-n5.iam.gserviceaccount.com" --role="roles/secretmanager.secretAccessor"

# Cloud Storage（読み取りのみの場合）
gcloud projects add-iam-policy-binding robotic-sky-465306-n5 --member="serviceAccount:tramemo-app@robotic-sky-465306-n5.iam.gserviceaccount.com" --role="roles/storage.objectViewer"

# Cloud Storage（書き込みも必要な場合）
gcloud projects add-iam-policy-binding robotic-sky-465306-n5 --member="serviceAccount:tramemo-app@robotic-sky-465306-n5.iam.gserviceaccount.com" --role="roles/storage.objectCreator"

# Cloud Logging（必要に応じて）
gcloud projects add-iam-policy-binding robotic-sky-465306-n5 --member="serviceAccount:tramemo-app@robotic-sky-465306-n5.iam.gserviceaccount.com" --role="roles/logging.logWriter"
```


#### 4.3 Terraform実行（無料枠最適化）
# Terraform初期化
terraform init

# プラン確認（無料枠最適化設定）
terraform plan -var="container_image=gcr.io/robotic-sky-465306-n5/tramemo-api:latest" -var="db_password=XX.-4Y!*Q4~rh3#7T&BY" -var="environment=production" -var="enable_cost_optimization=true"

```
実行結果
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform> terraform plan -var="container_image=gcr.io/robotic-sky-465306-n5/tramemo-api:latest" -var="db_password=XX.-4Y!*Q4~rh3#7T&BY" -var="environment=production" -var="enable_cost_optimization=true"

Terraform used the selected providers to generate the following execution plan. Resource actions are indicated with the
following symbols:
  + create

Terraform will perform the following actions:

  # google_cloud_run_service.main will be created
  + resource "google_cloud_run_service" "main" {
      + autogenerate_revision_name = false
      + id                         = (known after apply)
      + location                   = "asia-northeast1"
      + name                       = "traMemo-api"
      + project                    = (known after apply)
      + status                     = (known after apply)

      + metadata (known after apply)

      + template {
          + metadata {
              + annotations      = {
                  + "autoscaling.knative.dev/maxScale" = "10"
                  + "autoscaling.knative.dev/minScale" = "0"
                  + "autoscaling.knative.dev/target"   = "80"
                }
              + generation       = (known after apply)
              + labels           = (known after apply)
              + name             = (known after apply)
              + namespace        = (known after apply)
              + resource_version = (known after apply)
              + self_link        = (known after apply)
              + uid              = (known after apply)
            }
          + spec {
              + container_concurrency = 80
              + service_account_name  = (known after apply)
              + serving_state         = (known after apply)
              + timeout_seconds       = 300

              + containers {
                  + image = "gcr.io/robotic-sky-465306-n5/tramemo-api:latest"
                  + name  = (known after apply)

                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }

                  + ports (known after apply)

                  + resources {
                      + limits = {
                          + "cpu"    = "1000m"
                          + "memory" = "512Mi"
                        }
                    }

                  + startup_probe (known after apply)
                }
            }
        }

      + traffic {
          + latest_revision = true
          + percent         = 100
          + url             = (known after apply)
        }
    }

  # google_cloud_run_service_iam_member.public will be created
  + resource "google_cloud_run_service_iam_member" "public" {
      + etag     = (known after apply)
      + id       = (known after apply)
      + location = "asia-northeast1"
      + member   = "allUsers"
      + project  = (known after apply)
      + role     = "roles/run.invoker"
      + service  = "traMemo-api"
    }

  # google_sql_database.database will be created
  + resource "google_sql_database" "database" {
      + charset         = (known after apply)
      + collation       = (known after apply)
      + deletion_policy = "DELETE"
      + id              = (known after apply)
      + instance        = "traMemo-db"
      + name            = "traMemo_production"
      + project         = (known after apply)
      + self_link       = (known after apply)
    }

  # google_sql_database_instance.main will be created
  + resource "google_sql_database_instance" "main" {
      + available_maintenance_versions = (known after apply)
      + connection_name                = (known after apply)
      + database_version               = "MYSQL_8_0"
      + deletion_protection            = false
      + dns_name                       = (known after apply)
      + encryption_key_name            = (known after apply)
      + first_ip_address               = (known after apply)
      + id                             = (known after apply)
      + instance_type                  = (known after apply)
      + ip_address                     = (known after apply)
      + maintenance_version            = (known after apply)
      + master_instance_name           = (known after apply)
      + name                           = "traMemo-db"
      + private_ip_address             = (known after apply)
      + project                        = (known after apply)
      + psc_service_attachment_link    = (known after apply)
      + public_ip_address              = (known after apply)
      + region                         = "asia-northeast1"
      + self_link                      = (known after apply)
      + server_ca_cert                 = (known after apply)
      + service_account_email_address  = (known after apply)

      + replica_configuration (known after apply)

      + settings {
          + activation_policy     = "ALWAYS"
          + availability_type     = "ZONAL"
          + connector_enforcement = (known after apply)
          + disk_autoresize       = true
          + disk_autoresize_limit = 0
          + disk_size             = 10
          + disk_type             = "PD_SSD"
          + pricing_plan          = "PER_USE"
          + tier                  = "db-f1-micro"
          + user_labels           = (known after apply)
          + version               = (known after apply)

          + backup_configuration {
              + enabled                        = true
              + start_time                     = "02:00"
              + transaction_log_retention_days = (known after apply)

              + backup_retention_settings {
                  + retained_backups = 3
                  + retention_unit   = "COUNT"
                }
            }

          + ip_configuration {
              + ipv4_enabled = true
              + require_ssl  = true

              + authorized_networks {
                  + name            = "all"
                  + value           = "0.0.0.0/0"
                    # (1 unchanged attribute hidden)
                }
            }

          + location_preference (known after apply)
        }
    }

  # google_sql_user.users will be created
  + resource "google_sql_user" "users" {
      + host                    = (known after apply)
      + id                      = (known after apply)
      + instance                = "traMemo-db"
      + name                    = "traMemo_user"
      + password                = (sensitive value)
      + project                 = (known after apply)
      + sql_server_user_details = (known after apply)
    }

  # google_storage_bucket.frontend will be created
  + resource "google_storage_bucket" "frontend" {
      + force_destroy               = false
      + id                          = (known after apply)
      + labels                      = (known after apply)
      + location                    = "ASIA-NORTHEAST1"
      + name                        = "traMemo-frontend"
      + project                     = (known after apply)
      + public_access_prevention    = (known after apply)
      + self_link                   = (known after apply)
      + storage_class               = "STANDARD"
      + uniform_bucket_level_access = true
      + url                         = (known after apply)

      + soft_delete_policy (known after apply)

      + versioning (known after apply)

      + website {
          + main_page_suffix = "index.html"
          + not_found_page   = "404.html"
        }
    }

  # google_storage_bucket.images will be created
  + resource "google_storage_bucket" "images" {
      + force_destroy               = false
      + id                          = (known after apply)
      + labels                      = (known after apply)
      + location                    = "ASIA-NORTHEAST1"
      + name                        = "traMemo-images"
      + project                     = (known after apply)
      + public_access_prevention    = (known after apply)
      + self_link                   = (known after apply)
      + storage_class               = "STANDARD"
      + uniform_bucket_level_access = true
      + url                         = (known after apply)

      + lifecycle_rule {
          + action {
              + type          = "Delete"
                # (1 unchanged attribute hidden)
            }
          + condition {
              + age                    = 180
              + matches_prefix         = []
              + matches_storage_class  = []
              + matches_suffix         = []
              + with_state             = (known after apply)
                # (3 unchanged attributes hidden)
            }
        }

      + soft_delete_policy (known after apply)

      + versioning {
          + enabled = false
        }

      + website (known after apply)
    }

Plan: 7 to add, 0 to change, 0 to destroy.

Changes to Outputs:
  + cloud_run_url            = (known after apply)
  + database_connection_name = (known after apply)
  + storage_bucket           = "traMemo-images"

───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

Note: You didn't use the -out option to save this plan, so Terraform can't guarantee to take exactly these actions if
you run "terraform apply" now.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform> terraform plan -var="container_image=gcr.io/robotic-sky-465306-n5/tramemo-api:latest" -var="db_password=XX.-4Y!*Q4~rh3#7T&BY" -var="environment=production" -var="enable_cost_optimization=true"

Terraform used the selected providers to generate the following execution plan. Resource actions are indicated with the
following symbols:
  + create

Terraform will perform the following actions:

  # google_cloud_run_service.main will be created
  + resource "google_cloud_run_service" "main" {
      + autogenerate_revision_name = false
      + id                         = (known after apply)
      + location                   = "asia-northeast1"
      + name                       = "tramemo-api"
      + project                    = (known after apply)
      + status                     = (known after apply)

      + metadata (known after apply)

      + template {
          + metadata {
              + annotations      = {
                  + "autoscaling.knative.dev/maxScale" = "10"
                  + "autoscaling.knative.dev/minScale" = "0"
                  + "autoscaling.knative.dev/target"   = "80"
                }
              + generation       = (known after apply)
              + labels           = (known after apply)
              + name             = (known after apply)
              + namespace        = (known after apply)
              + resource_version = (known after apply)
              + self_link        = (known after apply)
              + uid              = (known after apply)
            }
          + spec {
              + container_concurrency = 80
              + service_account_name  = (known after apply)
              + serving_state         = (known after apply)
              + timeout_seconds       = 300

              + containers {
                  + image = "gcr.io/robotic-sky-465306-n5/tramemo-api:latest"
                  + name  = (known after apply)

                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }

                  + ports (known after apply)

                  + resources {
                      + limits = {
                          + "cpu"    = "1000m"
                          + "memory" = "512Mi"
                        }
                    }

                  + startup_probe (known after apply)
                }
            }
        }

      + traffic {
          + latest_revision = true
          + percent         = 100
          + url             = (known after apply)
        }
    }

  # google_cloud_run_service_iam_member.public will be created
  + resource "google_cloud_run_service_iam_member" "public" {
      + etag     = (known after apply)
      + id       = (known after apply)
      + location = "asia-northeast1"
      + member   = "allUsers"
      + project  = (known after apply)
      + role     = "roles/run.invoker"
      + service  = "tramemo-api"
    }

  # google_sql_database.database will be created
  + resource "google_sql_database" "database" {
      + charset         = (known after apply)
      + collation       = (known after apply)
      + deletion_policy = "DELETE"
      + id              = (known after apply)
      + instance        = "tramemo-db"
      + name            = "tramemo_production"
      + project         = (known after apply)
      + self_link       = (known after apply)
    }

  # google_sql_database_instance.main will be created
  + resource "google_sql_database_instance" "main" {
      + available_maintenance_versions = (known after apply)
      + connection_name                = (known after apply)
      + database_version               = "MYSQL_8_0"
      + deletion_protection            = false
      + dns_name                       = (known after apply)
      + encryption_key_name            = (known after apply)
      + first_ip_address               = (known after apply)
      + id                             = (known after apply)
      + instance_type                  = (known after apply)
      + ip_address                     = (known after apply)
      + maintenance_version            = (known after apply)
      + master_instance_name           = (known after apply)
      + name                           = "tramemo-db"
      + private_ip_address             = (known after apply)
      + project                        = (known after apply)
      + psc_service_attachment_link    = (known after apply)
      + public_ip_address              = (known after apply)
      + region                         = "asia-northeast1"
      + self_link                      = (known after apply)
      + server_ca_cert                 = (known after apply)
      + service_account_email_address  = (known after apply)

      + replica_configuration (known after apply)

      + settings {
          + activation_policy     = "ALWAYS"
          + availability_type     = "ZONAL"
          + connector_enforcement = (known after apply)
          + disk_autoresize       = true
          + disk_autoresize_limit = 0
          + disk_size             = 10
          + disk_type             = "PD_SSD"
          + pricing_plan          = "PER_USE"
          + tier                  = "db-f1-micro"
          + user_labels           = (known after apply)
          + version               = (known after apply)

          + backup_configuration {
              + enabled                        = true
              + start_time                     = "02:00"
              + transaction_log_retention_days = (known after apply)

              + backup_retention_settings {
                  + retained_backups = 3
                  + retention_unit   = "COUNT"
                }
            }

          + ip_configuration {
              + ipv4_enabled = true
              + require_ssl  = true

              + authorized_networks {
                  + name            = "all"
                  + value           = "0.0.0.0/0"
                    # (1 unchanged attribute hidden)
                }
            }

          + location_preference (known after apply)
        }
    }

  # google_sql_user.users will be created
  + resource "google_sql_user" "users" {
      + host                    = (known after apply)
      + id                      = (known after apply)
      + instance                = "tramemo-db"
      + name                    = "tramemo_user"
      + password                = (sensitive value)
      + project                 = (known after apply)
      + sql_server_user_details = (known after apply)
    }

  # google_storage_bucket.frontend will be created
  + resource "google_storage_bucket" "frontend" {
      + force_destroy               = false
      + id                          = (known after apply)
      + labels                      = (known after apply)
      + location                    = "ASIA-NORTHEAST1"
      + name                        = "tramemo-frontend"
      + project                     = (known after apply)
      + public_access_prevention    = (known after apply)
      + self_link                   = (known after apply)
      + storage_class               = "STANDARD"
      + uniform_bucket_level_access = true
      + url                         = (known after apply)

      + soft_delete_policy (known after apply)

      + versioning (known after apply)

      + website {
          + main_page_suffix = "index.html"
          + not_found_page   = "404.html"
        }
    }

  # google_storage_bucket.images will be created
  + resource "google_storage_bucket" "images" {
      + force_destroy               = false
      + id                          = (known after apply)
      + labels                      = (known after apply)
      + location                    = "ASIA-NORTHEAST1"
      + name                        = "tramemo-images"
      + project                     = (known after apply)
      + public_access_prevention    = (known after apply)
      + self_link                   = (known after apply)
      + storage_class               = "STANDARD"
      + uniform_bucket_level_access = true
      + url                         = (known after apply)

      + lifecycle_rule {
          + action {
              + type          = "Delete"
                # (1 unchanged attribute hidden)
            }
          + condition {
              + age                    = 180
              + matches_prefix         = []
              + matches_storage_class  = []
              + matches_suffix         = []
              + with_state             = (known after apply)
                # (3 unchanged attributes hidden)
            }
        }

      + soft_delete_policy (known after apply)

      + versioning {
          + enabled = false
        }

      + website (known after apply)
    }

Plan: 7 to add, 0 to change, 0 to destroy.

Changes to Outputs:
  + cloud_run_url            = (known after apply)
  + database_connection_name = (known after apply)
  + storage_bucket           = "tramemo-images"

───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

Note: You didn't use the -out option to save this plan, so Terraform can't guarantee to take exactly these actions if
you run "terraform apply" now.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform>
```


# インフラ構築（無料枠最適化設定）
## 実行コマンド
```
terraform apply -var="container_image=gcr.io/robotic-sky-465306-n5/tramemo-api:latest" -var="db_password=XX.-4Y!*Q4~rh3#7T&BY" -var="environment=production" -var="enable_cost_optimization=true" -auto-approve
```

## 実行ログ
```
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform> terraform apply -var="container_image=gcr.io/robotic-sky-465306-n5/tramemo-api:latest" -var="db_password=XX.-4Y!*Q4~rh3#7T&BY" -var="environment=production" -var="enable_cost_optimization=true" -auto-approve

Terraform used the selected providers to generate the following execution plan. Resource actions are indicated with the
following symbols:
  + create

Terraform will perform the following actions:

  # google_cloud_run_service.main will be created
  + resource "google_cloud_run_service" "main" {
      + autogenerate_revision_name = false
      + id                         = (known after apply)
      + location                   = "asia-northeast1"
      + name                       = "tramemo-api"
      + project                    = (known after apply)
      + status                     = (known after apply)

      + metadata (known after apply)

      + template {
          + metadata {
              + annotations      = {
                  + "autoscaling.knative.dev/maxScale" = "10"
                  + "autoscaling.knative.dev/minScale" = "0"
                  + "autoscaling.knative.dev/target"   = "80"
                }
              + generation       = (known after apply)
              + labels           = (known after apply)
              + name             = (known after apply)
              + namespace        = (known after apply)
              + resource_version = (known after apply)
              + self_link        = (known after apply)
              + uid              = (known after apply)
            }
          + spec {
              + container_concurrency = 80
              + service_account_name  = (known after apply)
              + serving_state         = (known after apply)
              + timeout_seconds       = 300

              + containers {
                  + image = "gcr.io/robotic-sky-465306-n5/tramemo-api:latest"
                  + name  = (known after apply)

                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }
                  + env {
                      # At least one attribute in this block is (or was) sensitive,
                      # so its contents will not be displayed.
                    }

                  + ports (known after apply)

                  + resources {
                      + limits = {
                          + "cpu"    = "1000m"
                          + "memory" = "512Mi"
                        }
                    }

                  + startup_probe (known after apply)
                }
            }
        }

      + traffic {
          + latest_revision = true
          + percent         = 100
          + url             = (known after apply)
        }
    }

  # google_cloud_run_service_iam_member.public will be created
  + resource "google_cloud_run_service_iam_member" "public" {
      + etag     = (known after apply)
      + id       = (known after apply)
      + location = "asia-northeast1"
      + member   = "allUsers"
      + project  = (known after apply)
      + role     = "roles/run.invoker"
      + service  = "tramemo-api"
    }

  # google_sql_database.database will be created
  + resource "google_sql_database" "database" {
      + charset         = (known after apply)
      + collation       = (known after apply)
      + deletion_policy = "DELETE"
      + id              = (known after apply)
      + instance        = "tramemo-db"
      + name            = "tramemo_production"
      + project         = (known after apply)
      + self_link       = (known after apply)
    }

  # google_sql_database_instance.main will be created
  + resource "google_sql_database_instance" "main" {
      + available_maintenance_versions = (known after apply)
      + connection_name                = (known after apply)
      + database_version               = "MYSQL_8_0"
      + deletion_protection            = false
      + dns_name                       = (known after apply)
      + encryption_key_name            = (known after apply)
      + first_ip_address               = (known after apply)
      + id                             = (known after apply)
      + instance_type                  = (known after apply)
      + ip_address                     = (known after apply)
      + maintenance_version            = (known after apply)
      + master_instance_name           = (known after apply)
      + name                           = "tramemo-db"
      + private_ip_address             = (known after apply)
      + project                        = (known after apply)
      + psc_service_attachment_link    = (known after apply)
      + public_ip_address              = (known after apply)
      + region                         = "asia-northeast1"
      + self_link                      = (known after apply)
      + server_ca_cert                 = (known after apply)
      + service_account_email_address  = (known after apply)

      + replica_configuration (known after apply)

      + settings {
          + activation_policy     = "ALWAYS"
          + availability_type     = "ZONAL"
          + connector_enforcement = (known after apply)
          + disk_autoresize       = true
          + disk_autoresize_limit = 0
          + disk_size             = 10
          + disk_type             = "PD_SSD"
          + pricing_plan          = "PER_USE"
          + tier                  = "db-f1-micro"
          + user_labels           = (known after apply)
          + version               = (known after apply)

          + backup_configuration {
              + enabled                        = true
              + start_time                     = "02:00"
              + transaction_log_retention_days = (known after apply)

              + backup_retention_settings {
                  + retained_backups = 3
                  + retention_unit   = "COUNT"
                }
            }

          + ip_configuration {
              + ipv4_enabled = true
              + require_ssl  = true

              + authorized_networks {
                  + name            = "all"
                  + value           = "0.0.0.0/0"
                    # (1 unchanged attribute hidden)
                }
            }

          + location_preference (known after apply)
        }
    }

  # google_sql_user.users will be created
  + resource "google_sql_user" "users" {
      + host                    = (known after apply)
      + id                      = (known after apply)
      + instance                = "tramemo-db"
      + name                    = "tramemo_user"
      + password                = (sensitive value)
      + project                 = (known after apply)
      + sql_server_user_details = (known after apply)
    }

  # google_storage_bucket.frontend will be created
  + resource "google_storage_bucket" "frontend" {
      + force_destroy               = false
      + id                          = (known after apply)
      + labels                      = (known after apply)
      + location                    = "ASIA-NORTHEAST1"
      + name                        = "tramemo-frontend"
      + project                     = (known after apply)
      + public_access_prevention    = (known after apply)
      + self_link                   = (known after apply)
      + storage_class               = "STANDARD"
      + uniform_bucket_level_access = true
      + url                         = (known after apply)

      + soft_delete_policy (known after apply)

      + versioning (known after apply)

      + website {
          + main_page_suffix = "index.html"
          + not_found_page   = "404.html"
        }
    }

  # google_storage_bucket.images will be created
  + resource "google_storage_bucket" "images" {
      + force_destroy               = false
      + id                          = (known after apply)
      + labels                      = (known after apply)
      + location                    = "ASIA-NORTHEAST1"
      + name                        = "tramemo-images"
      + project                     = (known after apply)
      + public_access_prevention    = (known after apply)
      + self_link                   = (known after apply)
      + storage_class               = "STANDARD"
      + uniform_bucket_level_access = true
      + url                         = (known after apply)

      + lifecycle_rule {
          + action {
              + type          = "Delete"
                # (1 unchanged attribute hidden)
            }
          + condition {
              + age                    = 180
              + matches_prefix         = []
              + matches_storage_class  = []
              + matches_suffix         = []
              + with_state             = (known after apply)
                # (3 unchanged attributes hidden)
            }
        }

      + soft_delete_policy (known after apply)

      + versioning {
          + enabled = false
        }

      + website (known after apply)
    }

Plan: 7 to add, 0 to change, 0 to destroy.

Changes to Outputs:
  + cloud_run_url            = (known after apply)
  + database_connection_name = (known after apply)
  + storage_bucket           = "tramemo-images"
google_storage_bucket.frontend: Creating...
google_storage_bucket.images: Creating...
google_sql_database_instance.main: Creating...
google_storage_bucket.images: Creation complete after 2s [id=tramemo-images]
google_storage_bucket.frontend: Creation complete after 2s [id=tramemo-frontend]
google_sql_database_instance.main: Still creating... [00m10s elapsed]
google_sql_database_instance.main: Still creating... [00m20s elapsed]
google_sql_database_instance.main: Still creating... [00m30s elapsed]
google_sql_database_instance.main: Still creating... [00m40s elapsed]
google_sql_database_instance.main: Still creating... [00m50s elapsed]
google_sql_database_instance.main: Still creating... [01m00s elapsed]
google_sql_database_instance.main: Still creating... [01m10s elapsed]
google_sql_database_instance.main: Still creating... [01m20s elapsed]
google_sql_database_instance.main: Still creating... [01m30s elapsed]
google_sql_database_instance.main: Still creating... [01m40s elapsed]
google_sql_database_instance.main: Still creating... [01m50s elapsed]
google_sql_database_instance.main: Still creating... [02m00s elapsed]
google_sql_database_instance.main: Still creating... [02m10s elapsed]
google_sql_database_instance.main: Still creating... [02m20s elapsed]
google_sql_database_instance.main: Still creating... [02m30s elapsed]
google_sql_database_instance.main: Still creating... [02m40s elapsed]
google_sql_database_instance.main: Still creating... [02m50s elapsed]
google_sql_database_instance.main: Still creating... [03m00s elapsed]
google_sql_database_instance.main: Still creating... [03m10s elapsed]
google_sql_database_instance.main: Still creating... [03m20s elapsed]
google_sql_database_instance.main: Still creating... [03m30s elapsed]
google_sql_database_instance.main: Still creating... [03m40s elapsed]
google_sql_database_instance.main: Still creating... [03m50s elapsed]
google_sql_database_instance.main: Still creating... [04m00s elapsed]
google_sql_database_instance.main: Still creating... [04m10s elapsed]
google_sql_database_instance.main: Still creating... [04m20s elapsed]
google_sql_database_instance.main: Still creating... [04m30s elapsed]
google_sql_database_instance.main: Still creating... [04m40s elapsed]
google_sql_database_instance.main: Still creating... [04m50s elapsed]
google_sql_database_instance.main: Still creating... [05m00s elapsed]
google_sql_database_instance.main: Still creating... [05m10s elapsed]
google_sql_database_instance.main: Still creating... [05m20s elapsed]
google_sql_database_instance.main: Still creating... [05m30s elapsed]
google_sql_database_instance.main: Still creating... [05m40s elapsed]
google_sql_database_instance.main: Still creating... [05m50s elapsed]
google_sql_database_instance.main: Still creating... [06m00s elapsed]
google_sql_database_instance.main: Still creating... [06m10s elapsed]
google_sql_database_instance.main: Still creating... [06m20s elapsed]
google_sql_database_instance.main: Still creating... [06m30s elapsed]
google_sql_database_instance.main: Still creating... [06m40s elapsed]
google_sql_database_instance.main: Still creating... [06m50s elapsed]
google_sql_database_instance.main: Still creating... [07m00s elapsed]
google_sql_database_instance.main: Still creating... [07m10s elapsed]
google_sql_database_instance.main: Still creating... [07m20s elapsed]
google_sql_database_instance.main: Still creating... [07m30s elapsed]
google_sql_database_instance.main: Still creating... [07m40s elapsed]
google_sql_database_instance.main: Still creating... [07m50s elapsed]
google_sql_database_instance.main: Still creating... [08m00s elapsed]
google_sql_database_instance.main: Still creating... [08m10s elapsed]
google_sql_database_instance.main: Still creating... [08m20s elapsed]
google_sql_database_instance.main: Still creating... [08m30s elapsed]
google_sql_database_instance.main: Still creating... [08m40s elapsed]
google_sql_database_instance.main: Still creating... [08m50s elapsed]
google_sql_database_instance.main: Still creating... [09m00s elapsed]
google_sql_database_instance.main: Still creating... [09m10s elapsed]
google_sql_database_instance.main: Still creating... [09m20s elapsed]
google_sql_database_instance.main: Still creating... [09m30s elapsed]
google_sql_database_instance.main: Still creating... [09m40s elapsed]
google_sql_database_instance.main: Still creating... [09m50s elapsed]
google_sql_database_instance.main: Still creating... [10m00s elapsed]
google_sql_database_instance.main: Still creating... [10m10s elapsed]
google_sql_database_instance.main: Still creating... [10m20s elapsed]
google_sql_database_instance.main: Still creating... [10m30s elapsed]
google_sql_database_instance.main: Still creating... [10m40s elapsed]
google_sql_database_instance.main: Still creating... [10m50s elapsed]
google_sql_database_instance.main: Still creating... [11m00s elapsed]
google_sql_database_instance.main: Still creating... [11m10s elapsed]
google_sql_database_instance.main: Still creating... [11m20s elapsed]
google_sql_database_instance.main: Still creating... [11m30s elapsed]
google_sql_database_instance.main: Still creating... [11m40s elapsed]
google_sql_database_instance.main: Still creating... [11m50s elapsed]
google_sql_database_instance.main: Still creating... [12m00s elapsed]
google_sql_database_instance.main: Still creating... [12m10s elapsed]
google_sql_database_instance.main: Still creating... [12m20s elapsed]
google_sql_database_instance.main: Still creating... [12m30s elapsed]
google_sql_database_instance.main: Still creating... [12m40s elapsed]
google_sql_database_instance.main: Still creating... [12m50s elapsed]
google_sql_database_instance.main: Still creating... [13m00s elapsed]
google_sql_database_instance.main: Still creating... [13m10s elapsed]
google_sql_database_instance.main: Still creating... [13m20s elapsed]
google_sql_database_instance.main: Still creating... [13m30s elapsed]
google_sql_database_instance.main: Still creating... [13m40s elapsed]
google_sql_database_instance.main: Still creating... [13m50s elapsed]
google_sql_database_instance.main: Still creating... [14m00s elapsed]
google_sql_database_instance.main: Still creating... [14m10s elapsed]
google_sql_database_instance.main: Still creating... [14m20s elapsed]
google_sql_database_instance.main: Still creating... [14m30s elapsed]
google_sql_database_instance.main: Still creating... [14m40s elapsed]
google_sql_database_instance.main: Still creating... [14m50s elapsed]
google_sql_database_instance.main: Still creating... [15m00s elapsed]
google_sql_database_instance.main: Still creating... [15m10s elapsed]
google_sql_database_instance.main: Still creating... [15m20s elapsed]
google_sql_database_instance.main: Still creating... [15m30s elapsed]
google_sql_database_instance.main: Still creating... [15m40s elapsed]
google_sql_database_instance.main: Still creating... [15m50s elapsed]
google_sql_database_instance.main: Still creating... [16m00s elapsed]
google_sql_database_instance.main: Still creating... [16m10s elapsed]
google_sql_database_instance.main: Still creating... [16m20s elapsed]
google_sql_database_instance.main: Still creating... [16m30s elapsed]
google_sql_database_instance.main: Still creating... [16m40s elapsed]
google_sql_database_instance.main: Still creating... [16m50s elapsed]
google_sql_database_instance.main: Still creating... [17m00s elapsed]
google_sql_database_instance.main: Still creating... [17m10s elapsed]
google_sql_database_instance.main: Still creating... [17m20s elapsed]
google_sql_database_instance.main: Still creating... [17m30s elapsed]
google_sql_database_instance.main: Still creating... [17m40s elapsed]
google_sql_database_instance.main: Still creating... [17m50s elapsed]
google_sql_database_instance.main: Still creating... [18m00s elapsed]
google_sql_database_instance.main: Still creating... [18m10s elapsed]
google_sql_database_instance.main: Still creating... [18m20s elapsed]
google_sql_database_instance.main: Creation complete after 18m23s [id=tramemo-db]
google_sql_database.database: Creating...
google_sql_user.users: Creating...
google_sql_database.database: Creation complete after 0s [id=projects/robotic-sky-465306-n5/instances/tramemo-db/databases/tramemo_production]
google_sql_user.users: Creation complete after 1s [id=tramemo_user//tramemo-db]
google_cloud_run_service.main: Creating...
google_cloud_run_service.main: Still creating... [00m10s elapsed]
google_cloud_run_service.main: Still creating... [00m20s elapsed]
google_cloud_run_service.main: Still creating... [00m30s elapsed]
google_cloud_run_service.main: Still creating... [00m40s elapsed]
google_cloud_run_service.main: Still creating... [00m50s elapsed]
google_cloud_run_service.main: Still creating... [01m00s elapsed]
google_cloud_run_service.main: Still creating... [01m10s elapsed]
google_cloud_run_service.main: Still creating... [01m20s elapsed]
google_cloud_run_service.main: Still creating... [01m30s elapsed]
google_cloud_run_service.main: Still creating... [01m40s elapsed]
google_cloud_run_service.main: Creation complete after 1m48s [id=locations/asia-northeast1/namespaces/robotic-sky-465306-n5/services/tramemo-api]
google_cloud_run_service_iam_member.public: Creating...
google_cloud_run_service_iam_member.public: Creation complete after 5s [id=v1/projects/robotic-sky-465306-n5/locations/asia-northeast1/services/tramemo-api/roles/run.invoker/allUsers]

Apply complete! Resources: 7 added, 0 changed, 0 destroyed.

Outputs:

cloud_run_url = "https://tramemo-api-zthkxnsiza-an.a.run.app"
database_connection_name = "robotic-sky-465306-n5:asia-northeast1:tramemo-db"
storage_bucket = "tramemo-images"
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra\gcp\terraform>
```

### ステップ6: Dockerイメージビルド・プッシュ
#### 6.2 イメージビルド・プッシュ
```bash
# プロジェクトルートで実行
docker build -t gcr.io/robotic-sky-465306-n5/tramemo-api:latest -f infra/gcp/docker/Dockerfile .
docker push gcr.io/robotic-sky-465306-n5/tramemo-api:latest
```

```
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend> gcloud auth configure-docker
Adding credentials for all GCR repositories.
WARNING: A long list of credential helpers may cause delays running 'docker build'. We recommend passing the registry name to configure only the registry you are using.
After update, the following will be written to your Docker config file located at [C:\Users\USER\.docker\config.json]:
 {
  "credHelpers": {
    "gcr.io": "gcloud",
    "us.gcr.io": "gcloud",
    "eu.gcr.io": "gcloud",
    "asia.gcr.io": "gcloud",
    "staging-k8s.gcr.io": "gcloud",
    "marketplace.gcr.io": "gcloud"
  }
}

Do you want to continue (Y/n)?  y

Docker configuration file updated.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend> docker build -t gcr.io/robotic-sky-465306-n5/traMemo-api:latest -f infra/gcp/docker/Dockerfile .
ERROR: error during connect: Head "http://%2F%2F.%2Fpipe%2FdockerDesktopLinuxEngine/_ping": open //./pipe/dockerDesktopLinuxEngine: The system cannot find the file specified.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
[+] Building 65.8s (7/13)                                                                          docker:desktop-linux
 => [internal] load build definition from Dockerfile                                                               0.1s
 => => transferring dockerfile: 1.03kB                                                                             0.0s
 => [internal] load metadata for docker.io/library/composer:latest                                                 2.5s
 => [internal] load metadata for docker.io/library/php:8.2-fpm                                                     2.5sa
[+] Building 65.8s (7/13)                                                                          docker:desktop-linux
 => [internal] load build definition from Dockerfile                                                               0.1s
 => => transferring dockerfile: 1.03kB                                                                             0.0s
[+] Building 175.1s (12/13)                                                                        docker:desktop-linuxa
 => [internal] load build definition from Dockerfile                                                               0.1s
 => => transferring dockerfile: 1.03kB                                                                             0.0s
 => [internal] load metadata for docker.io/library/composer:latest                                                 2.5s
 => [internal] load metadata for docker.io/library/php:8.2-fpm                                                     2.5s
 => [internal] load .dockerignore                                                                                  0.1s
 => => transferring context: 2B                                                                                    0.0s
 => [stage-0 1/7] FROM docker.io/library/php:8.2-fpm@sha256:760a51ad7c03efaf81d682924f7419734fb67abe57441426f2d7  23.8s
 => => resolve docker.io/library/php:8.2-fpm@sha256:760a51ad7c03efaf81d682924f7419734fb67abe57441426f2d7b681ba64f  0.1s
 => => sha256:a3c60b9a08e9bec6380e0e08a568c70670438ca00ced9485430940afa999aed1 9.18kB / 9.18kB                     0.3s
 => => sha256:76aca633092544d6d87ced15ad18a31e8e5cee615f05f99238c5536fc11667c7 246B / 246B                         0.4s
 => => sha256:c659f0c7018344d159acb166ff8689e90699f4d2ba302c360c993b819cfa4b12 252B / 252B                         0.4s
 => => sha256:3cb3c2a4d9322fe2ddabae1cc4348b2e765077431cb5edb16c682a86a5c9d420 2.45kB / 2.45kB                     0.4s
 => => sha256:a5ca9d95af5090fbb8f46c6f1c1af5025678ffdfbe4be8a87cf3e69d6cea0796 27.59MB / 27.59MB                  11.2s
 => => sha256:14b78a104d740a410b6c172d74b0394a78b9059738cfbb3191c511a3327121a1 486B / 486B                         0.6s
 => => sha256:a26ebe53368dcdb1ed1b27ac242ecc5b374ab3f60f192ca3654f8c9178147ec5 12.27MB / 12.27MB                   3.1s
 => => sha256:073cdee253a41348251bcfadec9f8e393b726e99f87fe81ab5b0ece83868df59 104.33MB / 104.33MB                17.5s
 => => sha256:6d7a2d9b7912ff8c0587fd7128e058a4253b1ed1389e46793bedea37fee33af4 225B / 225B                         0.4s
[+] Building 186.5s (14/14) FINISHED                                                               docker:desktop-linux
 => [internal] load build definition from Dockerfile                                                               0.1s
 => => transferring dockerfile: 1.03kB                                                                             0.0s
 => [internal] load metadata for docker.io/library/composer:latest                                                 2.5s
 => [internal] load metadata for docker.io/library/php:8.2-fpm                                                     2.5s
 => [internal] load .dockerignore                                                                                  0.1s
 => => transferring context: 2B                                                                                    0.0s
 => [stage-0 1/7] FROM docker.io/library/php:8.2-fpm@sha256:760a51ad7c03efaf81d682924f7419734fb67abe57441426f2d7  23.8s
 => => resolve docker.io/library/php:8.2-fpm@sha256:760a51ad7c03efaf81d682924f7419734fb67abe57441426f2d7b681ba64f  0.1s
 => => sha256:a3c60b9a08e9bec6380e0e08a568c70670438ca00ced9485430940afa999aed1 9.18kB / 9.18kB                     0.3s
 => => sha256:76aca633092544d6d87ced15ad18a31e8e5cee615f05f99238c5536fc11667c7 246B / 246B                         0.4s
 => => sha256:c659f0c7018344d159acb166ff8689e90699f4d2ba302c360c993b819cfa4b12 252B / 252B                         0.4s
 => => sha256:3cb3c2a4d9322fe2ddabae1cc4348b2e765077431cb5edb16c682a86a5c9d420 2.45kB / 2.45kB                     0.4s
 => => sha256:a5ca9d95af5090fbb8f46c6f1c1af5025678ffdfbe4be8a87cf3e69d6cea0796 27.59MB / 27.59MB                  11.2s
 => => sha256:14b78a104d740a410b6c172d74b0394a78b9059738cfbb3191c511a3327121a1 486B / 486B                         0.6s
 => => sha256:a26ebe53368dcdb1ed1b27ac242ecc5b374ab3f60f192ca3654f8c9178147ec5 12.27MB / 12.27MB                   3.1s
 => => sha256:073cdee253a41348251bcfadec9f8e393b726e99f87fe81ab5b0ece83868df59 104.33MB / 104.33MB                17.5s
 => => sha256:6d7a2d9b7912ff8c0587fd7128e058a4253b1ed1389e46793bedea37fee33af4 225B / 225B                         0.4s
 => => sha256:497ffba6e0f4b234746be4786b1337e7ce459b422d9dfcda2081622d65e74584 226B / 226B                         0.5s
 => => sha256:3da95a905ed546f99c4564407923a681757d89651a388ec3f1f5e9bf5ed0b39d 28.23MB / 28.23MB                   5.9s
 => => extracting sha256:3da95a905ed546f99c4564407923a681757d89651a388ec3f1f5e9bf5ed0b39d                          1.1s
 => => extracting sha256:497ffba6e0f4b234746be4786b1337e7ce459b422d9dfcda2081622d65e74584                          0.1s
 => => extracting sha256:073cdee253a41348251bcfadec9f8e393b726e99f87fe81ab5b0ece83868df59                          2.8s
 => => extracting sha256:6d7a2d9b7912ff8c0587fd7128e058a4253b1ed1389e46793bedea37fee33af4                          0.1s
 => => extracting sha256:a26ebe53368dcdb1ed1b27ac242ecc5b374ab3f60f192ca3654f8c9178147ec5                          0.1s
 => => extracting sha256:14b78a104d740a410b6c172d74b0394a78b9059738cfbb3191c511a3327121a1                          0.0s
 => => extracting sha256:a5ca9d95af5090fbb8f46c6f1c1af5025678ffdfbe4be8a87cf3e69d6cea0796                          0.7s
 => => extracting sha256:3cb3c2a4d9322fe2ddabae1cc4348b2e765077431cb5edb16c682a86a5c9d420                          0.0s
 => => extracting sha256:c659f0c7018344d159acb166ff8689e90699f4d2ba302c360c993b819cfa4b12                          0.1s
 => => extracting sha256:76aca633092544d6d87ced15ad18a31e8e5cee615f05f99238c5536fc11667c7                          0.1s
 => => extracting sha256:4f4fb700ef54461cfa02571ae0db9a0dc1e0cdb5577484a6d75e68dc38e8acc1                          0.0s
 => => extracting sha256:a3c60b9a08e9bec6380e0e08a568c70670438ca00ced9485430940afa999aed1                          0.1s
 => [internal] load build context                                                                                 59.3s
 => => transferring context: 73.76MB                                                                              59.0s
 => FROM docker.io/library/composer:latest@sha256:69d57c07ed077bc22d6e584202b6d9160f636abdb6df25c7c437ded589b3fa6  2.2s
 => => resolve docker.io/library/composer:latest@sha256:69d57c07ed077bc22d6e584202b6d9160f636abdb6df25c7c437ded58  0.1s
 => => sha256:a3e141382acfb75c4a31d782c5f0837bb164ef89d645af35d1c815bcdfcf5cac 93B / 93B                           0.2s
 => => sha256:27cc4579c8bd93cd33c1b651f3f52e56ca67655b23b2fc99ae1954c25cfc8630 419B / 419B                         0.6s
 => => sha256:218c0a682557e725de4b4ae1b3d83acabd53d9ce5f23a45ef3e1b3721fea1262 976.41kB / 976.41kB                 0.7s
 => => extracting sha256:218c0a682557e725de4b4ae1b3d83acabd53d9ce5f23a45ef3e1b3721fea1262                          0.1s
 => => extracting sha256:27cc4579c8bd93cd33c1b651f3f52e56ca67655b23b2fc99ae1954c25cfc8630                          0.1s
 => => extracting sha256:a3e141382acfb75c4a31d782c5f0837bb164ef89d645af35d1c815bcdfcf5cac                          0.1s
 => [stage-0 2/7] RUN apt-get update && apt-get install -y     git     curl     libpng-dev     libonig-dev     l  70.1s
 => [stage-0 3/7] COPY --from=composer:latest /usr/bin/composer /usr/bin/composer                                  0.2s
 => [stage-0 4/7] WORKDIR /var/www/html                                                                            0.1s
 => [stage-0 5/7] COPY src/ .                                                                                      1.3s
 => [stage-0 6/7] RUN composer install --optimize-autoloader --no-dev                                              2.0s
 => [stage-0 7/7] RUN chown -R www-data:www-data /var/www/html     && chmod -R 755 /var/www/html/storage     &&   76.5s
 => exporting to image                                                                                             9.5s
 => => exporting layers                                                                                            5.2s
 => => exporting manifest sha256:777ecab1e3862e825b556f57ffad85f680c39239304e03455a284818f20e3285                  0.0s
 => => exporting config sha256:2b659f44061d30b59b22c3396a6b9991423ea1dfb6e165fc6960e6430464f62d                    0.0s
 => => exporting attestation manifest sha256:ad27749d49ea357a12fa1c2407a24fe387409d51e99494eebe67cba5288ed435      0.1s
 => => exporting manifest list sha256:da2a0a12182e8f676ec19cf0895370062e38322184c76818e0a6a9ca4af64e9b             0.0s
 => => naming to gcr.io/robotic-sky-465306-n5/tramemo-api:latest                                                   0.0s
 => => unpacking to gcr.io/robotic-sky-465306-n5/tramemo-api:latest                                                3.9s
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend> docker push gcr.io/robotic-sky-465306-n5/tramemo-api:latest
The push refers to repository [gcr.io/robotic-sky-465306-n5/tramemo-api]
1b38f74df04e: Pushed
06efb1cb663f: Pushed
14b78a104d74: Layer already exists
24adf4ca7e69: Pushed
e0f5ad01218c: Pushed
c659f0c70183: Layer already exists
6d7a2d9b7912: Layer already exists
9ffbf3f821a9: Pushed
4f4fb700ef54: Layer already exists
073cdee253a4: Layer already exists
79bf3c9d7dba: Pushed
a26ebe53368d: Layer already exists
497ffba6e0f4: Layer already exists
3da95a905ed5: Layer already exists
3cb3c2a4d932: Layer already exists
a5ca9d95af50: Layer already exists
a3c60b9a08e9: Layer already exists
76aca6330925: Layer already exists
latest: digest: sha256:da2a0a12182e8f676ec19cf0895370062e38322184c76818e0a6a9ca4af64e9b size: 856
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend>
```

### ステップ7: Cloud Runデプロイ
#### 7.1 サービスデプロイ（無料枠最適化）
```bash
# Cloud Runにデプロイ（無料枠最適化設定）
gcloud run deploy tramemo-api --image gcr.io/robotic-sky-465306-n5/tramemo-api:latest --region asia-northeast1  --platform managed --allow-unauthenticated --min-instances=0 --max-instances=10 --concurrency=80 --timeout=300 --memory=512Mi --cpu=1 --set-env-vars="APP_ENV=production" --set-env-vars="DB_HOST=/cloudsql/robotic-sky-465306-n5:asia-northeast1:tramemo-db" --set-env-vars="DB_DATABASE=tramemo_production" --set-env-vars="DB_USERNAME=tramemo_user" --set-env-vars="GOOGLE_CLOUD_STORAGE_BUCKET=tramemo-images" --add-cloudsql-instances=robotic-sky-465306-n5:asia-northeast1:tramemo-db
```

```実行ログ
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud run deploy tramemo-api --image gcr.io/robotic-sky-465306-n5/tramemo-api:latest --region asia-northeast1  --platform managed --allow-unauthenticated --min-instances=0 --max-instances=10 --concurrency=80 --timeout=300 --memory=512Mi --cpu=1 --set-env-vars="APP_ENV=production" --set-env-vars="DB_HOST=/cloudsql/robotic-sky-465306-n5:asia-northeast1:tramemo-db" --set-env-vars="DB_DATABASE=tramemo_production" --set-env-vars="DB_USERNAME=tramemo_user" --set-env-vars="GOOGLE_CLOUD_STORAGE_BUCKET=tramemo-images" --add-cloudsql-instances=robotic-sky-465306-n5:asia-northeast1:tramemo-db
Deploying container to Cloud Run service [tramemo-api] in project [robotic-sky-465306-n5] region [asia-northeast1]
✓ Deploying... Done.
  ✓ Creating Revision...
  ✓ Routing traffic...
  ✓ Setting IAM Policy...
Done.
Service [tramemo-api] revision [tramemo-api-00002-csf] has been deployed and is serving 100 percent of traffic.
Service URL: https://tramemo-api-681273518185.asia-northeast1.run.app
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra>
```

#### 7.2 データベースマイグレーション
```bash
# マイグレーション実行
gcloud run jobs create migrate --image gcr.io/robotic-sky-465306-n5/tramemo-api:latest --command="php" --args="artisan,migrate,--force" --region=asia-northeast1 --set-cloudsql-instances=robotic-sky-465306-n5:asia-northeast1:tramemo-db --set-env-vars="DB_HOST=/cloudsql/robotic-sky-465306-n5:asia-northeast1:tramemo-db" --set-env-vars="DB_DATABASE=tramemo_production" --set-env-vars="DB_USERNAME=tramemo_user" --set-secrets="DB_PASSWORD=db-password:latest"
```

```実行結果
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud run jobs create migrate --image gcr.io/robotic-sky-465306-n5/tramemo-api:latest --command="php" --args="artisan,migrate,--force" --region=asia-northeast1 --set-cloudsql-instances=robotic-sky-465306-n5:asia-northeast1:tramemo-db --set-env-vars="DB_HOST=/cloudsql/robotic-sky-465306-n5:asia-northeast1:tramemo-db" --set-env-vars="DB_DATABASE=tramemo_production" --set-env-vars="DB_USERNAME=tramemo_user" --set-secrets="DB_PASSWORD=db-password:latest"
Creating Cloud Run job [migrate] in project [robotic-sky-465306-n5] region [asia-northeast1]
✓ Creating job... Done.
Done.
Job [migrate] has successfully been created.

To execute this job, use:
gcloud run jobs execute migrate
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra>
```

### 6.2 Cloud Storageへアップロード
```bash
# バケット作成（初回のみ）
gsutil mb -l asia-northeast1 gs://tramemo-frontend
# バケットを公開設定
gsutil iam ch allUsers:objectViewer gs://tramemo-frontend
# 静的サイト設定
gsutil web set -m index.html -e 404.html gs://tramemo-frontend
# ビルドファイルをアップロード
gsutil -m cp -r dist/* gs://tramemo-frontend/
# キャッシュ設定
gsutil -m setmeta -h "Cache-Control:public, max-age=31536000" gs://tramemo-frontend/assets/**/*
gsutil -m setmeta -h "Cache-Control:public, max-age=0" gs://tramemo-frontend/index.html
# 不要なソースマップファイル削除
gsutil -m rm gs://tramemo-frontend/**/*.map
```

```実行結果
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> gsutil mb -l asia-northeast1 gs://tramemo-frontend
Creating gs://tramemo-frontend/...
ServiceException: 409 A Cloud Storage bucket named 'tramemo-frontend' already exists. Try another name. Bucket names must be globally unique across all Google Cloud projects, including those outside of your organization.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> gsutil iam ch allUsers:objectViewer gs://tramemo-frontend
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> gsutil web set -m index.html -e 404.html gs://tramemo-frontend
Setting website configuration on gs://tramemo-frontend/...
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> gsutil -m cp -r dist/* gs://tramemo-frontend/
Copying file://dist\index.html [Content-Type=text/html]...
Copying file://dist\placeholder.svg [Content-Type=image/svg+xml]...
Copying file://dist\assets\index-XjDdWhjF.css [Content-Type=text/css]...
Copying file://dist\vite.svg [Content-Type=image/svg+xml]...
Copying file://dist\assets\index-CwT3RMK3.js [Content-Type=text/javascript]...
- [5/5 files][782.8 KiB/782.8 KiB] 100% Done
Operation completed over 5 objects/782.8 KiB.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> gsutil -m setmeta -h "Cache-Control:public, max-age=31536000" gs://tramemo-frontend/assets/**/*
Setting metadata on gs://tramemo-frontend/assets/index-CwT3RMK3.js...
Setting metadata on gs://tramemo-frontend/assets/index-XjDdWhjF.css...
/ [2/2 objects] 100% Done
Operation completed over 2 objects.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> gsutil -m setmeta -h "Cache-Control:public, max-age=0" gs://tramemo-frontend/index.html
Setting metadata on gs://tramemo-frontend/index.html...
/ [1/1 objects] 100% Done
Operation completed over 1 objects.
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
```

### 7.2 ジョブ実行
```bash
# マイグレーション実行
gcloud run jobs execute migrate --region=asia-northeast1
```

```実行ログ

```

## 8. Cloud CDN/ロードバランサ設定

```bash
# 外部IPアドレスを予約
gcloud compute addresses create tramemo-frontend-ip --global
# バックエンドバケットを作成
gcloud compute backend-buckets create tramemo-frontend-backend --gcs-bucket-name=tramemo-frontend
# URLマップを作成
gcloud compute url-maps create tramemo-frontend-map --default-backend-bucket=tramemo-frontend-backend
# キャッシュポリシーを設定
gcloud compute url-maps update tramemo-frontend-map --default-backend-bucket=tramemo-frontend-backend --cache-key-include-query-string=false
# HTTPSプロキシを作成
gcloud compute target-https-proxies create tramemo-frontend-https-proxy --url-map=tramemo-frontend-map --ssl-certificates=tramemo-frontend-cert
# 転送ルールを作成
gcloud compute global-forwarding-rules create tramemo-frontend-rule --address=tramemo-frontend-ip --target-https-proxy=tramemo-frontend-https-proxy --global --ports=443
```

```実行結果（失敗のため、切り戻し作業実施済）
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute ssl-certificates create tramemo-frontend-cert
ERROR: (gcloud.compute.ssl-certificates.create) Exactly one of (--domains | --certificate --private-key) must be specified.
Usage: gcloud compute ssl-certificates create NAME (--domains=DOMAIN,[DOMAIN,...] | --certificate=LOCAL_FILE_PATH --private-key=LOCAL_FILE_PATH) [optional flags]
  optional flags may be  --certificate | --description | --domains | --global |
                         --help | --private-key | --region

For detailed information on this command and its flags, run:
  gcloud compute ssl-certificates create --help
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute addresses create tramemo-frontend-ip --global
Created [https://www.googleapis.com/compute/v1/projects/robotic-sky-465306-n5/global/addresses/tramemo-frontend-ip].
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute backend-buckets create tramemo-frontend-backend --gcs-bucket-name=tramemo-frontend
Created [https://www.googleapis.com/compute/v1/projects/robotic-sky-465306-n5/global/backendBuckets/tramemo-frontend-backend].
NAME                      GCS_BUCKET_NAME   ENABLE_CDN
tramemo-frontend-backend  tramemo-frontend  False
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute url-maps create tramemo-frontend-map --default-backend-bucket=tramemo-frontend-backend
Created [https://www.googleapis.com/compute/v1/projects/robotic-sky-465306-n5/global/urlMaps/tramemo-frontend-map].
NAME                  DEFAULT_SERVICE
tramemo-frontend-map  backendBuckets/tramemo-frontend-backend
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute url-maps update tramemo-frontend-map --default-backend-bucket=tramemo-frontend-backend --cache-key-include-query-string=false
ERROR: (gcloud.compute.url-maps) Invalid choice: 'update'.
Maybe you meant:
  gcloud compute backend-buckets
  gcloud compute url-maps

To search the help text of gcloud commands, run:
  gcloud help -- SEARCH_TERMS
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute backend-buckets update tramemo-frontend-backend --cache-key-include-query-string=false
ERROR: (gcloud.compute.backend-buckets.update) unrecognized arguments: --cache-key-include-query-string=false (did you mean '--cache-key-query-string-whitelist'?)

To search the help text of gcloud commands, run:
  gcloud help -- SEARCH_TERMS
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute url-maps describe tramemo-frontend-map
creationTimestamp: '2025-07-15T19:30:49.537-07:00'
defaultService: https://www.googleapis.com/compute/v1/projects/robotic-sky-465306-n5/global/backendBuckets/tramemo-frontend-backend
fingerprint: 0FVf7pQmN_8=
id: '1837427019916947510'
kind: compute#urlMap
name: tramemo-frontend-map
selfLink: https://www.googleapis.com/compute/v1/projects/robotic-sky-465306-n5/global/urlMaps/tramemo-frontend-map
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute backend-buckets list
NAME                      GCS_BUCKET_NAME   ENABLE_CDN
tramemo-frontend-backend  tramemo-frontend  False
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute target-https-proxies create tramemo-frontend-https-proxy --url-map=tramemo-frontend-map
ERROR: (gcloud.compute.target-https-proxies.create) Could not fetch resource:
 - Certificate Map or at least 1 SSL certificate must be specified for TargetHttpsProxy creation.

PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra> gcloud compute url-maps delete tramemo-frontend-map
The following url maps will be deleted:
 - [tramemo-frontend-map]

Do you want to continue (Y/n)?  y

Deleted [https://www.googleapis.com/compute/v1/projects/robotic-sky-465306-n5/global/urlMaps/tramemo-frontend-map].
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_backend\infra>
```

# フロントプロジェクトのCloud Runデプロイ
## 4. DockerイメージのビルドとGCRへのpush

```sh
# Dockerビルド
# プロジェクトルートで実行
docker build -t gcr.io/robotic-sky-465306-n5/tramemo-front:latest -f infra/gcp_front/docker/Dockerfile .
docker push gcr.io/robotic-sky-465306-n5/tramemo-front:latest
```

```実行結果
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> docker build -t gcr.io/robotic-sky-465306-n5/tramemo-front[+] Building 37.2s (15/15) FINISHED                                                                docker:desktop-linux
 => [internal] load build definition from Dockerfile                                                               0.0s
 => => transferring dockerfile: 612B                                                                               0.0s
 => [internal] load metadata for docker.io/library/nginx:alpine                                                    1.5s
 => [internal] load metadata for docker.io/library/node:20                                                         1.5s
 => [internal] load .dockerignore                                                                                  0.0s
 => => transferring context: 2B                                                                                    0.0s
 => [build 1/6] FROM docker.io/library/node:20@sha256:7413ddc70ce25fbd9469d5b7ce2200fd793a55b5c57b9d87cf6c2866828  0.1s
 => => resolve docker.io/library/node:20@sha256:7413ddc70ce25fbd9469d5b7ce2200fd793a55b5c57b9d87cf6c286682808573   0.1s
 => [stage-1 1/3] FROM docker.io/library/nginx:alpine@sha256:6054a84cbe362215f2e2aa83e01fe29afa9a8616a5f43bceffe6  0.1s
 => => resolve docker.io/library/nginx:alpine@sha256:6054a84cbe362215f2e2aa83e01fe29afa9a8616a5f43bceffe6d1ecb054  0.1s
 => [internal] load build context                                                                                  2.0s
 => => transferring context: 3.27MB                                                                                1.9s
 => CACHED [build 2/6] WORKDIR /app                                                                                0.0s
 => CACHED [build 3/6] COPY ../../../package.json ../../../package-lock.json ./                                    0.0s
 => CACHED [build 4/6] RUN npm ci                                                                                  0.0s
 => [build 5/6] COPY ../../../ ./                                                                                 22.7s
 => [build 6/6] RUN npm run build                                                                                  9.1s
 => CACHED [stage-1 2/3] COPY --from=build /app/dist /usr/share/nginx/html                                         0.0s
 => CACHED [stage-1 3/3] COPY infra/gcp_front/docker/nginx.conf /etc/nginx/conf.d/default.conf                     0.0s
 => exporting to image                                                                                             0.2s
 => => exporting layers                                                                                            0.0s
 => => exporting manifest sha256:c26614f302e841d4cc1eb9a0bd3b0536e45f1291e1a42036224ee23d7eaad81f                  0.0s
 => => exporting config sha256:60d9ea8b1a8d45635f426032ebc0798d035e0c578a5ae5c031014c5f88ec38e4                    0.0s
 => => exporting attestation manifest sha256:0440369d673e83f73b86979b172adf7bed7c561fd6a05043a6b4df50dd76709c      0.1s
 => => exporting manifest list sha256:2f9c5053fac26bded40053bbbc8195be79292024150c1e6008cfed087a90a5ad             0.0s
 => => naming to gcr.io/robotic-sky-465306-n5/tramemo-front:latest                                                 0.0s
 => => unpacking to gcr.io/robotic-sky-465306-n5/tramemo-front:latest                                              0.0s
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> docker push gcr.io/robotic-sky-465306-n5/tramemo-front:latest
The push refers to repository [gcr.io/robotic-sky-465306-n5/tramemo-front]
a5585638209e: Layer already exists
fd372c3c84a2: Layer already exists
c1d2dc189e38: Layer already exists
071448bbc4a8: Pushed
9824c27679d3: Layer already exists
bdaad27fd04a: Layer already exists
f23865b38cc6: Layer already exists
82bc6f0e14b4: Pushed
828fa206d77b: Layer already exists
e49ceb6007a1: Pushed
958a74d6a238: Layer already exists
latest: digest: sha256:2f9c5053fac26bded40053bbbc8195be79292024150c1e6008cfed087a90a5ad size: 856
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>
```

## 5. Cloud Runでデプロイ
```sh
gcloud run deploy tramemo-front --image gcr.io/robotic-sky-465306-n5/tramemo-front:latest --platform managed --region asia-northeast1 --allow-unauthenticated --min-instances=0 --max-instances=10 --concurrency=80 --timeout=300 --memory=512Mi --cpu=1
```

```
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend> gcloud run deploy tramemo-front --image gcr.io/robotic-sky-465306-n5/tramemo-front:latest --platform managed --region asia-northeast1 --allow-unauthenticated --min-instances=0 --max-instances=10 --concurrency=80 --timeout=300 --memory=512Mi --cpu=1
Deploying container to Cloud Run service [tramemo-front] in project [robotic-sky-465306-n5] region [asia-northeast1]
X Deploying new service...
  - Creating Revision...
  . Routing traffic...
  ✓ Setting IAM Policy...
Deployment failed
ERROR: (gcloud.run.deploy) Revision 'tramemo-front-00001-gjw' is not ready and cannot serve traffic. The user-provided container failed to start and listen on the port defined provided by the PORT=8080 environment variable within the allocated timeout. This can happen when the container port is misconfigured or if the timeout is too short. The health check timeout can be extended. Logs for this revision might contain more information.

Logs URL: https://console.cloud.google.com/logs/viewer?project=robotic-sky-465306-n5&resource=cloud_run_revision/service_name/tramemo-front/revision_name/tramemo-front-00001-gjw&advancedFilter=resource.type%3D%22cloud_run_revision%22%0Aresource.labels.service_name%3D%22tramemo-front%22%0Aresource.labels.revision_name%3D%22tramemo-front-00001-gjw%22
For more troubleshooting guidance, see https://cloud.google.com/run/docs/troubleshooting#container-failed-to-start
PS C:\Users\USER\Desktop\DockerProject\TraMemo_prot_frontend>

↓

Cloud Runは環境変数PORT（デフォルト8080）で指定されたポートでHTTPサーバが起動していることを必須としています。
Nginxやアプリが8080番でリッスンしていない場合、Cloud Runは「起動失敗」とみなします。

nginx.confのlistenを8080に修正
DockerfileのEXPOSEも8080に修正
Docker buildしアップロード後、再度Runにデプロイで解決
```