<?php

namespace Tests\Feature;

use App\Models\Page;
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

        $response = $this->postJson('/connexion-admin/session', [
            'credential' => 'adminx',
            'password' => 'secret-password',
        ]);

        $response->assertOk()->assertJsonPath('user.login', 'adminx');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('sessions', [
            'user_id' => User::query()->where('login', 'adminx')->value('id'),
        ]);
    }

    public function test_native_browser_login_redirects_to_admin(): void
    {
        $role = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        User::factory()->create([
            'role_id' => $role->id,
            'login' => 'adminx',
            'password' => 'secret-password',
        ]);

        $this->post('/connexion-admin/session', [
            'credential' => 'adminx',
            'password' => 'secret-password',
            'remember' => true,
        ])->assertRedirect('/admin');

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

    public function test_session_login_grants_access_to_protected_cms_routes(): void
    {
        $role = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        User::factory()->create([
            'role_id' => $role->id,
            'login' => 'adminx',
            'password' => 'secret-password',
        ]);
        Page::query()->create([
            'title' => 'Accueil',
            'slug' => 'accueil',
            'status' => 'published',
            'robots' => 'index,follow',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'credential' => 'adminx',
            'password' => 'secret-password',
            'remember' => true,
        ])->assertOk();

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.login', 'adminx');

        $this->getJson('/api/v1/pages?per_page=100')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'accueil');
    }
}
