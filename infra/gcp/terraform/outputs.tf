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