# Lot 2 — Conversion et qualification commerciale

## Objectif

Transformer les formulaires publics en un flux commercial traçable, sans faire du portail AISYSPRO un ERP. Chaque demande devient une entrée qualifiée du CRM interne et conserve les informations nécessaires au cadrage.

## Fonctionnalités livrées

- création atomique d'une demande et du prospect associé ;
- référence publique stable au format `AISYSPRO-000001` ;
- anti-doublon par UUID de soumission ;
- consentement obligatoire avec date, version et empreinte non réversible de l'adresse IP ;
- choix d'un pack publié et d'options publiées uniquement ;
- calcul du montant estimatif exclusivement côté serveur ;
- conservation d'un instantané des options et prix choisis ;
- qualification par activité, taille, utilisateurs, outils actuels, hébergement, délai et budget ;
- catalogue d'options administrable dans le Back Office ;
- lecture et mise à jour des principaux critères de qualification dans les demandes entrantes.

## Déploiement

La migration `2026_09_06_000200_create_offer_options_and_qualify_inquiries.php` :

1. crée le catalogue `offer_options` ;
2. initialise les huit options de référence ;
3. enrichit `inquiries` sans supprimer ni modifier les demandes existantes.

Le déploiement standard exécute `php artisan migrate --force`. Aucun `db:seed` supplémentaire n'est nécessaire pour rendre le configurateur utilisable.

## Contrôles

- toute offre ou option masquée est refusée par l'API publique ;
- un montant envoyé par le navigateur est ignoré ;
- une répétition du même UUID retourne la demande initiale sans recréer de prospect ;
- les opérations demande/prospect sont regroupées dans une transaction SQL ;
- les formulaires restent limités par le throttle public et le champ anti-robot existants.

## Suite recommandée

Le lot suivant doit ajouter les notifications commerciales, l'historique d'activité du prospect et le passage contrôlé de la demande qualifiée vers un devis métier.
