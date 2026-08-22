# Déploiement staging CloudPanel

Le site `staging.aisyspro.tn` existe déjà dans CloudPanel. Cette procédure ne recrée ni le site, ni le domaine, ni le certificat.

## Configuration CloudPanel à contrôler une fois

- Type de site : PHP.
- PHP : 8.3 ou supérieur avec `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `intl` et `bcmath`.
- Document root : dossier `public/` du dépôt, jamais la racine Laravel.
- HTTPS actif.
- MariaDB : base et utilisateur dédiés au staging.
- Le site Linux CloudPanel doit posséder le dépôt, `storage/` et `bootstrap/cache/`.

Exemple de chemin, à adapter au véritable utilisateur CloudPanel :

```text
/home/<site-user>/htdocs/staging.aisyspro.tn
```

## Variables `.env` minimales

Le fichier `.env` est créé directement sur le serveur et n'est jamais versionné :

```dotenv
APP_NAME=AISYSPRO
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.aisyspro.tn

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aisyspro_staging
DB_USERNAME=aisyspro_staging
DB_PASSWORD=<secret>

SESSION_DRIVER=database
SESSION_DOMAIN=staging.aisyspro.tn
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=staging.aisyspro.tn

ADMIN_NAME="Administrateur AISYSPRO"
ADMIN_LOGIN=adminx
ADMIN_EMAIL=<adresse-administrateur>
ADMIN_PASSWORD=<secret-initial>
```

Après le premier `php artisan db:seed --force`, retirer `ADMIN_PASSWORD` du `.env` ou le remplacer par une valeur gérée dans le coffre de secrets. Le mot de passe applicatif reste stocké haché en base.

## Premier raccordement Git

Dans le terminal CloudPanel, sous l'utilisateur Linux du site :

```bash
cd /home/<site-user>/htdocs/staging.aisyspro.tn
git init
git remote add origin https://github.com/AISYSNEXT-Ltd/aisyspro.git
git fetch origin develop
git checkout -B develop origin/develop
cp .env.example .env
php artisan key:generate
```

Avant cette initialisation, conserver une copie du contenu placeholder actuellement présent dans le dossier du site. Git laissera en place les fichiers non suivis qui ne sont pas en conflit avec le dépôt.

Renseigner ensuite le `.env` côté serveur, puis lancer le déploiement initial :

```bash
bash scripts/deploy-staging.sh /home/<site-user>/htdocs/staging.aisyspro.tn
php artisan db:seed --force
```

Le seeder importe le snapshot validé de la plateforme de référence : 52 solutions métier, 57 articles, trois packs et les FAQ publiques. Cette commande est exécutée lors de l'initialisation ou d'une reprise explicitement décidée ; elle n'est pas intégrée à chaque déploiement afin de préserver les modifications éditoriales réalisées ensuite dans le Back Office.

## Déploiement GitHub Actions

Créer l'environnement GitHub `staging`, puis ces secrets :

| Secret | Valeur attendue |
| --- | --- |
| `STAGING_HOST` | IP ou nom SSH du VPS |
| `STAGING_PORT` | Port SSH, généralement `22` |
| `STAGING_USER` | Utilisateur Linux du site CloudPanel |
| `STAGING_PATH` | Chemin absolu exact du dépôt |
| `STAGING_SSH_PRIVATE_KEY` | Clé privée dédiée au déploiement |
| `STAGING_SSH_HOST_KEY` | Ligne `known_hosts` vérifiée du VPS |

Le workflow **Deploy staging** est volontairement manuel. Il ne peut s'exécuter que depuis `develop` et évite qu'un push incomplet déclenche une mise en ligne automatique.

## Vérifications après déploiement

```text
https://staging.aisyspro.tn/up
https://staging.aisyspro.tn/api/v1/health
https://staging.aisyspro.tn/connexion-admin
https://staging.aisyspro.tn/solutions
https://staging.aisyspro.tn/blog
```

Le endpoint métier `/api/v1/health` doit répondre avec un statut HTTP 200 et confirmer l'accès à MariaDB.
