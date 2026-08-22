# AISYSPRO

Reconstruction contrôlée de la plateforme AISYSPRO avec Laravel 13, Vue.js 3 et MariaDB.

## État du projet

Le dépôt contient le socle technique initial. Il ne reproduit pas encore l'ensemble des fonctionnalités de l'application de référence. Les modules métier seront intégrés progressivement après inventaire et validation.

## Prérequis

- PHP 8.3 ou supérieur
- Composer 2
- MariaDB 10.6 ou supérieur
- Node.js 22 LTS ou supérieur
- npm 10 ou supérieur

Extensions PHP minimales : Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, pdo_mysql, Session, Tokenizer et XML.

## Installation locale

```bash
git clone https://github.com/AISYSNEXT-Ltd/aisyspro.git
cd aisyspro
git switch develop
composer install
cp .env.example .env
php artisan key:generate
```

Créer ensuite une base MariaDB et un utilisateur dédiés :

```sql
CREATE DATABASE aisyspro_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'aisyspro_local'@'localhost' IDENTIFIED BY 'CHANGE_ME';
GRANT ALL PRIVILEGES ON aisyspro_local.* TO 'aisyspro_local'@'localhost';
FLUSH PRIVILEGES;
```

Renseigner le mot de passe dans le fichier `.env` local, puis exécuter :

```bash
php artisan migrate
npm install
npm run build
composer run dev
```

Application : `http://localhost:8000`

Vérification API : `http://localhost:8000/api/v1/health`

## Tests

```bash
php artisan test
npm run build
```

Les tests utilisent SQLite en mémoire afin de rester rapides. L'environnement applicatif utilise MariaDB.

## Stratégie Git

- `main` : production validée
- `develop` : intégration et staging
- `feature/*` : développement isolé
- `hotfix/*` : correction urgente basée sur `main`

Les changements passent par une pull request vers `develop`. La fusion vers `main` intervient uniquement après validation du staging.

## Sécurité

Ne jamais committer les fichiers `.env`, mots de passe, tokens, certificats, clés privées ou accès CloudPanel. En production, `APP_ENV=production` et `APP_DEBUG=false` sont obligatoires.

Voir [docs/architecture.md](docs/architecture.md) pour les décisions d'architecture.
