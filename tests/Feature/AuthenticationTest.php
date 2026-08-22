<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_log_in_with_application_login(): void
    {
        $role = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        User::factory()->create([
            'role_id' => $role->id,
            'login' => 'adminx',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'credential' => 'adminx',
            'password' => 'secret-password',
        ]);

        $response->assertOk()->assertJsonPath('user.login', 'adminx');
        $this->assertAuthenticated();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::factory()->create(['login' => 'blocked', 'is_active' => false, 'password' => 'secret-password']);

        $this->postJson('/api/v1/auth/login', [
            'credential' => 'blocked',
            'password' => 'secret-password',
        ])->assertUnprocessable();

        $this->assertGuest();
    }

    public function test_guest_cannot_access_back_office_api(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    }
}
