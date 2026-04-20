<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayCrudTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::factory()->create(['is_default' => true]);
        $this->tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $this->tenant->domains()->firstOrCreate(['domain' => 'holiday-test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->user = User::factory()->create();
        $this->baseUrl = 'http://holiday-test.localhost';
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_can_list_holidays(): void
    {
        Holiday::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get("{$this->baseUrl}/feriados");

        $response->assertStatus(200);
    }

    public function test_can_create_holiday(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/feriados", [
                'name' => 'Natal',
                'date' => '2026-12-25',
                'recurring' => true,
                'notes' => 'Feriado nacional',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('holidays', [
            'name' => 'Natal',
            'date' => '2026-12-25 00:00:00',
            'recurring' => true,
        ]);
    }

    public function test_can_update_holiday(): void
    {
        $holiday = Holiday::factory()->create([
            'name' => 'Ano Novo',
            'date' => '2026-01-01',
        ]);

        $response = $this->actingAs($this->user)
            ->put("{$this->baseUrl}/feriados/{$holiday->id}", [
                'name' => 'Ano Novo Atualizado',
                'date' => '2026-01-01',
                'recurring' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'name' => 'Ano Novo Atualizado',
            'recurring' => true,
        ]);
    }

    public function test_can_delete_holiday(): void
    {
        $holiday = Holiday::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("{$this->baseUrl}/feriados/{$holiday->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('holidays', ['id' => $holiday->id]);
    }

    public function test_create_holiday_requires_name_and_date(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/feriados", []);

        $response->assertSessionHasErrors(['name', 'date']);
    }

    public function test_unauthenticated_user_cannot_access_holidays(): void
    {
        $response = $this->get("{$this->baseUrl}/feriados");

        $response->assertRedirect();
    }

    public function test_can_create_holiday_without_optional_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/feriados", [
                'name' => 'Tiradentes',
                'date' => '2026-04-21',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('holidays', [
            'name' => 'Tiradentes',
            'date' => '2026-04-21 00:00:00',
        ]);
    }

    public function test_recurring_defaults_to_false(): void
    {
        $this->actingAs($this->user)
            ->post("{$this->baseUrl}/feriados", [
                'name' => 'Feriado Local',
                'date' => '2026-06-15',
            ]);

        $this->assertDatabaseHas('holidays', [
            'name' => 'Feriado Local',
            'recurring' => false,
        ]);
    }

    public function test_can_create_holiday_with_notes(): void
    {
        $this->actingAs($this->user)
            ->post("{$this->baseUrl}/feriados", [
                'name' => 'Corpus Christi',
                'date' => '2026-06-04',
                'notes' => 'Feriado facultativo em alguns municípios',
            ]);

        $this->assertDatabaseHas('holidays', [
            'name' => 'Corpus Christi',
            'notes' => 'Feriado facultativo em alguns municípios',
        ]);
    }

    public function test_name_max_255_chars_is_enforced(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/feriados", [
                'name' => str_repeat('A', 256),
                'date' => '2026-12-25',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_invalid_date_format_is_rejected(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/feriados", [
                'name' => 'Teste',
                'date' => 'not-a-date',
            ]);

        $response->assertSessionHasErrors(['date']);
    }
}
