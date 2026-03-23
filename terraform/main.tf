terraform {
  required_providers {
    docker = {
      source  = "kreuzwerker/docker"
      version = "~> 3.0"
    }
  }
}

provider "docker" {
  host = "npipe:////.//pipe//docker_engine"  # Windows Docker Desktop
}

# ── Réseau ────────────────────────────────────────────────────────────
resource "docker_network" "sgee_network" {
  name   = "sgee_network"
  driver = "bridge"
}

# ── Volumes ───────────────────────────────────────────────────────────
resource "docker_volume" "pgdata" {
  name = "pgdata"
}

resource "docker_volume" "redisdata" {
  name = "redisdata"
}

resource "docker_volume" "prometheusdata" {
  name = "prometheusdata"
}

resource "docker_volume" "grafanadata" {
  name = "grafanadata"
}

# ── PostgreSQL ────────────────────────────────────────────────────────
resource "docker_container" "postgres" {
  name  = "laravel_db"
  image = "postgres:15-alpine"

  restart = "unless-stopped"

  env = [
    "POSTGRES_DB=${var.db_database}",
    "POSTGRES_USER=${var.db_username}",
    "POSTGRES_PASSWORD=${var.db_password}"
  ]

  ports {
    internal = 5432
    external = 5432
  }

  volumes {
    volume_name    = docker_volume.pgdata.name
    container_path = "/var/lib/postgresql/data"
  }

  networks_advanced {
    name = docker_network.sgee_network.name
  }
}

# ── Redis ─────────────────────────────────────────────────────────────
resource "docker_container" "redis" {
  name  = "laravel_redis"
  image = "redis:7-alpine"

  restart = "unless-stopped"

  ports {
    internal = 6379
    external = 6379
  }

  volumes {
    volume_name    = docker_volume.redisdata.name
    container_path = "/data"
  }

  networks_advanced {
    name = docker_network.sgee_network.name
  }
}

# ── Laravel App ───────────────────────────────────────────────────────
resource "docker_container" "app" {
  name  = "laravel_app"
  image = var.docker_image

  restart = "unless-stopped"

  env = [
    "APP_KEY=${var.app_key}",
    "APP_ENV=production",
    "DB_CONNECTION=pgsql",
    "DB_HOST=laravel_db",
    "DB_PORT=5432",
    "DB_DATABASE=${var.db_database}",
    "DB_USERNAME=${var.db_username}",
    "DB_PASSWORD=${var.db_password}",
    "REDIS_HOST=laravel_redis",
    "REDIS_PORT=6379"
  ]

  networks_advanced {
    name = docker_network.sgee_network.name
  }

  depends_on = [
    docker_container.postgres,
    docker_container.redis
  ]
}

# ── Nginx ─────────────────────────────────────────────────────────────
resource "docker_container" "nginx" {
  name  = "laravel_nginx"
  image = "nginx:alpine"

  restart = "unless-stopped"

  ports {
    internal = 80
    external = 8080
  }

  networks_advanced {
    name = docker_network.sgee_network.name
  }

  depends_on = [docker_container.app]
}

# ── Prometheus ────────────────────────────────────────────────────────
resource "docker_container" "prometheus" {
  name  = "prometheus"
  image = "prom/prometheus"

  restart = "unless-stopped"

  ports {
    internal = 9090
    external = 9090
  }

  volumes {
    host_path      = abspath("${path.module}/../docker/prometheus/prometheus.yml")
    container_path = "/etc/prometheus/prometheus.yml"
  }

  volumes {
    volume_name    = docker_volume.prometheusdata.name
    container_path = "/prometheus"
  }

  networks_advanced {
    name = docker_network.sgee_network.name
  }
}

# ── Grafana ───────────────────────────────────────────────────────────
resource "docker_container" "grafana" {
  name  = "grafana"
  image = "grafana/grafana"

  restart = "unless-stopped"

  ports {
    internal = 3000
    external = 3001
  }

  volumes {
    volume_name    = docker_volume.grafanadata.name
    container_path = "/var/lib/grafana"
  }

  networks_advanced {
    name = docker_network.sgee_network.name
  }
}
