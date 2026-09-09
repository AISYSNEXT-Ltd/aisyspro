# Lot 3 — CRM, référentiels et fiabilité du Back Office

Date : 9 septembre 2026  
Branche : `develop`

## Diagnostic confirmé

- Les statuts des prospects, devis, clients, demandes et tâches étaient codés en dur dans les contrôleurs et l'interface.
- La page générique du Back Office pouvait rester bloquée sur « Chargement… » lorsqu'une requête principale ou auxiliaire échouait.
- Les prospects étaient gérés uniquement en liste, sans pipeline Kanban configurable.
- La création d'un prospect ne créait pas de devis associé.
- La déconnexion et le profil étaient encore placés en bas du menu latéral.

## Réalisation

- Nouveau référentiel central avec groupe, code stable, libellé, couleur, ordre, valeur par défaut et activation.
- Protection des valeurs déjà utilisées : elles doivent être désactivées au lieu d'être supprimées.
- Lecture des référentiels par les utilisateurs autorisés et modification réservée aux administrateurs.
- Pipeline CRM construit à partir du référentiel `lead_status`, avec recherche et déplacement par glisser-déposer.
- Historisation des changements d'étape.
- Création du prospect et de son devis initial dans une transaction unique.
- Devis initial lié directement au prospect, même lorsqu'aucun client n'a encore été créé.
- Référence déterministe `DEV-AAAA-NNNNNN`, statut `draft`, montants préremplis et événement d'historique.
- UUID de soumission et verrou applicatif pour éviter les doublons lors d'un double clic ou d'une reprise API.
- États chargement, erreur, liste vide et bouton « Réessayer » dans les listes génériques.
- En-tête Back Office avec compteur des nouvelles demandes, profil, paramètres et déconnexion.

## Validation exécutée

- PHPUnit : 41 tests, 210 assertions, tous réussis.
- Laravel Pint : réussi.
- Vue TypeScript : réussi.
- Build Vite : réussi.
- Contrôle des espaces Git et syntaxe PHP des fichiers modifiés : réussi.

## Déploiement

Le code est prêt pour la recette sur `staging.aisyspro.tn`. La production `aisyspro.tn` n'est pas concernée par ce lot tant que la recette fonctionnelle et la vérification MariaDB du staging ne sont pas terminées.
