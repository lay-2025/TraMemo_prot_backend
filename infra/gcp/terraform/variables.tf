variable "project_id" {
  description = "GCP Project ID"
  type        = string
  default     = "robotic-sky-465306-n5"
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