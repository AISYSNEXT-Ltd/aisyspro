<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Inquiry;
use App\Models\Pack;
use App\Models\Role;
use App\Models\Solution;
use App\Models\User;
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
