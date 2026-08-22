<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Solution;
use Illuminate\Database\Seeder;
use RuntimeException;

class ReferenceContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->snapshot('reference-solutions.json.gz.b64') as $entry) {
            Solution::query()->updateOrCreate(
                ['slug' => $entry['slug']],
                [
                    'title' => $entry['title'],
                    'category' => $entry['category'] ?? null,
                    'short_description' => $entry['excerpt'] ?? null,
                    'description' => $entry['content'] ?? null,
                    'problem' => $entry['problem'] ?? null,
                    'value_proposition' => $entry['valueProposition'] ?? null,
                    'modules' => $this->list($entry['modules'] ?? null),
                    'benefits' => $this->list($entry['benefits'] ?? null),
                    'tags' => $this->list($entry['tags'] ?? null),
                    'hero_image' => $entry['heroImage'] ?? null,
                    'meta_title' => $entry['metaTitle'] ?? null,
                    'meta_description' => $entry['metaDescription'] ?? null,
                    'status' => 'published',
                    'featured' => (bool) ($entry['featured'] ?? false),
                    'sort_order' => (int) ($entry['sortOrder'] ?? 0),
                ],
            );
        }

        foreach ($this->snapshot('reference-articles.json.gz.b64') as $entry) {
            BlogPost::query()->updateOrCreate(
                ['slug' => $entry['slug']],
                [
                    'title' => $entry['title'],
                    'category' => $entry['category'] ?? null,
                    'excerpt' => $entry['excerpt'] ?? null,
                    'content' => $entry['content'] ?? null,
                    'tags' => $this->list($entry['tags'] ?? null),
                    'hero_image' => $entry['heroImage'] ?? null,
                    'featured' => (bool) ($entry['featured'] ?? false),
                    'sort_order' => (int) ($entry['sortOrder'] ?? 0),
                    'meta_title' => $entry['metaTitle'] ?? null,
                    'meta_description' => $entry['metaDescription'] ?? null,
                    'status' => 'published',
                    'published_at' => $entry['publishedAt'] ?? now(),
                ],
            );
        }
    }

    private function snapshot(string $file): array
    {
        $encoded = file_get_contents(database_path("seeders/data/{$file}"));
        $json = $encoded === false ? false : gzdecode(base64_decode(preg_replace('/\s+/', '', $encoded), true));

        if ($json === false) {
            throw new RuntimeException("Le snapshot {$file} est illisible.");
        }

        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    private function list(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }
}
