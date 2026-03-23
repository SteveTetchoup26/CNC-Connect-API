output "app_url" {
  description = "URL de l'application"
  value       = "http://localhost:8080"
}

output "grafana_url" {
  description = "URL de Grafana"
  value       = "http://localhost:3001"
}

output "prometheus_url" {
  description = "URL de Prometheus"
  value       = "http://localhost:9090"
}

output "network_name" {
  description = "Nom du réseau Docker"
  value       = docker_network.sgee_network.name
}
