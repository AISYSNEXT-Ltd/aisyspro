<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Role;
use App\Models\Solution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_manage_page_sections_without_replacing_system_content_by_default(): void
    {
        $editor = $this->editor();
        $page = Page::query()->create([
            'title' => 'Accueil', 'slug' => 'accueil', 'status' => 'published',
            'robots' => 'index,follow', 'published_at' => now(),
        ]);

        $this->actingAs($editor)->postJson('/api/v1/page-sections', [
            'page_id' => $page->id,
            'type' => 'text',
            'title' => 'Section administrée',
            'content' => 'Contenu conservé et administrable.',
            'settings' => ['eyebrow' => 'CMS'],
            'display_mode' => 'append',
            'animation' => 'fade',
            'duration' => 500,
            'delay' => 0,
            'intensity' => 30,
            'is_visible' => true,
            'sort_order' => 10,
        ])->assertCreated()->assertJsonPath('data.display_mode', 'append');

        $this->getJson('/api/v1/public/pages/accueil')
            ->assertOk()
            ->assertJsonPath('data.sections.0.title', 'Section administrée');
    }

    public function test_hidden_sections_and_menu_items_are_not_public(): void
    {
        $page = Page::query()->create(['title' => 'FAQ', 'slug' => 'faq', 'status' => 'published', 'robots' => 'index,follow']);
        PageSection::query()->create([
            'page_id' => $page->id, 'type' => 'text', 'title' => 'Masquée', 'display_mode' => 'append',
            'animation' => 'none', 'duration' => 0, 'delay' => 0, 'intensity' => 0, 'is_visible' => false, 'sort_order' => 10,
        ]);
        $menu = Menu::query()->create(['name' => 'Principal', 'location' => 'header', 'is_active' => true]);
        MenuItem::query()->create(['menu_id' => $menu->id, 'label' => 'Visible', 'url' => '/', 'link_type' => 'internal', 'target' => '_self', 'is_visible' => true]);
        MenuItem::query()->create(['menu_id' => $menu->id, 'label' => 'Masqué', 'url' => '/secret', 'link_type' => 'internal', 'target' => '_self', 'is_visible' => false]);

        $this->getJson('/api/v1/public/pages/faq')->assertOk()->assertJsonCount(0, 'data.sections');
        $this->getJson('/api/v1/public/menus/header')->assertOk()->assertJsonCount(1, 'data')->assertJsonMissing(['label' => 'Masqué']);
    }

    public function test_editor_can_upload_and_delete_a_valid_media_file(): void
    {
        Storage::fake('public');
        $editor = $this->editor();
        $response = $this->actingAs($editor)->post('/api/v1/media', [
            'file' => UploadedFile::fake()->create('guide.pdf', 100, 'application/pdf'),
            'alt_text' => 'Guide AISYSPRO',
        ])->assertCreated()->assertJsonPath('data.alt_text', 'Guide AISYSPRO');

        $path = $response->json('data.path');
        Storage::disk('public')->assertExists($path);
        $this->actingAs($editor)->deleteJson('/api/v1/media/'.$response->json('data.id'))->assertNoContent();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_sitemap_404_and_security_headers_are_available(): void
    {
        Solution::query()->create(['title' => 'Solution', 'slug' => 'solution-test', 'status' => 'published']);
        BlogPost::query()->create(['title' => 'Article', 'slug' => 'article-test', 'status' => 'published', 'published_at' => now()]);

        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/solutions/solution-test')->assertSee('/blog/article-test');
        $this->get('/page-inconnue')->assertNotFound();
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    private function editor(): User
    {
        $role = Role::query()->create(['name' => 'Éditeur', 'slug' => 'editeur']);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
