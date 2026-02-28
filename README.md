# Smart Campus

> Plateforme de supervision environnementale des salles du campus, basée sur des capteurs IoT (Systèmes d'Acquisition) pilotés via une interface web Symfony.

## Stack technique

- **Backend :** PHP 8 / Symfony 7 — Doctrine ORM — PHPUnit
- **Base de données :** MariaDB
- **Frontend :** Twig — Asset Mapper — Chart.js
- **Infrastructure :** Docker / Docker Compose — phpMyAdmin
- **Intégration capteurs :** API externe de capture de données (Systèmes d'Acquisition)
- **Outils :** Composer — Symfony CLI

## Fonctionnalités

### Espace Chargé de Mission
- Gestion des salles (ajout, modification, suppression, liste)
- Gestion des Systèmes d'Acquisition — SA (ajout, suppression)
- Création et consultation de demandes d'installation / désinstallation de SA
- Visualisation des données environnementales par salle (température, humidité, CO₂...)
- Gestion des alertes avec filtrage par criticité et type
- Tableau de bord global

### Espace Technicien
- Vue de l'ensemble des salles et SA
- Accès aux demandes d'installation
- Consultation de l'historique des données
- Gestion des seuils d'alerte

### Transverse
- Authentification avec deux rôles : Chargé de Mission et Technicien
- Réinitialisation de mot de passe par email
- Navigation responsive

## Installation & Lancement

**Prérequis :** Docker, Docker Compose

```bash
# Cloner le dépôt
git clone https://github.com/golubeduard2306-collab/smart-campus.git
cd smart-campus/stack-php-smart_campus

# Copier et remplir le fichier d'environnement
cp projet_symfony/.env.example projet_symfony/.env
# Éditer projet_symfony/.env avec vos valeurs locales

# Lancer les conteneurs
docker compose up -d

# Installer les dépendances PHP
docker exec smart_campus_php composer install -C projet_symfony

# Créer le schéma de base de données
docker exec smart_campus_php sh -c "cd projet_symfony && php bin/console doctrine:migrations:migrate --no-interaction"

# (Optionnel) Charger les données de test
docker exec smart_campus_php sh -c "cd projet_symfony && php bin/console doctrine:fixtures:load --no-interaction"
```

L'application est accessible sur `http://localhost:8888`
phpMyAdmin est accessible sur `http://localhost:8886`

> **Performance sous Windows :** Pour éviter les lenteurs Docker/Windows, placer le dépôt dans WSL2 (voir `stack-php-smart_campus/PERFS.md`).

## Variables d'environnement

Copier `projet_symfony/.env.example` vers `projet_symfony/.env` et renseigner :

| Variable | Description |
|---|---|
| `APP_ENV` | Environnement (`dev` ou `prod`) |
| `APP_SECRET` | Clé secrète Symfony (32 caractères hex) |
| `DATABASE_URL` | URL de connexion MariaDB |
| `MAILER_DSN` | Configuration SMTP pour les emails |
| `API_CAPTURE_USERNAME` | Identifiant de l'API de capture des données capteurs |
| `API_CAPTURE_PASSWORD` | Mot de passe de l'API de capture des données capteurs |

## Contexte

IUT La Rochelle — BUT Informatique — 2025-2026
SAÉ 3.4 — 2ème année

Projet réalisé en équipe de 5 avec méthode Scrum :
Eduard GOLUB *(Scrum Master)*, Kolan Allain, Noé Leteurtre, Simon Plault, Morgan Vanvelthem
