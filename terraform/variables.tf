variable "db_database" {
  description = "Nom de la base de données Laravel"
  type        = string
  default     = "cnc_connect"
}

variable "db_username" {
  description = "Utilisateur PostgreSQL"
  type        = string
  default     = "laravel"
}

variable "db_password" {
  description = "Mot de passe PostgreSQL"
  type        = string
  sensitive   = true
}

variable "app_key" {
  description = "Clé d'application Laravel"
  type        = string
  sensitive   = true
}

variable "docker_image" {
  description = "Image Docker de l'application"
  type        = string
  default     = "stevetetchoup/cnc-connect-api:latest"
}
