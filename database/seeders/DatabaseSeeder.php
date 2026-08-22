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

        foreach ([
            ['title' => 'LEXISPRO', 'slug' => 'lexispro', 'short_description' => 'Pilotage des cabinets juridiques, dossiers et échéances.', 'sort_order' => 10],
            ['title' => 'MEDISPRO', 'slug' => 'medispro', 'short_description' => 'Gestion structurée des cabinets médicaux et de leur activité.', 'sort_order' => 20],
            ['title' => 'SERVISPRO', 'slug' => 'servispro', 'short_description' => 'CRM et opérations pour les sociétés de services.', 'sort_order' => 30],
        ] as $solution) {
            Solution::query()->firstOrCreate(['slug' => $solution['slug']], $solution + ['description' => $solution['short_description'], 'status' => 'published', 'featured' => true]);
        }

        foreach ([
            ['name' => 'Essentiel', 'slug' => 'essentiel', 'description' => 'Les fonctions indispensables pour structurer votre activité.', 'price' => 79, 'billing_period' => 'mois', 'features' => ['CRM clients et prospects', 'Tâches et suivi', 'Support standard'], 'sort_order' => 10],
            ['name' => 'Professionnel', 'slug' => 'professionnel', 'description' => 'Un pilotage complet pour les équipes en croissance.', 'price' => 149, 'billing_period' => 'mois', 'features' => ['Tous les modules métier', 'Devis et tableaux de bord', 'Support prioritaire'], 'sort_order' => 20, 'featured' => true],
            ['name' => 'Entreprise', 'slug' => 'entreprise', 'description' => 'Une configuration adaptée à votre organisation.', 'price' => null, 'billing_period' => 'sur devis', 'features' => ['Personnalisation avancée', 'Accompagnement au déploiement', 'SLA dédié'], 'sort_order' => 30],
        ] as $pack) {
            Pack::query()->firstOrCreate(['slug' => $pack['slug']], $pack + ['status' => 'published', 'featured' => false]);
        }

        foreach ([
            ['question' => 'Puis-je commencer avec un seul module ?', 'answer' => 'Oui. La plateforme est modulaire et peut évoluer avec vos besoins.', 'sort_order' => 10],
            ['question' => 'Mes données sont-elles séparées ?', 'answer' => 'Chaque environnement utilise une configuration et une base indépendantes.', 'sort_order' => 20],
            ['question' => 'Proposez-vous un accompagnement ?', 'answer' => 'Oui, de la configuration initiale à la formation de vos équipes.', 'sort_order' => 30],
        ] as $faq) {
            Faq::query()->firstOrCreate(['question' => $faq['question']], $faq + ['category' => 'Général', 'status' => 'published']);
        }

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
