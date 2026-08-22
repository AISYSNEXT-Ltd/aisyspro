# Architecture cible AISYSPRO

## Principes

- Laravel 13 porte l'API, les règles métier, les autorisations, les traitements asynchrones et l'accès MariaDB.
- Vue.js 3 porte les pages publiques et le back-office sous forme de SPA.
- Laravel Sanctum fournit l'authentification SPA par cookie de session.
- Les échanges applicatifs sont versionnés sous `/api/v1`.
- Les environnements local, staging et production utilisent des bases, secrets, caches, sessions, queues et stockages distincts.

## Backend

```text
app/
├── Http/Controllers/Api/V1  Contrôleurs HTTP minces
├── Http/Requests             Validation et autorisation d'entrée
├── Http/Resources            Représentation des réponses API
├── Models                    Entités Eloquent
├── Policies                  Autorisations par ressource
├── Services                  Cas d'usage et logique métier
└── Jobs                      Traitements asynchrones
```

Les entités métier ne seront ajoutées qu'après validation de l'inventaire fonctionnel de l'application de référence.

## Frontend

```text
resources/js/
├── pages       Écrans routés
├── components  Composants réutilisables
├── layouts     Layouts public et back-office
├── router      Routes et gardes de navigation
├── services    Client HTTP et adaptateurs
└── stores      État Pinia
```

## Environnements

| Environnement | Branche | Domaine | Base |
| --- | --- | --- | --- |
| Local | `feature/*` ou `develop` | `localhost` | `aisyspro_local` |
| Staging | `develop` | `staging.aisyspro.tn` | Base staging dédiée |
| Production | `main` | `aisyspro.tn` | Base production dédiée |

Le document root Nginx doit pointer vers le dossier Laravel `public/`. Aucun déploiement ne doit copier `.env`, `node_modules` ou `vendor` depuis un poste de développement.

## Décisions différées

- Modèle métier définitif
- Rôles et permissions
- Structure CRM et pipeline
- Workflow de devis
- Architecture des contenus et des solutions
- Queues et notifications

Ces décisions nécessitent l'audit du Back Office de référence pour éviter d'inventer des règles métier.
