# Audit comparatif AISYSPRO — 23 août 2026

## Périmètre audité

- Référence ChatGPT Pro : Front Office, connexion, tableau de bord et modules CMS/système.
- Staging Laravel 13 / Vue 3 : routes, API, modèle de données, sécurité, SEO, responsive et Back Office.
- Dépôt `develop` : migrations, modèles, contrôleurs, composants, tests et scripts de déploiement.

## Synthèse

Le Front Office Laravel reprend correctement l'identité, la structure multipage, les 55 solutions publiées, les 57 articles et les trois packs. Le socle CRM est fonctionnel. L'écart principal se situe dans la profondeur du CMS et du Back Office : la référence expose un Page Builder, des menus, une médiathèque et des paramètres, alors que le staging utilisait principalement un CRUD générique.

## Matrice d'écarts

| Priorité | Domaine | Écart observé | Décision |
|---|---|---|---|
| Critique | CMS | Les pages publiques ne consommaient pas les pages administrées | Ajouter des sections persistantes, ordonnables, visibles/masquées et injectées sans perte du contenu système |
| Critique | SEO | Seulement `title` et description étaient pilotés côté client | Ajouter canonical, robots, Open Graph, Twitter Card, données structurées et sitemap dynamique |
| Critique | SEO | Une URL inconnue renvoyait la SPA sans page 404 dédiée | Ajouter une route HTTP 404 et une page Vue 404 avec `noindex` |
| Critique | Navigation | Menu codé en dur | Ajouter menus, sous-menus, ordre, visibilité, liens internes/externes et cibles |
| Critique | Sécurité | En-têtes de défense absents | Ajouter CSP, anti-sniffing, framing, referrer policy, permissions policy et HSTS en production HTTPS |
| Important | Médias | Aucun stockage administrable ni ALT centralisé | Ajouter médiathèque avec validation MIME/taille, stockage public, ALT et suppression du fichier physique |
| Important | CMS | FAQ et témoignages étaient confondus dans la référence et absents du staging | Conserver la FAQ et ajouter un module témoignages distinct et réutilisable |
| Important | Configuration | Coordonnées et règles SEO dispersées dans le code | Ajouter des paramètres globaux persistants et une interface dédiée |
| Important | Back Office | Navigation plate, sans groupes métier | Regrouper Pilotage, CRM, Contenu, Offre et Système avec filtrage par rôle |
| Important | UX | CRUD générique peu adapté aux contenus complexes | Garder le CRUD pour les entités simples et utiliser des écrans spécialisés pour pages, menus et médias |
| Amélioration | Dashboard | Staging plus simple que la référence | Ajouter pipeline, activité récente et raccourcis après alimentation réelle du CRM |
| Amélioration | CRM | Pas de vue Kanban ni fiche détaillée riche | Ajouter après stabilisation du CMS, sans casser les listes et API existantes |
| Amélioration | Offre | Modules partagés entre solutions et packs non normalisés | Normaliser progressivement le référentiel de modules tout en conservant les tableaux JSON existants pendant la migration |
| Finition | UI | Icônes, densité, états et micro-interactions hétérogènes | Finaliser après validation fonctionnelle du lot CMS/SEO |

## Principes d'implémentation

1. Aucune suppression des contenus système : un bloc choisit `append` ou `replace` explicitement.
2. Les routes et API existantes sont conservées.
3. Les nouvelles fonctions sont protégées par les rôles Administrateur/Éditeur.
4. Les fichiers téléversés excluent SVG et exécutables ; taille maximale 5 Mo.
5. Les éléments SEO ont un repli automatique lorsque le CMS ne les renseigne pas.
6. Le menu codé en dur reste un fallback tant que le menu CMS n'est pas initialisé.
7. Le déploiement reste limité à `develop` et au staging jusqu'à recette finale.

## Lots suivants

- Lot 2 : dashboard enrichi, fiches détaillées et pipeline Kanban.
- Lot 3 : référentiel de modules, associations packs/solutions, workflows et intégrations.
- Lot 4 : audit Lighthouse/Core Web Vitals, optimisation images et recette responsive multi-écrans.
- Lot 5 : recette fonctionnelle complète, documentation d'exploitation et préparation production.
