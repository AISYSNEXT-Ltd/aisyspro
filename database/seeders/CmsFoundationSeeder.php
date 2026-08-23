<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class CmsFoundationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['title' => 'Accueil', 'slug' => 'accueil', 'meta_title' => 'AISYSPRO — CRM, site web et hébergement métier', 'meta_description' => 'Solutions métier, CRM, sites web connectés et hébergement pour les professionnels et PME.'],
            ['title' => 'Solutions', 'slug' => 'solutions', 'meta_title' => 'Solutions métier configurables | AISYSPRO', 'meta_description' => 'Plus de 50 solutions CRM et applications métier adaptées aux processus de chaque secteur.'],
            ['title' => 'Packs & tarifs', 'slug' => 'packs', 'meta_title' => 'Packs et tarifs AISYSPRO', 'meta_description' => 'Des packs CRM, site web et hébergement avec des prix clairs et des options à la carte.'],
            ['title' => 'Blog', 'slug' => 'blog', 'meta_title' => 'Guides CRM et transformation digitale par métier | AISYSPRO', 'meta_description' => 'Guides pratiques pour digitaliser votre activité, structurer votre CRM et automatiser vos processus.'],
            ['title' => 'À propos', 'slug' => 'a-propos', 'meta_title' => 'À propos d’AISYSPRO', 'meta_description' => 'AISYSPRO transforme les processus métier en solutions simples, robustes et évolutives.'],
            ['title' => 'FAQ', 'slug' => 'faq', 'meta_title' => 'FAQ AISYSPRO', 'meta_description' => 'Réponses sur les packs, la personnalisation, les données, les délais, la sécurité et le support.'],
            ['title' => 'Contact', 'slug' => 'contact', 'meta_title' => 'Contact AISYSPRO', 'meta_description' => 'Parlez-nous de votre projet CRM, site web, hébergement ou transformation digitale.'],
        ] as $page) {
            Page::query()->firstOrCreate(['slug' => $page['slug']], $page + ['status' => 'published', 'robots' => 'index,follow', 'published_at' => now()]);
        }

        $header = Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Menu principal', 'is_active' => true]);
        $footer = Menu::query()->firstOrCreate(['location' => 'footer'], ['name' => 'Pied de page', 'is_active' => true]);
        $this->items($header, [
            ['Accueil', '/'], ['Solutions', '/solutions'], ['Packs', '/packs'], ['Blog', '/blog'],
            ['À propos', '/a-propos'], ['FAQ', '/faq'], ['Contact', '/contact'],
        ]);
        $this->items($footer, [
            ['Mentions légales', '/mentions-legales'], ['Confidentialité', '/confidentialite'], ['Demander un devis', '/devis'],
        ]);

        foreach ([
            ['group' => 'general', 'key' => 'brand_name', 'value' => 'AISYSPRO'],
            ['group' => 'general', 'key' => 'email', 'value' => 'contact.aisyspro@gmail.com'],
            ['group' => 'general', 'key' => 'phone', 'value' => '+216 51 912 668'],
            ['group' => 'general', 'key' => 'locale', 'value' => 'fr-TN'],
            ['group' => 'seo', 'key' => 'default_robots', 'value' => 'index,follow'],
        ] as $setting) {
            SiteSetting::query()->firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    private function items(Menu $menu, array $items): void
    {
        foreach ($items as $order => [$label, $url]) {
            MenuItem::query()->firstOrCreate(
                ['menu_id' => $menu->id, 'label' => $label],
                ['url' => $url, 'link_type' => 'internal', 'target' => '_self', 'is_visible' => true, 'sort_order' => ($order + 1) * 10],
            );
        }
    }
}
