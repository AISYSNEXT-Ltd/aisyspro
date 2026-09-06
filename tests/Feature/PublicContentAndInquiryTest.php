<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Inquiry;
use App\Models\Lead;
use App\Models\OfferOption;
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
        OfferOption::query()->delete();
        OfferOption::create(['name' => 'Visible', 'slug' => 'visible', 'price' => 100, 'status' => 'published']);
        OfferOption::create(['name' => 'Brouillon', 'slug' => 'brouillon', 'price' => 50, 'status' => 'draft']);
        Faq::create(['question' => 'Question ?', 'answer' => 'Réponse.', 'status' => 'published']);

        $this->getJson('/api/v1/public/content')
            ->assertOk()
            ->assertJsonCount(1, 'solutions')
            ->assertJsonPath('solutions.0.title', 'Visible')
            ->assertJsonCount(1, 'packs')
            ->assertJsonCount(1, 'offer_options')
            ->assertJsonCount(1, 'faqs');
    }

    public function test_public_content_uses_reference_pack_and_article_ordering(): void
    {
        Pack::create(['name' => 'CRM + Site web', 'slug' => 'business', 'status' => 'published', 'featured' => true, 'sort_order' => 20]);
        Pack::create(['name' => 'Pack CRM', 'slug' => 'crm', 'status' => 'published', 'featured' => false, 'sort_order' => 10]);

        foreach ([
            ['title' => 'Guide 2', 'slug' => 'guide-2', 'sort_order' => 20, 'featured' => true],
            ['title' => 'Guide 1', 'slug' => 'guide-1', 'sort_order' => 10, 'featured' => true],
            ['title' => 'Guide non mis en avant', 'slug' => 'guide-3', 'sort_order' => 5, 'featured' => false],
        ] as $post) {
            BlogPost::create($post + ['status' => 'published', 'published_at' => now()->subDay()]);
        }

        $this->getJson('/api/v1/public/content')
            ->assertOk()
            ->assertJsonPath('packs.0.slug', 'crm')
            ->assertJsonPath('packs.1.slug', 'business')
            ->assertJsonPath('posts.0.slug', 'guide-1')
            ->assertJsonPath('posts.1.slug', 'guide-2')
            ->assertJsonCount(2, 'posts');

        $this->getJson('/api/v1/public/posts')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'guide-3')
            ->assertJsonPath('data.1.slug', 'guide-1');
    }

    public function test_public_visitor_can_submit_an_inquiry(): void
    {
        $this->postJson('/api/v1/public/inquiries', [
            'type' => 'contact',
            'name' => 'Client Test',
            'email' => 'client@example.test',
            'phone' => '+216 20 000 000',
            'company' => 'Entreprise Test',
            'subject' => 'CRM / application métier',
            'message' => 'Je souhaite recevoir une démonstration complète.',
            'consent' => true,
            'submission_uuid' => '04fd9fd8-26a2-4e43-993a-164964d365db',
            'website' => '',
        ])->assertCreated()->assertJsonPath('message', 'Votre demande a bien été enregistrée.');

        $this->assertDatabaseHas('inquiries', [
            'email' => 'client@example.test',
            'type' => 'contact',
            'status' => 'new',
        ]);
        $this->assertDatabaseHas('leads', [
            'email' => 'client@example.test',
            'source' => 'portal_contact',
            'status' => 'new',
        ]);
        $this->assertNotNull(Inquiry::query()->firstOrFail()->lead_id);
        $this->assertNotNull(Inquiry::query()->firstOrFail()->consent_accepted_at);
    }

    public function test_quote_submission_is_qualified_linked_and_priced_by_the_server(): void
    {
        $pack = Pack::create(['name' => 'CRM + Site web', 'slug' => 'business', 'price' => 750, 'status' => 'published']);
        OfferOption::query()->where('slug', 'seo-local')->update(['price' => 180, 'status' => 'published']);
        OfferOption::create(['name' => 'Option privée', 'slug' => 'private-option', 'price' => 9999, 'status' => 'draft']);

        $payload = [
            'type' => 'quote',
            'name' => 'Prospect Qualifié',
            'email' => 'PROSPECT@example.test',
            'phone' => '+216 21 000 000',
            'company' => 'Entreprise Digitale',
            'activity' => 'Conseil aux entreprises',
            'company_size' => '11-50',
            'user_count' => 18,
            'current_tools' => ['Excel / Google Sheets'],
            'hosting_preference' => 'included',
            'desired_timeline' => '1-3-months',
            'budget_range' => '1000-3000',
            'pack_slug' => 'business',
            'option_slugs' => ['seo-local'],
            'message' => 'Nous souhaitons centraliser le suivi commercial.',
            'consent' => true,
            'submission_uuid' => '729cf876-5ccb-4f4b-86f5-4ddaa1f645f1',
            'source_url' => 'https://staging.aisyspro.tn/devis',
            'estimated_total' => 1,
            'website' => '',
        ];

        $this->postJson('/api/v1/public/inquiries', $payload)
            ->assertCreated()
            ->assertJsonPath('data.reference', 'AISYSPRO-000001')
            ->assertJsonPath('data.estimated_total', 930);

        $inquiry = Inquiry::query()->firstOrFail();
        $lead = Lead::query()->firstOrFail();

        $this->assertSame($pack->id, $inquiry->pack_id);
        $this->assertSame($lead->id, $inquiry->lead_id);
        $this->assertSame('prospect@example.test', $inquiry->email);
        $this->assertSame('930.000', $inquiry->estimated_total);
        $this->assertSame('seo-local', $inquiry->selected_options[0]['slug']);
        $this->assertSame('privacy-v1-2026-09-06', $inquiry->consent_version);
        $this->assertSame('portal_quote', $lead->source);
        $this->assertSame('930.000', $lead->value);

        $this->postJson('/api/v1/public/inquiries', $payload)
            ->assertOk()
            ->assertJsonPath('data.reference', 'AISYSPRO-000001');

        $this->assertDatabaseCount('inquiries', 1);
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_consent_and_published_catalog_are_enforced(): void
    {
        Pack::create(['name' => 'Pack masqué', 'slug' => 'hidden-pack', 'price' => 500, 'status' => 'draft']);

        $this->postJson('/api/v1/public/inquiries', [
            'type' => 'contact',
            'name' => 'Sans Consentement',
            'email' => 'privacy@example.test',
            'subject' => 'Autre demande',
            'message' => 'Cette demande ne doit pas être enregistrée.',
            'submission_uuid' => '375dcb76-7e1b-4505-b475-93307442f8c4',
            'website' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors('consent');

        $this->postJson('/api/v1/public/inquiries', [
            'type' => 'quote',
            'name' => 'Pack Masqué',
            'email' => 'hidden@example.test',
            'phone' => '+216 22 000 000',
            'activity' => 'Services',
            'pack_slug' => 'hidden-pack',
            'consent' => true,
            'submission_uuid' => '4fd0937f-30bd-4af7-b1cf-4445dc683f2b',
            'website' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors('pack_slug');

        $this->assertDatabaseCount('inquiries', 0);
        $this->assertDatabaseCount('leads', 0);
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

    public function test_editor_can_manage_offer_options_used_by_the_public_configurator(): void
    {
        $role = Role::create(['name' => 'Éditeur', 'slug' => 'editeur']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)->postJson('/api/v1/offer-options', [
            'name' => 'Audit des données',
            'slug' => 'audit-donnees',
            'description' => 'Analyse de la qualité des données avant migration.',
            'price' => 320,
            'status' => 'published',
            'sort_order' => 90,
        ])->assertCreated()->assertJsonPath('data.slug', 'audit-donnees');

        $this->getJson('/api/v1/public/content')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'audit-donnees', 'name' => 'Audit des données']);
    }
}
