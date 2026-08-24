<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Pack;
use App\Models\Role;
use App\Models\Solution;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['slug' => 'administrateur'],
            ['name' => 'Administrateur'],
        );

        Role::query()->firstOrCreate(['slug' => 'commercial'], ['name' => 'Commercial']);
        Role::query()->firstOrCreate(['slug' => 'editeur'], ['name' => 'Éditeur']);

        $this->call(CmsFoundationSeeder::class);

        foreach ([
            ['title' => 'LEXISPRO', 'slug' => 'lexispro', 'short_description' => 'Pilotage des cabinets juridiques, dossiers et échéances.', 'sort_order' => 1010],
            ['title' => 'MEDISPRO', 'slug' => 'medispro', 'short_description' => 'Gestion structurée des cabinets médicaux et de leur activité.', 'sort_order' => 1020],
            ['title' => 'SERVISPRO', 'slug' => 'servispro', 'short_description' => 'CRM et opérations pour les sociétés de services.', 'sort_order' => 1030],
        ] as $solution) {
            Solution::query()->firstOrCreate(['slug' => $solution['slug']], $solution + ['description' => $solution['short_description'], 'status' => 'draft', 'featured' => false]);
        }

        foreach ([
            ['name' => 'Pack CRM', 'slug' => 'crm', 'description' => 'Piloter votre activité.', 'price' => 500, 'billing_period' => 'paiement unique', 'features' => ['CRM adapté à votre métier', 'Prospects, clients et historique', 'Pipeline commercial configurable', 'Tâches, rappels et tableaux de bord', 'Paramétrage initial inclus'], 'sort_order' => 10],
            ['name' => 'CRM + Site web', 'slug' => 'business', 'description' => 'Gérer et développer.', 'price' => 750, 'billing_period' => 'paiement unique', 'features' => ['Tout le Pack CRM', 'Site web responsive professionnel', 'CMS : pages, blog, FAQ et témoignages', 'Formulaires connectés au CRM', 'Référencement SEO de base'], 'sort_order' => 20, 'featured' => true],
            ['name' => 'Pack 360', 'slug' => 'complete', 'description' => 'Une solution clé en main.', 'price' => 950, 'billing_period' => 'hébergement valable 1 an', 'features' => ['Tout le pack CRM + Site web', 'Hébergement sécurisé pendant 1 an', 'Nom de domaine pendant 1 an', 'Certificat SSL et sauvegardes', 'Emails professionnels et mise en ligne'], 'sort_order' => 30],
        ] as $pack) {
            Pack::query()->updateOrCreate(['slug' => $pack['slug']], $pack + ['status' => 'published', 'featured' => false]);
        }

        foreach ([
            ['question' => 'Puis-je déployer la solution AISYSPRO sur mon propre VPS ?', 'answer' => 'Oui, si l’option d’auto-hébergement est prévue dans votre offre. Vous gardez le contrôle de l’infrastructure et AISYSPRO peut prendre en charge l’installation, le HTTPS, la base, les sauvegardes et la maintenance selon le contrat.', 'category' => 'Hébergement', 'sort_order' => 10],
            ['question' => 'Quelle différence entre les trois packs AISYSPRO ?', 'answer' => 'Le Pack CRM structure la gestion commerciale. CRM + Site web ajoute une présence web connectée et administrable. Le Pack 360 complète l’ensemble avec domaine, hébergement, SSL, sauvegardes et emails pendant un an.', 'category' => 'Offres', 'sort_order' => 20],
            ['question' => 'Combien de temps faut-il pour lancer une solution ?', 'answer' => 'Le délai dépend du périmètre, des données à reprendre et des validations. Un planning précis est confirmé après le cadrage et le prototype.', 'category' => 'Projet', 'sort_order' => 30],
            ['question' => 'La solution s’adapte-t-elle réellement à mon métier ?', 'answer' => 'Oui. Les rôles, champs, statuts, documents, validations et indicateurs sont configurés selon vos processus prioritaires.', 'category' => 'Personnalisation', 'sort_order' => 40],
            ['question' => 'Pouvez-vous reprendre mes fichiers Excel ou mon ancien CRM ?', 'answer' => 'Oui, après analyse de la qualité, du volume et de la structure des données. La méthode d’import et les contrôles sont intégrés au devis.', 'category' => 'Données', 'sort_order' => 50],
            ['question' => 'Les options sont-elles obligatoires ?', 'answer' => 'Non. Elles sont ajoutées uniquement lorsqu’elles créent une valeur utile pour votre activité.', 'category' => 'Tarifs', 'sort_order' => 60],
            ['question' => 'Que se passe-t-il après la première année du Pack 360 ?', 'answer' => 'Le renouvellement des services d’hébergement, de domaine, d’emails et de maintenance est présenté avant l’échéance selon le périmètre réellement utilisé.', 'category' => 'Hébergement', 'sort_order' => 70],
            ['question' => 'AISYSPRO assure-t-elle la maintenance ?', 'answer' => 'Oui. Le périmètre, les délais d’intervention et les mises à jour incluses sont précisés dans votre offre de maintenance.', 'category' => 'Support', 'sort_order' => 80],
            ['question' => 'Comment protégez-vous les accès et les données ?', 'answer' => 'Les accès sont contrôlés par rôle, les communications utilisent HTTPS et l’exploitation prévoit sauvegardes, journalisation, mises à jour et procédures de restauration.', 'category' => 'Sécurité', 'sort_order' => 90],
        ] as $faq) {
            Faq::query()->updateOrCreate(['question' => $faq['question']], $faq + ['status' => 'published']);
        }

        $this->call(ReferenceContentSeeder::class);

        if (config('aisyspro.admin.password')) {
            User::query()->updateOrCreate(
                ['login' => config('aisyspro.admin.login')],
                [
                    'name' => config('aisyspro.admin.name'),
                    'email' => config('aisyspro.admin.email') ?: 'admin@staging.aisyspro.tn',
                    'password' => config('aisyspro.admin.password'),
                    'role_id' => $adminRole->id,
                    'is_active' => true,
                ],
            );
        }
    }
}
