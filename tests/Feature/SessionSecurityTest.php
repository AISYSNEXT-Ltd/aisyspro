<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function loginLocation(): string
    {
        $role = Role::create(['name' => 'Administrateur', 'slug' => 'administrateur']);
        User::factory()->create(['login' => 'security-admin', 'role_id' => $role->id, 'password' => 'test-password']);

        $response = $this->post('/connexion-admin/session', [
            'credential' => 'security-admin', 'password' => 'test-password', 'remember' => true,
        ])->assertRedirectContains('/connexion-admin/complete?token=');
        $this->withCookie(config('session.cookie'), session()->getId());

        return $response->headers->get('Location');
    }

    public function test_completion_cannot_be_replayed(): void
    {
        $location = $this->loginLocation();
        $this->get($location)->assertRedirect('/admin');
        $this->postJson('/api/v1/auth/logout')->assertOk();
        $this->get($location)->assertRedirect('/connexion-admin?error=expired');
        $this->assertGuest();
    }

    public function test_completion_is_bound_to_the_originating_session(): void
    {
        $location = $this->loginLocation();
        $this->app['session']->driver()->regenerate();
        $this->withCookie(config('session.cookie'), session()->getId());
        $this->get($location)->assertRedirect('/connexion-admin?error=expired');
        $this->assertGuest();
    }

    public function test_expired_completion_is_rejected(): void
    {
        $location = $this->loginLocation();
        $this->travel(61)->seconds();
        $this->get($location)->assertRedirect('/connexion-admin?error=expired');
        $this->assertGuest();
    }

    public function test_busy_completion_lock_does_not_authenticate_or_consume_token(): void
    {
        $location = $this->loginLocation();
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $lock = Cache::lock('admin-login-completion:'.hash('sha256', $query['token']).':lock', 10);
        $this->assertTrue($lock->get());
        try {
            $this->get($location)->assertRedirect('/connexion-admin?error=expired');
            $this->assertGuest();
        } finally {
            $lock->release();
        }
        $this->get($location)->assertRedirect('/admin');
    }

    public function test_disabled_account_loses_existing_api_access(): void
    {
        $location = $this->loginLocation();
        $this->get($location)->assertRedirect('/admin');
        User::where('login', 'security-admin')->update(['is_active' => false]);
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
        $this->assertFalse(Auth::guard('web')->check());
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_public_settings_exclude_internal_values(): void
    {
        SiteSetting::create(['group' => 'general', 'key' => 'brand_name', 'value' => 'AISYSPRO']);
        SiteSetting::create(['group' => 'general', 'key' => 'integration_token', 'value' => 'test-only-private']);
        $this->getJson('/api/v1/public/settings')->assertOk()
            ->assertJsonPath('data.brand_name', 'AISYSPRO')
            ->assertJsonMissingPath('data.integration_token')
            ->assertDontSee('test-only-private');
    }

    public function test_staging_diagnostics_are_removed_and_indexing_is_disabled(): void
    {
        $this->app->instance('env', 'staging');
        $this->get('/controle-staging')->assertNotFound();
        $this->getJson('/api/v1/auth/session-status')->assertNotFound();
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /')
            ->assertDontSee('Allow: /')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_login_pages_and_completion_are_not_cacheable_and_hide_referrers(): void
    {
        $response = $this->get('/connexion-admin');
        $response->assertOk()->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response = $this->get('/connexion-admin/complete?token=invalid');
        $response->assertRedirect('/connexion-admin?error=expired')
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
