# Lot 1 — sécurité des sessions et du portail

État : validation locale réussie le 6 septembre 2026 ; recette navigateur et MariaDB staging en attente.

## Modifications

- Jeton de connexion lié à l'identifiant de session initial (empreinte SHA-256), toujours expirant à 60 secondes.
- Lecture et suppression du jeton sous verrou du cache ; refus immédiat si verrou occupé. Vérifier que le cache de staging est partagé et supporte les verrous (database/Redis).
- Middleware active après authentification sur toutes les API privées : compte supprimé ou désactivé refusé, session web invalidée.
- Suppression des diagnostics /controle-staging et /api/v1/auth/session-status.
- Liste blanche des paramètres publics : brand_name, email, phone, locale, default_robots.
- Réponses auth/admin privées non stockables, Referrer-Policy no-referrer sur ces parcours.
- X-Robots-Tag noindex,nofollow et robots.txt restrictif quand APP_ENV=staging.

## Validation effectuée

- vue-tsc --noEmit : réussi.
- vite build : réussi ; avertissement optionnel fontaine préexistant.
- git diff --check : réussi.
- PHP 8.3.6 portable installé dans le workspace après échec de apt. Suite PHPUnit : 34 tests réussis, 158 assertions, dont huit nouveaux tests de sécurité. Tests corrigés pour transmettre explicitement le cookie de session entre requêtes et utiliser le pilote database pour la vérification de persistance SQL.
- Pint : réussi sur les fichiers du lot après rangement des imports.

Le test de verrou est un test de contention contrôlée, pas un test multiprocessus MariaDB. Les tests HTTP Laravel ne remplacent pas une recette réelle des cookies dans Chromium.

## Avant publication et déploiement

1. Exécuter PHPUnit et Pint avec PHP compatible et les dépendances verrouillées. Corriger les éventuels échecs avant publication.
2. Tester avec MariaDB les sessions et la contention sur le cache utilisé en staging.
3. Vérifier l'absence de cache proxy sur auth/admin, y compris anciennes réponses publiques des paramètres : purger leur cache au déploiement.
4. Vérifier login avec/sans Rester connecté, rechargement, nouvel onglet, déconnexion et refus après désactivation. Le navigateur doit conserver sa session invitée entre POST et GET ; le nouveau contrôle refuse le flux si le proxy perd ce cookie.
5. Vérifier APP_ENV=staging, 404 des diagnostics et en-têtes noindex/no-store servis réellement.
6. Les jetons de connexion restent temporairement dans l'URL : masquer le paramètre token dans les journaux proxy/serveur. no-referrer évite le transfert par référent mais ne supprime pas les journaux ni l'historique navigateur.

Aucune migration de données requise. Aucun déploiement, modification CloudPanel ou suppression de cron effectué dans ce lot. Production inchangée. Ne pas publier ce lot comme validé avant exécution des contrôles manquants.
