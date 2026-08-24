#!/usr/bin/env bash

set -Eeuo pipefail

app_dir="${1:-}"

if [[ ! "${app_dir}" =~ ^/home/[^/]+/htdocs/staging\.aisyspro\.tn$ ]]; then
    echo "Usage: deploy-staging.sh /chemin/absolu/vers/staging.aisyspro.tn" >&2
    exit 2
fi

if [[ ! -d "${app_dir}/.git" || ! -f "${app_dir}/artisan" ]]; then
    echo "Le dossier cible n'est pas un déploiement Git Laravel AISYSPRO valide." >&2
    exit 3
fi

cd "${app_dir}"

git switch develop
git pull --ff-only origin develop

composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Un précédent déploiement optimisé peut encore contenir l'ancienne
# configuration du cookie. La vider après l'installation de Composer garantit
# que le `.env` courant est relu, y compris lors du premier déploiement.
php artisan config:clear

if command -v npm >/dev/null 2>&1; then
    npm ci --ignore-scripts
    npm run build
elif [[ ! -f public/build/manifest.json ]]; then
    echo "npm est indisponible et aucun bundle frontend versionné n'est présent." >&2
    exit 4
else
    echo "npm indisponible : utilisation du bundle frontend versionné et validé."
fi

php artisan down --retry=15
trap 'php artisan up' EXIT

php artisan migrate --force
php artisan db:seed --class='Database\Seeders\CmsFoundationSeeder' --force
[[ -L public/storage ]] || php artisan storage:link
php artisan optimize

session_cookie="$(php artisan tinker --execute='echo config("session.cookie");' 2>/dev/null)"
session_domain="$(php artisan tinker --execute='var_export(config("session.domain"));' 2>/dev/null)"

if [[ "${session_cookie}" != "aisyspro_staging_session_v2" || "${session_domain}" != "NULL" ]]; then
    echo "Configuration de session staging invalide (cookie=${session_cookie}, domain=${session_domain})." >&2
    exit 5
fi

php artisan up
trap - EXIT

php artisan about --only=environment
echo "Déploiement staging terminé."
