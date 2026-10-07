## Prérequis

Avant de commencer, assurez-vous que vous avez installé les éléments suivants sur votre machine :

PHP >= 8.0
Composer (gestionnaire de dépendances pour PHP)
Base de données (MySQL, PostgreSQL, SQLite, etc.) selon la configuration du projet

## Installation des dépendances

--> sudo apt install ffmpeg

Après avoir cloné le projet, exécutez la commande suivante pour installer les dépendances :

--> composer install --prefer-dist

## Creation d'un utilisateur administrateur

Pour creer un utilisateur administrateur, il faut entrer la commande suivante dans le terminal :

--> php bin/console app:create-administrator

## Pour éviter de purger la base de données

Lors du chargement des fixtures avec la commande :

```bash
php bin/console doctrine:fixtures:load
```

Vous pouvez utiliser l'option --append. Cette option permet d'ajouter les données sans supprimer les données existantes.

```bash
php bin/console doctrine:fixtures:load --append
```

## pour le test unitaire

```bash
php bin/console doctrine:migrations:migrate --env=test
```

```bash
php bin/console doctrine:fixtures:load --env=test --append
```

## Pour le chargement des fixtures localisation et project-parent seulement

```bash
php bin/console doctrine:fixtures:load --env=test --append --group=localisation
```

```bash
php bin/console doctrine:fixtures:load --env=test --append --group=project-parent
```

## Pour l'export en excel

-> composer require phpoffice/phpspreadsheet

## Symfony Messenger

execute : composer require symfony/messenger

```bash
run : php bin/console messenger:consume async -vv
```

## Modif Parametres de PHP

    max_execution_time = 0 : Temps d'exécution illimité pour les scripts PHP.
    max_input_time = -1 : Temps illimité pour l'analyse des entrées PHP.
    memory_limit = 1024M : 1 Go de mémoire allouée aux scripts PHP.
    post_max_size = 512M : Taille maximale des requêtes POST (affecte l'upload de fichiers).
    upload_max_filesize = 512M : Taille maximale d'un fichier uploadé.

# 🔧 Symfony Console Commands – Clear Tables & Clear Uploads

Ce projet comprend deux commandes personnalisées pour Symfony afin de faciliter le nettoyage d'une base de données
partiellement remplie ainsi que la suppression des fichiers uploadés par l'utilisateur.

---

## 📦 Commandes disponibles

### 1. `app:clear-tables`

### 2. `app:clear-uploads`

> Vide certaines tables de la base de données tout en respectant les contraintes d'intégrité référentielle.

#### ✅ Utilisation

🧪 Simuler la suppression

```bash Mode simulation  (dry-run)
php bin/console app:clear-tables  --dry-run
```

```bash Mode simulation (dry-run)
php bin/console app:clear-uploads --dry-run
```

🔥 Supprimer réellement

```bash
php bin/console app:clear-tables
```

```bash
php bin/console app:clear-uploads
```

# 🛡️ SIEM Fixtures – Initialisation des données de sécurité

Ce projet utilise un système de **log de sécurité (SIEM)** pour tracer automatiquement les actions critiques : création, modification, suppression d'entités, envoi d'emails, etc.

Pour que ce système fonctionne correctement, des données de référence doivent être injectées en base (types d'événements, d'erreurs, etc.).

---

## 🚀 Commande d'installation des données SIEM

Pour charger uniquement les données liées au SIEM, exécute la commande suivante :

```bash
php bin/console doctrine:fixtures:load --append --group=siem --purge-with-truncate
```

## Dépendance système

    Pour la création d’images à partir d’un fichier HTML, vous devez installer rsvg-convert

```bash
sudo apt install librsvg2-bin
```
