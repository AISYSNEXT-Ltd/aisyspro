<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\ReferenceValue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        $this->admin = User::factory()->create(['role_id' => $role->id]);
    }

    public function test_creating_a_lead_creates_one_initial_draft_quote_and_activity(): void
    {
        $payload = [
            'submission_uuid' => 'b1016417-4a57-4c8d-b65a-7f1e5f0746c6',
            'name' => 'Prospect AISYSPRO',
            'email' => 'prospect@example.test',
            'source' => 'manual',
            'status' => 'new',
            'value' => 750,
        ];

        $this->actingAs($this->admin)->postJson('/api/v1/leads', $payload)
            ->assertCreated()
            ->assertJsonPath('data.quotes.0.status', 'draft')
            ->assertJsonPath('data.quotes.0.total', '750.000');

        $lead = Lead::query()->firstOrFail();
        $this->assertDatabaseHas('quotes', ['lead_id' => $lead->id, 'is_initial' => true, 'status' => 'draft']);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'quote_created']);

        $this->actingAs($this->admin)->postJson('/api/v1/leads', $payload)->assertOk();
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('quotes', 1);
        $this->assertDatabaseCount('lead_activities', 1);
    }

    public function test_pipeline_uses_configured_order_and_records_stage_changes(): void
    {
        $lead = Lead::create(['name' => 'Pipeline Test', 'source' => 'manual', 'status' => 'new']);

        $this->actingAs($this->admin)->getJson('/api/v1/lead-pipeline')
            ->assertOk()->assertJsonPath('data.0.stage.code', 'new');

        $this->actingAs($this->admin)->patchJson("/api/v1/leads/{$lead->id}/stage", ['status' => 'qualified'])
            ->assertOk()->assertJsonPath('data.status', 'qualified');

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'status_changed',
        ]);
    }

    public function test_used_reference_cannot_be_deleted_and_only_one_default_remains(): void
    {
        $used = ReferenceValue::where('group_key', 'lead_status')->where('code', 'new')->firstOrFail();
        Lead::create(['name' => 'Référence utilisée', 'status' => 'new']);

        $this->actingAs($this->admin)->deleteJson("/api/v1/reference-values/{$used->id}")
            ->assertUnprocessable();

        $qualified = ReferenceValue::where('group_key', 'lead_status')->where('code', 'qualified')->firstOrFail();
        $this->actingAs($this->admin)->putJson("/api/v1/reference-values/{$qualified->id}", [
            'group_key' => 'lead_status', 'code' => 'qualified', 'label' => 'Qualifié',
            'color' => '#0891b2', 'sort_order' => 30, 'is_default' => true, 'is_active' => true,
        ])->assertOk();

        $this->assertSame(1, ReferenceValue::where('group_key', 'lead_status')->where('is_default', true)->count());
        $this->assertTrue($qualified->refresh()->is_default);
    }

    public function test_commercial_can_read_references_but_cannot_change_them(): void
    {
        $role = Role::create(['name' => 'Commercial', 'slug' => 'commercial']);
        $commercial = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($commercial)->getJson('/api/v1/reference-values?group_key=lead_status')->assertOk();
        $this->actingAs($commercial)->postJson('/api/v1/reference-values', [])->assertForbidden();
    }
}
