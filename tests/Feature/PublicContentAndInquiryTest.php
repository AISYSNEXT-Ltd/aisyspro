<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Inquiry;
use App\Models\Pack;
use App\Models\Role;
use App\Models\Solution;
use App\Models\User;
use Database\Seeders\ReferenceContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentAndInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_returns_only_published_records(): void
    {
        Solution::create(['title' => 'Visible', 'slug' => 'visible', 'status' => 'published']);
        Solution::create(['title' => 'Brouillon', 'slug' => 'brouillon', 'status' => 'draft']);
        Pack::create(['name' => 'Pro', 'slug' => 'pro', 'status' => 'published']);
        Faq::create(['question' => 'Question ?', 'answer' => 'Réponse.', 'status' => 'published']);

        $this->getJson('/api/v1/public/content')
            ->assertOk()
            ->assertJsonCount(1, 'solutions')
            ->assertJsonPath('solutions.0.title', 'Visible')
            ->assertJsonCount(1, 'packs')
            ->assertJsonCount(1, 'faqs');
    }

    public function test_public_visitor_can_submit_an_inquiry(): void
    {
        $this->postJson('/api/v1/public/inquiries', [
            'type' => 'quote',
            'name' => 'Client Test',
            'email' => 'client@example.test',
            'company' => 'Entreprise Test',
            'message' => 'Je souhaite recevoir une démonstration complète.',
            'website' => '',
        ])->assertCreated()->assertJsonPath('message', 'Votre demande a bien été enregistrée.');

        $this->assertDatabaseHas('inquiries', [
            'email' => 'client@example.test',
            'type' => 'quote',
            'status' => 'new',
        ]);
    }

    public function test_reference_content_snapshot_imports_all_solutions_and_articles(): void
    {
        $this->seed(ReferenceContentSeeder::class);

        $this->assertDatabaseCount('solutions', 52);
        $this->assertDatabaseCount('blog_posts', 57);
        $this->assertDatabaseHas('solutions', [
            'slug' => 'solution-batiment',
            'category' => 'Opérations terrain',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'comment-deployer-projet-sur-vps',
            'category' => 'Hébergement & DevOps',
            'status' => 'published',
        ]);
    }

    public function test_public_catalogs_support_search_category_and_detail_pages(): void
    {
        Solution::create([
            'title' => 'Solution médicale',
            'slug' => 'solution-medicale',
            'category' => 'Santé',
            'short_description' => 'Gestion du cabinet',
            'status' => 'published',
        ]);
        BlogPost::create([
            'title' => 'Guide santé',
            'slug' => 'guide-sante',
            'category' => 'Santé',
            'content' => 'Contenu du guide.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->getJson('/api/v1/public/solutions?category=Sant%C3%A9&search=m%C3%A9dicale')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'solution-medicale');
        $this->getJson('/api/v1/public/solutions/solution-medicale')
            ->assertOk()->assertJsonPath('data.category', 'Santé');
        $this->getJson('/api/v1/public/posts?category=Sant%C3%A9&search=guide')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'guide-sante');
        $this->getJson('/api/v1/public/posts/guide-sante')
            ->assertOk()->assertJsonPath('data.title', 'Guide santé');
    }

    public function test_honeypot_rejects_automated_submission(): void
    {
        $this->postJson('/api/v1/public/inquiries', [
            'type' => 'contact',
            'name' => 'Robot',
            'email' => 'robot@example.test',
            'message' => 'Message automatisé de validation.',
            'website' => 'https://spam.example',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_public_form_validation_errors_are_translated_to_french(): void
    {
        app()->setLocale('fr');

        $this->postJson('/api/v1/public/inquiries', [
            'type' => 'quote',
            'name' => '',
            'email' => 'invalide',
            'message' => 'court',
            'website' => '',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'Le champ nom est obligatoire.')
            ->assertJsonPath('errors.email.0', 'Le champ e-mail doit être une adresse e-mail valide.');
    }

    public function test_commercial_can_process_inquiries(): void
    {
        $role = Role::create(['name' => 'Commercial', 'slug' => 'commercial']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $inquiry = Inquiry::create([
            'type' => 'contact',
            'name' => 'Prospect',
            'email' => 'prospect@example.test',
            'message' => 'Demande de renseignements commerciaux.',
            'status' => 'new',
        ]);

        $this->actingAs($user)->putJson("/api/v1/inquiries/{$inquiry->id}", [
            'type' => 'contact',
            'name' => 'Prospect',
            'email' => 'prospect@example.test',
            'message' => 'Demande de renseignements commerciaux.',
            'status' => 'in_progress',
            'assigned_to' => $user->id,
        ])->assertOk()->assertJsonPath('data.status', 'in_progress');
    }
}
