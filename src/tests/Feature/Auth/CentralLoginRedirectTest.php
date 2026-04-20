<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentralLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function centralUrl(string $path = ''): string
    {
        $domain = config('tenancy.central_domains')[0];

        return 'http://'.$domain.($path ? '/'.ltrim($path, '/') : '');
    }

    public function test_central_home_renders_enter_email_page(): void
    {
        $response = $this->get($this->centralUrl('/'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/enter-email')
        );
    }

    public function test_login_redirect_with_valid_email_redirects_to_tenant_login_with_email(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'acme.localhost']);

        $user = User::factory()->withoutTwoFactor()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
        ]);

        $response = $this->post($this->centralUrl('login/redirect'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('acme.localhost', $redirectUrl);
        $this->assertStringContainsString('/login', $redirectUrl);
        $this->assertStringContainsString('email='.rawurlencode('user@example.com'), $redirectUrl);
    }

    public function test_login_redirect_with_unknown_email_returns_validation_error(): void
    {
        $response = $this->post($this->centralUrl('login/redirect'), [
            'email' => 'unknown@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect();
    }

    public function test_login_redirect_with_user_without_tenant_returns_validation_error(): void
    {
        $user = User::factory()->withoutTwoFactor()->create([
            'tenant_id' => null,
            'email' => 'notenant@example.com',
        ]);

        $response = $this->post($this->centralUrl('login/redirect'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect();
    }

    public function test_login_redirect_with_tenant_without_domain_returns_validation_error(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        // No domain created for this tenant

        $user = User::factory()->withoutTwoFactor()->create([
            'tenant_id' => $tenant->id,
            'email' => 'nodomain@example.com',
        ]);

        $response = $this->post($this->centralUrl('login/redirect'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect();
    }

    public function test_login_redirect_validates_email_format(): void
    {
        $response = $this->post($this->centralUrl('login/redirect'), [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_tenant_login_page_receives_email_from_query_string(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'acme.localhost']);

        $response = $this->get('http://acme.localhost/login?email=user%40example.com');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/login')
            ->has('email')
            ->where('email', 'user@example.com')
        );
    }
}
