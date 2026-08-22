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
npm ci --ignore-scripts
npm run build

php artisan down --retry=15
trap 'php artisan up' EXIT

php artisan migrate --force
[[ -L public/storage ]] || php artisan storage:link
php artisan optimize

php artisan up
trap - EXIT

php artisan about --only=environment
echo "Déploiement staging terminé."
