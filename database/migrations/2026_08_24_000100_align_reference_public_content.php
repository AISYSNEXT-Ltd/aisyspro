<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PACKS = [
        'Pack CRM' => ['slug' => 'crm', 'legacy_slug' => 'essentiel', 'price' => 500, 'sort_order' => 10, 'featured' => false],
        'CRM + Site web' => ['slug' => 'business', 'legacy_slug' => 'professionnel', 'price' => 750, 'sort_order' => 20, 'featured' => true],
        'Pack 360' => ['slug' => 'complete', 'legacy_slug' => 'entreprise', 'price' => 950, 'sort_order' => 30, 'featured' => false],
    ];

    private const LEGACY_SOLUTIONS = ['lexispro', 'medispro', 'servispro'];

    private const LEGACY_FAQS = [
        'Puis-je commencer avec un seul module ?',
        'Mes données sont-elles séparées ?',
        'Proposez-vous un accompagnement ?',
    ];

    public function up(): void
    {
        if (Schema::hasTable('packs')) {
            foreach (self::PACKS as $name => $pack) {
                DB::table('packs')->where('name', $name)->update([
                    'slug' => $pack['slug'],
                    'price' => $pack['price'],
                    'sort_order' => $pack['sort_order'],
                    'featured' => $pack['featured'],
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('solutions')) {
            DB::table('solutions')->whereIn('slug', self::LEGACY_SOLUTIONS)->update([
                'status' => 'draft',
                'featured' => false,
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('faqs')) {
            DB::table('faqs')->whereIn('question', self::LEGACY_FAQS)->update([
                'status' => 'draft',
                'updated_at' => now(),
            ]);
        }

        $this->synchroniseArticles();
    }

    public function down(): void
    {
        if (Schema::hasTable('packs')) {
            foreach (self::PACKS as $name => $pack) {
                DB::table('packs')->where('name', $name)->update([
                    'slug' => $pack['legacy_slug'],
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('solutions')) {
            DB::table('solutions')->whereIn('slug', self::LEGACY_SOLUTIONS)->update(['status' => 'published']);
        }

        if (Schema::hasTable('faqs')) {
            DB::table('faqs')->whereIn('question', self::LEGACY_FAQS)->update(['status' => 'published']);
        }
    }

    private function synchroniseArticles(): void
    {
        if (! Schema::hasTable('blog_posts')) {
            return;
        }

        $encoded = file_get_contents(database_path('seeders/data/reference-articles.json.gz.b64'));
        $json = $encoded === false ? false : gzdecode(base64_decode(preg_replace('/\s+/', '', $encoded), true));

        if ($json === false) {
            return;
        }

        $homepagePosts = ['comment-deployer-projet-sur-vps', 'digitalisation-batiment', 'digitalisation-medecins'];

        foreach (json_decode($json, true, flags: JSON_THROW_ON_ERROR) as $index => $entry) {
            DB::table('blog_posts')->where('slug', $entry['slug'])->update([
                'sort_order' => $index + 1,
                'featured' => in_array($entry['slug'], $homepagePosts, true),
                'updated_at' => now(),
            ]);
        }
    }
};
