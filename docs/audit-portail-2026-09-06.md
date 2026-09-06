# AISYSPRO — audit de reprise du portail, 6 septembre 2026

## 1. Résumé exécutif

Le dépôt contient un portail Laravel/Vue avancé avec CRM interne, CMS, menus, médias, catalogue et blog. Il doit être conservé. L'ERP livré aux clients reste un produit séparé. Le parcours commercial central n'est toutefois pas complet : une soumission crée un Inquiry, sans prospect lié ni notification. La qualification du besoin reste du texte libre et les options sont codées en dur.

Ce rapport est un audit statique, pas une attestation de bon fonctionnement du staging ou de sécurité globale. Aucun code applicatif, contenu serveur ou compte n'a été modifié pendant cette intervention.

## 2. Périmètre et limites

- Dépôt local inspecté : branche develop, arbre propre au début, commit 16db752.
- GitHub interrogé : AISYSNEXT-Ltd/aisyspro, develop pointe sur 79a2005f0edd90eda4dfe9cfc99e1d3a44fc2e60. Les SHA local et distant diffèrent ; aucune égalité complète des arbres n'est affirmée.
- Référence et staging : les tentatives d'ouverture web ont échoué. Disponibilité, parité visuelle, sessions Admin, base réelle et responsive non vérifiés aujourd'hui.
- PHP et Composer non trouvés dans PATH. Aucun résultat PHPUnit/Pint actuel.
- Commande npm typecheck/build interrompue : approbation réseau annulée avant décision. Aucun résultat frontend actuel.
- Les validations historiques ne remplacent pas une recette actuelle.

## 3. Référence et contenus à préserver

La référence fonctionnelle demeure https://aisyspro-platform.aisysnext.chatgpt.site/. Son interface actuelle n'a pas été observée pendant cet audit.

Le dépôt conserve deux snapshots dans database/seeders/data : reference-solutions.json.gz.b64 et reference-articles.json.gz.b64. Le rapport historique du 23 août annonce 55 solutions et 57 articles ; les effectifs actuels du serveur ne sont pas vérifiés.

Préserver avant déploiement : articles et leurs slugs, catégories, tags, textes, dates, SEO et images ; solutions et modules associés ; packs ; pages et sections ordonnées ; menus ; médias et ALT ; paramètres ; prospects, demandes, devis, utilisateurs et tâches.

ReferenceContentSeeder utilise updateOrCreate avec le contenu historique et force le statut published : le réexécuter peut écraser des modifications éditoriales récentes. Prévoir un inventaire et une sauvegarde restaurable avant toute réimportation ; importer seulement les manquants ou traiter explicitement les conflits.

## 4. Projet local

Laravel déclaré ^13.17, PHP ^8.3, Vue 3, Pinia, Vue Router, Sanctum et MariaDB prévu. API v1 ; contrôles de rôles administrateur/commercial/éditeur. Pages publiques à URL distincte, rendues par SPA. Page Builder, Menu Builder, médiathèque et paramètres spécialisés sont présents dans le code.

La suite contient des tests fonctionnels pour auth, contenus, CMS, utilisateurs et pagination. Le seul test sous tests/Unit est assertTrue(true). package.json ne définit aucun test Vue/E2E. tsconfig.json désactive checkJs : un typecheck réussi ne prouve pas la correction de toute la logique JavaScript.

## 5. Infrastructure

Cible : staging.aisyspro.tn ; production : aisyspro.tn. Aucun accès CloudPanel vérifié aujourd'hui, aucun redémarrage exécuté.

Contrôles restants : version réellement déployée, santé Laravel et MariaDB, PHP/extensions, caches, exclusion du cache proxy sur auth/admin, cookies après rechargement, workers, scheduler, sauvegarde/restauration, stockage médias, journaux et inventaire exact des crons et clés temporaires. Ne pas supposer que les sessions navigateur des échanges précédents persistent.

## 6. Matrice des écarts constatés dans le code

| Priorité | Domaine | Preuve | Écart et action |
|---|---|---|---|
| Critique | Conversion | PublicInquiryController::store | Crée seulement Inquiry. Créer Inquiry + Lead liés dans une transaction ; rendre les reprises idempotentes et prévoir notification après commit. |
| Important | Qualification | QuotePage.vue | Trois étapes mais navigation libre, champs de l'étape entreprise démontés avant envoi. Valider chaque étape et tout le payload serveur. Ajouter utilisateurs, outils, contraintes, hébergement, délai et récapitulatif. |
| Important | Consentement | InquiryForm.vue et PublicInquiryController | Case contact uniquement côté navigateur, aucun champ serveur ; devis sans consentement. Valider et conserver preuve/version du texte présenté. |
| Important | Catalogue | QuotePage.vue | Options et prix codés en dur ; pack transmis comme nom et estimation dans message. Références stables, options administrables et estimation recalculée serveur. |
| Critique | Sessions | AuthController, routes/web.php | Jeton de connexion en URL, consommé par Cache::pull sans liaison au navigateur d'origine ni garantie atomique visible. Analyser le contournement proxy et tester expiration, rejeu, concurrence et transfert entre sessions avant maintien ou remplacement. |
| Critique | Diagnostics | routes/web.php | Diagnostics staging accessibles sans auth : état sessions et compteur de sessions authentifiées. Retirer les routes temporaires dans le lot sécurité. |
| Important | Comptes désactivés | EnsureRole et routes API | is_active contrôlé au login, pas dans EnsureRole. Tester puis imposer le refus d'une session existante après désactivation. |
| Important | Paramètres | PublicContentController::settings | Toutes les valeurs SiteSetting sont publiques ; aucune liste blanche. Limiter explicitement les paramètres publics avant ajout d'intégrations sensibles. Aucun secret réel exposé n'est affirmé. |
| Important | Pages CMS | router/index.js, routes/web.php | Liste de routes publiques fixe ; une nouvelle page CMS arbitraire aboutit au catch-all. Ajouter résolution publique par slug, respect brouillon et vrai statut HTTP. Page Services dédiée absente de la liste. |
| Important | Page Builder | PageBuilderPage.vue | Éditeur JSON, aperçu du publié, pas de workflow visible de duplication/révision. Concevoir champs par bloc, aperçu brouillon protégé et publication contrôlée. |
| Important | SEO staging | routes/web.php | robots autorise l'indexation sans distinction staging. Définir noindex sur préproduction ; vérifier également les en-têtes réellement servis. |
| Amélioration | Performance | PublicContentController | Catalogues complets via get(), imports router synchrones. Mesurer volumes/temps puis paginer et découper les bundles. |
| Important | QA | tests et package.json | Pas de tests unitaires métier significatifs ni suite navigateur configurée. Ajouter les scénarios de conversion/auth prioritaires. |

## 7. Risques et décisions produit

Préserver le CRM interne pour gérer l'acquisition ; exclure les modules opérationnels propres aux ERP clients. Remplacer les promesses de couverture absolue par un périmètre validé avec chaque prospect. Les exemples marketing et tarifs nécessitent cohérence avec les offres validées, sans inventer de bénéfices chiffrés.

Ne pas annoncer la parité avec la référence, une absence de faille ou des performances garanties tant que les contrôles correspondants ne sont pas exécutés.

## 8. Plan priorisé

1. Sécurité et session : reproduire le parcours réel, corriger désactivation/paramètres publics, supprimer diagnostics, sécuriser le mécanisme de connexion et isoler l'indexation staging.
2. Conversion : formulaire structuré, packs/options administrables, validation par étape, consentement, transaction demande/prospect, notifications, anti-doublons et gestion d'erreurs.
3. CMS et contenus : pages dynamiques, Page Builder accessible aux éditeurs, brouillons/aperçu, menus, import sans écrasement, métiers et articles associés.
4. Back Office commercial : pipeline, fiches, affectation, relances ; homogénéiser recherche/tri/pagination 5/10/25/50/100.
5. Recette : comparaison visuelle réelle desktop/tablette/mobile, clavier/accessibilité, SEO, mesures de performance, tests MariaDB, sécurité ciblée, sauvegarde/rollback et déploiement vérifié.

Chaque lot dépend d'une baseline reproductible, de tests ciblés et d'une recette staging. Aucune migration destructive ou réinitialisation de base.

## 9. Critères d'acceptation du premier lot

- Connexion native valide puis navigation/rechargement/nouvel onglet authentifiés ; comportement avec/sans Rester connecté testé.
- Mauvais identifiants, utilisateur désactivé, jeton expiré/rejoué ou provenant d'un autre navigateur refusés ; consommation concurrente testée si le mécanisme est conservé.
- Après désactivation, une session déjà ouverte ne peut plus appeler les API privées ; déconnexion invalide la session.
- Invité : 401 sur API privées ; utilisateur sans rôle requis : 403.
- Routes de diagnostic : 404 ; paramètres internes absents de l'API publique.
- Préproduction non indexable ; pas de cache partagé des réponses authentifiées.
- Tests Laravel pertinents, Pint, typecheck/build et recette navigateur exécutés avec résultats documentés ; aucun secret dans logs/rapport.

## 10. Prochaine action

Valider le lot 1 décrit ci-dessus conformément à l'instruction de démarrage du prompt maître, puis rétablir une exécution de tests reproductible et vérifier le parcours de session sur le staging. Les accès nécessaires sont ceux de la session Admin de test et des contrôles d'exploitation ; ne transmettre aucun mot de passe dans la conversation.
