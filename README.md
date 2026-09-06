# AISYSPRO

Reconstruction contrôlée de la plateforme AISYSPRO avec Laravel 13, Vue.js 3 et MariaDB.

## État du projet

Le dépôt contient une première version fonctionnelle du Front Office multipage et du Back Office AISYSPRO. La charte, les parcours publics et les contenus sont reconstruits à partir de l'application de référence validée.

Le Front Office inclut l'accueil, le catalogue de 52 solutions, les fiches métier, les packs, le configurateur de devis, le blog avec 57 articles, les pages À propos, FAQ, Contact et les pages légales. Les solutions, articles, packs et FAQ sont administrables depuis le Back Office.

Le Back Office inclut l'authentification applicative, les rôles, le tableau de bord et la gestion paginée des prospects, clients, devis, tâches, demandes, articles, solutions, packs, FAQ, pages et utilisateurs.

Le premier lot Back Office inclut désormais l'authentification applicative, les rôles, le tableau de bord et la gestion paginée des prospects, clients, devis, tâches, articles et solutions.

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
php artisan db:seed
npm install
npm run build
composer run dev
```

Application : `http://localhost:8000`

Vérification API : `http://localhost:8000/api/v1/health`

## Tests

```bash
npm run typecheck
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

La procédure de mise en ligne CloudPanel est documentée dans [docs/staging-deployment.md](docs/staging-deployment.md).

Les évolutions fonctionnelles sont détaillées dans [docs/lot-1-securite.md](docs/lot-1-securite.md) et [docs/lot-2-conversion.md](docs/lot-2-conversion.md).
