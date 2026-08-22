<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackOfficePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_and_allowed_page_size_work_together(): void
    {
        $role = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        $user = User::factory()->create(['role_id' => $role->id]);

        Client::factory()->count(12)->create();
        Client::factory()->count(7)->create(['company' => 'AisysNext']);

        $this->actingAs($user)
            ->getJson('/api/v1/clients?search=AisysNext&per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('total', 7)
            ->assertJsonPath('per_page', 5)
            ->assertJsonPath('last_page', 2);
    }

    public function test_editor_cannot_manage_crm_data(): void
    {
        $role = Role::create(['name' => 'Éditeur', 'slug' => 'editeur']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)->getJson('/api/v1/clients')->assertForbidden();
    }
}
