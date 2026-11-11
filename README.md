# CNC-Connect API

API REST de la plateforme **CNC-Connect**, une plateforme d'enrôlement aux concours académiques.
Elle gère les concours, les inscriptions des candidats, la vérification des paiements, le suivi des candidatures et l'administration de la plateforme.

Le frontend est disponible ici : [CNC-Connect](https://github.com/SteveTetchoup26/CNC-Connect).

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-4169E1?logo=postgresql)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker)
![Kubernetes](https://img.shields.io/badge/Kubernetes-HPA-326CE5?logo=kubernetes)

---

## Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Technologies](#technologies)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Démarrage](#démarrage)
- [Documentation de l'API](#documentation-de-lapi)
- [Docker](#docker)
- [Monitoring et logs](#monitoring-et-logs)
- [Kubernetes et tests de charge](#kubernetes-et-tests-de-charge)
- [CI/CD](#cicd)
- [Tests](#tests)
- [Structure du projet](#structure-du-projet)

---

## Fonctionnalités

### Candidats

- **Authentification** : inscription, connexion, déconnexion et rafraîchissement du token (Laravel Sanctum).
- **Consultation des concours** : liste publique et détails (filières, niveaux, dates, frais, documents requis).
- **Inscription à un concours** : dossier complet (informations personnelles, pièce d'identité, famille, parcours scolaire, paiement, centres de dépôt et d'examen).
- **Vérification automatique du paiement par OCR** : le reçu bancaire déposé (image ou PDF) est lu avec Tesseract. Le montant et le compte sont comparés aux frais du concours, avec une tolérance configurable.
- **Suivi des candidatures** : statut et historique de chaque candidature.
- **Fiche de candidature PDF** : générée avec un QR code menant à son téléchargement.
- **Rappels** : le candidat programme un rappel par email pour une candidature. Une tâche planifiée l'envoie à l'heure choisie.
- **Notifications par email** : confirmation d'inscription, candidature validée, candidature rejetée.

### Administrateurs

Toutes les routes `/api/v1/admin/*` sont réservées au rôle `ADMINISTRATEUR`.

- **Tableau de bord** : candidatures (total, en attente, approuvées), utilisateurs, concours, écoles, centres, évolution sur 12 mois et répartition par statut.
- **Gestion des concours** : création, modification, suppression, spécifications (documents requis, frais), sessions et changement d'état.
- **Gestion des écoles, épreuves, centres et utilisateurs** (avec leurs rôles).
- **Traitement des candidatures** : consultation, modification, validation, rejet et affectation des centres de dépôt et d'examen.
- **Exports** : liste des concours et liste des candidats d'un concours, en PDF et en Excel.

---

## Technologies

| Domaine | Outils |
|---|---|
| Langage et framework | PHP 8.4, Laravel 12 |
| Base de données et cache | PostgreSQL 15, Redis 7 |
| Authentification | Laravel Sanctum (tokens) avec rôles et permissions |
| Documents | DomPDF (PDF), Laravel Excel (exports), Simple QrCode |
| OCR | Tesseract via `thiagoalessio/tesseract_ocr` |
| Documentation | Swagger / OpenAPI (L5-Swagger) |
| Conteneurs | Docker, Docker Compose, Nginx |
| Orchestration | Kubernetes (Deployment, Service, HPA), Terraform |
| Monitoring | Prometheus, Grafana, Node Exporter |
| Logs | Elasticsearch, Logstash, Kibana, Filebeat |
| Qualité | PHPUnit, PHPStan, PHP CS Fixer, SonarQube |
| Tests de charge | k6 |
| CI/CD | GitHub Actions, Docker Hub |

---

## Prérequis

- PHP 8.2 ou plus (8.4 recommandé) et Composer
- PostgreSQL
- Node.js et npm (pour la commande `composer dev`)
- [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) avec les langues `fra` et `eng`, pour la vérification des paiements
- Docker et Docker Compose, pour l'environnement conteneurisé (facultatif)

---

## Installation

```bash
git clone https://github.com/SteveTetchoup26/CNC-Connect-API.git
cd CNC-Connect-API

composer install
cp .env.example .env
php artisan key:generate
```

Renseignez ensuite dans `.env` la connexion à la base de données (`DB_*`), l'envoi des emails (`MAIL_*`) et les options OCR :

| Variable | Rôle | Défaut |
|---|---|---|
| `OCR_ACTIF` | Active la vérification des reçus de paiement | `true` |
| `OCR_TOLERANCE_MONTANT` | Écart toléré entre le montant lu et les frais du concours | `5` |
| `OCR_REJETER_SI_ECHEC` | Refuse l'inscription si la vérification échoue | `true` |

Sous Windows, si Tesseract n'est pas dans `C:/Program Files/Tesseract-OCR/`, indiquez son chemin dans la configuration OCR (`config/ocr.php`).

Créez les tables puis chargez les données de base (rôles, permissions, régions, centres, écoles, niveaux, concours…) :

```bash
php artisan migrate --seed
```

Le seeder crée un compte administrateur de démonstration : `admin@test.com` / `123456`. Changez ce mot de passe en dehors d'un environnement local.

---

## Démarrage

```bash
composer dev
```

Cette commande lance en parallèle le serveur (`php artisan serve`), la file d'attente des jobs et Vite.
L'API répond sur `http://localhost:8000/api/v1`.

Pour l'envoi des rappels, le planificateur doit tourner (la commande `rappels:envoyer` est exécutée chaque minute) :

```bash
php artisan schedule:work
```

---

## Documentation de l'API

La documentation Swagger est servie sur `/api/v1/documentation`.

Aperçu des principales routes (préfixe `/api/v1`) :

| Méthode | Route | Accès |
|---|---|---|
| `POST` | `/register`, `/login` | Public |
| `GET` | `/concours`, `/concours/{id}` | Public |
| `GET` | `/me` · `POST` `/logout`, `/refresh-token` | Connecté |
| `POST` | `/inscription-concours` | Connecté |
| `GET` | `/mes-candidatures`, `/mon-profil-complet`, `/ma-fiche-paiement` | Connecté |
| `POST` | `/candidatures/{id}/rappel` | Connecté |
| `GET` | `/centres`, `/centres/type/{type}/region/{regionId}` | Connecté |
| `GET` | `/admin/dashboard`, `/admin/dashboard/stats-by-period` | Admin |
| `CRUD` | `/admin/concours`, `/admin/sessions`, `/admin/ecoles`, `/admin/epreuves`, `/admin/centres`, `/admin/utilisateurs`, `/admin/spec-concours` | Admin |
| `POST` | `/admin/candidatures/{id}/valider`, `/rejeter`, `/affecter-centres` | Admin |
| `GET` | `/admin/concours/export/{pdf,excel}`, `/admin/concours/{id}/export-candidats-{pdf,excel}` | Admin |

La fiche de candidature se télécharge sur `/api/candidature/{id}/fiche/download`, le lien contenu dans son QR code.

---

## Docker

`docker-compose.yml` lance l'application avec toute son infrastructure :

| Service | Rôle | Port |
|---|---|---|
| `app` | PHP-FPM 8.4 (Laravel) | — |
| `nginx` | Serveur web | `8080` |
| `db` | PostgreSQL 15 | `5432` |
| `redis` | Cache | `6379` |
| `prometheus` | Collecte des métriques | `9090` |
| `grafana` | Tableaux de bord | `3001` |
| `node-exporter` | Métriques de la machine | `9100` |
| `elasticsearch`, `logstash`, `kibana`, `filebeat` | Centralisation des logs | `9200`, `5044`, `5601` |
| `sonarqube` | Analyse de la qualité du code | `9001` |

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

L'API est alors accessible sur `http://localhost:8080/api/v1`.

---

## Monitoring et logs

- **Métriques** : un middleware enregistre les métriques des requêtes HTTP, exposées au format Prometheus sur `/metrics`. Prometheus les collecte et Grafana les affiche.
- **Logs** : Filebeat lit les logs Laravel (`storage/logs`), Logstash les traite et les envoie à Elasticsearch, consultables dans Kibana.

---

## Kubernetes et tests de charge

Le dossier `k8s/` contient les manifestes du namespace `cnc-connect` : application, Nginx, Redis, ConfigMap et Secret.

- L'application démarre avec **2 réplicas**.
- Un **HorizontalPodAutoscaler** ajuste le nombre de pods entre **2 et 10**, à partir de 70 % de CPU ou 80 % de mémoire.

Trois scénarios [k6](https://k6.io/) sont fournis dans `k8s/tests/` :

| Scénario | Charge |
|---|---|
| `test-charge-normale.js` | 50 utilisateurs simultanés |
| `test-pic-inscription.js` | Montée jusqu'à 200 utilisateurs, simulant un pic d'inscriptions |
| `test-resistance.js` | 100 utilisateurs pendant 5 minutes |

Objectif commun : 95 % des requêtes sous 3 secondes.

```bash
k6 run k8s/tests/test-pic-inscription.js
```

Le dossier `terraform/` décrit l'infrastructure Docker (réseau, volumes, conteneurs) avec le provider `kreuzwerker/docker`.

---

## CI/CD

Le workflow GitHub Actions (`.github/workflows/ci.yml`) s'exécute à chaque push et pull request sur `main` et `develop` :

1. **Lint** : PHP CS Fixer et PHPStan (niveau 3).
2. **Tests** : migrations et suite PHPUnit sur SQLite en mémoire.
3. **SonarQube** : analyse de la qualité du code.
4. **Déploiement** (branche `main`) : construction de l'image Docker et publication sur Docker Hub.

---

## Tests

```bash
php artisan test
```

Les tests fonctionnels couvrent l'authentification, les concours, les sessions, les écoles et les centres (`tests/Feature`).

---

## Structure du projet

```
app/
├── Console/Commands/   commande d'envoi des rappels
├── Exports/            exports Excel
├── Http/
│   ├── Controllers/
│   │   ├── Api/        authentification, concours, candidats, écoles, sessions
│   │   └── Admin/      tableau de bord, candidatures, centres, épreuves, utilisateurs
│   ├── Middleware/     rôles, Sanctum optionnel, métriques Prometheus
│   └── Requests/       validation des formulaires
├── Jobs/               envoi des rappels
├── Mail/               emails aux candidats
├── Models/             modèles Eloquent
└── Services/           service OCR
database/
├── migrations/
├── seeders/
└── factories/
resources/views/        fiche de candidature, emails et exports PDF
routes/api.php          routes de l'API
docker/                 Dockerfile, Nginx, Prometheus, Logstash, Filebeat
k8s/                    manifestes Kubernetes et tests de charge k6
terraform/              infrastructure en tant que code
```
