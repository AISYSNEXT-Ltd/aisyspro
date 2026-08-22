<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_and_update_a_user(): void
    {
        $adminRole = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        $commercialRole = Role::create(['name' => 'Commercial', 'slug' => 'commercial']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $created = $this->actingAs($admin)->postJson('/api/v1/users', [
            'name' => 'Commercial Test',
            'login' => 'commercial.test',
            'email' => 'commercial@example.test',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'role_id' => $commercialRole->id,
            'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.login', 'commercial.test');

        $id = $created->json('data.id');
        $this->actingAs($admin)->putJson("/api/v1/users/{$id}", [
            'name' => 'Commercial Modifié',
            'login' => 'commercial.test',
            'email' => 'commercial@example.test',
            'password' => '',
            'password_confirmation' => '',
            'role_id' => $commercialRole->id,
            'is_active' => false,
        ])->assertOk()->assertJsonPath('data.is_active', false);
    }

    public function test_non_administrator_cannot_manage_users(): void
    {
        $role = Role::create(['name' => 'Commercial', 'slug' => 'commercial']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_administrator_can_list_available_roles(): void
    {
        $adminRole = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        Role::create(['name' => 'Commercial', 'slug' => 'commercial']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)->getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_change_own_password(): void
    {
        $user = User::factory()->create(['password' => 'OldPassword2026']);

        $this->actingAs($user)->putJson('/api/v1/profile/password', [
            'current_password' => 'OldPassword2026',
            'password' => 'NewPassword2026',
            'password_confirmation' => 'NewPassword2026',
        ])->assertOk();

        $this->assertTrue(password_verify('NewPassword2026', $user->fresh()->password));
    }
}
