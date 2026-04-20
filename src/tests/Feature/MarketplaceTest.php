<?php

namespace Tests\Feature;

use App\Enums\SegmentEnum;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function centralUrl(string $path = ''): string
    {
        $domain = config('tenancy.central_domains')[0];

        return 'http://'.$domain.($path ? '/'.ltrim($path, '/') : '');
    }

    private function createTenantWithDomain(array $attributes = []): Tenant
    {
        $plan = Plan::factory()->create(['is_default' => true]);

        $tenant = Tenant::factory()->create(array_merge([
            'plan_id' => $plan->id,
        ], $attributes));

        $tenant->domains()->firstOrCreate([
            'domain' => $tenant->id.'.localhost',
        ]);

        return $tenant;
    }

    public function test_marketplace_page_returns_200(): void
    {
        $response = $this->get($this->centralUrl('/marketplace'));

        $response->assertOk();
        $response->assertViewIs('marketplace.index');
    }

    public function test_marketplace_shows_tenants_with_domains(): void
    {
        $tenant = $this->createTenantWithDomain(['name' => 'Salão Teste']);

        $response = $this->get($this->centralUrl('/marketplace'));

        $response->assertOk();
        $response->assertSee('Salão Teste');
    }

    public function test_marketplace_hides_tenants_without_domains(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);

        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'Tenant Sem Domínio',
        ]);

        $response = $this->get($this->centralUrl('/marketplace'));

        $response->assertOk();
        $response->assertDontSee('Tenant Sem Domínio');
    }

    public function test_marketplace_shows_segment_filter_chips(): void
    {
        $response = $this->get($this->centralUrl('/marketplace'));

        $response->assertOk();
        $response->assertSee(SegmentEnum::BeautyAesthetics->label());
    }

    public function test_marketplace_search_filters_by_name(): void
    {
        $this->createTenantWithDomain(['name' => 'Salão Elegante']);
        $this->createTenantWithDomain(['name' => 'Clínica Saúde']);

        $response = $this->get($this->centralUrl('/marketplace/search?search=Salão'));

        $response->assertOk();
        $response->assertSee('Salão Elegante');
        $response->assertDontSee('Clínica Saúde');
    }

    public function test_marketplace_search_filters_by_segment(): void
    {
        $this->createTenantWithDomain(['name' => 'Salão A', 'segment' => SegmentEnum::BeautyAesthetics]);
        $this->createTenantWithDomain(['name' => 'Clínica B', 'segment' => SegmentEnum::HealthWellness]);

        $response = $this->get($this->centralUrl('/marketplace/search?segment=beauty_aesthetics'));

        $response->assertOk();
        $response->assertSee('Salão A');
        $response->assertDontSee('Clínica B');
    }

    public function test_marketplace_search_filters_by_city(): void
    {
        $this->createTenantWithDomain(['name' => 'Salão SP', 'city' => 'São Paulo']);
        $this->createTenantWithDomain(['name' => 'Salão RJ', 'city' => 'Rio de Janeiro']);

        $response = $this->get($this->centralUrl('/marketplace/search?city=São Paulo'));

        $response->assertOk();
        $response->assertSee('Salão SP');
        $response->assertDontSee('Salão RJ');
    }

    public function test_marketplace_search_filters_by_country(): void
    {
        $this->createTenantWithDomain(['name' => 'Salão BR', 'country' => 'Brasil']);
        $this->createTenantWithDomain(['name' => 'Salon US', 'country' => 'United States']);

        $response = $this->get($this->centralUrl('/marketplace/search?country=Brasil'));

        $response->assertOk();
        $response->assertSee('Salão BR');
        $response->assertDontSee('Salon US');
    }

    public function test_marketplace_cities_endpoint_filters_by_country(): void
    {
        $this->createTenantWithDomain(['city' => 'São Paulo', 'country' => 'Brasil']);
        $this->createTenantWithDomain(['city' => 'New York', 'country' => 'United States']);

        $response = $this->get($this->centralUrl('/marketplace/cities?country=Brasil'));

        $response->assertOk();
        $response->assertSee('São Paulo');
        $response->assertDontSee('New York');
    }

    public function test_marketplace_shows_services_count(): void
    {
        $tenant = $this->createTenantWithDomain(['name' => 'Salão Contagem']);

        tenancy()->initialize($tenant);
        Service::factory()->count(3)->create();
        tenancy()->end();

        $response = $this->get($this->centralUrl('/marketplace'));

        $response->assertOk();
        $response->assertSee('3');
    }

    public function test_marketplace_tenant_card_links_to_tenant_domain(): void
    {
        $tenant = $this->createTenantWithDomain(['name' => 'Salão Link']);
        $domain = $tenant->domains()->first()->domain;

        $response = $this->get($this->centralUrl('/marketplace'));

        $response->assertOk();
        $response->assertSee($domain.'/agendar');
    }
}
