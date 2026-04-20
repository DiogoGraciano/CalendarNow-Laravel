<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDayOff;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDayOffCrudTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Employee $employee;

    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::factory()->create(['is_default' => true]);
        $this->tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $this->tenant->domains()->firstOrCreate(['domain' => 'dayoff-test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->user = User::factory()->create();
        $this->employee = Employee::factory()->create();
        $this->baseUrl = 'http://dayoff-test.localhost';
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_can_list_employee_days_off(): void
    {
        EmployeeDayOff::factory()->count(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("{$this->baseUrl}/folgas");

        $response->assertStatus(200);
    }

    public function test_can_filter_days_off_by_employee(): void
    {
        $otherEmployee = Employee::factory()->create();
        EmployeeDayOff::factory()->create(['employee_id' => $this->employee->id]);
        EmployeeDayOff::factory()->create(['employee_id' => $otherEmployee->id]);

        $response = $this->actingAs($this->user)
            ->get("{$this->baseUrl}/folgas?employee_id={$this->employee->id}");

        $response->assertStatus(200);
    }

    public function test_can_create_employee_day_off(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", [
                'employee_id' => $this->employee->id,
                'start_date' => '2026-04-10',
                'end_date' => '2026-04-12',
                'type' => 'vacation',
                'reason' => 'Férias programadas',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_days_off', [
            'employee_id' => $this->employee->id,
            'start_date' => '2026-04-10 00:00:00',
            'end_date' => '2026-04-12 00:00:00',
            'type' => 'vacation',
        ]);
    }

    public function test_can_update_employee_day_off(): void
    {
        $dayOff = EmployeeDayOff::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => 'day_off',
        ]);

        $response = $this->actingAs($this->user)
            ->put("{$this->baseUrl}/folgas/{$dayOff->id}", [
                'employee_id' => $this->employee->id,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'type' => 'vacation',
                'reason' => 'Mudança para férias',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_days_off', [
            'id' => $dayOff->id,
            'type' => 'vacation',
            'start_date' => '2026-05-01 00:00:00',
            'end_date' => '2026-05-05 00:00:00',
        ]);
    }

    public function test_can_delete_employee_day_off(): void
    {
        $dayOff = EmployeeDayOff::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("{$this->baseUrl}/folgas/{$dayOff->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('employee_days_off', ['id' => $dayOff->id]);
    }

    public function test_end_date_must_be_after_or_equal_start_date(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", [
                'employee_id' => $this->employee->id,
                'start_date' => '2026-04-15',
                'end_date' => '2026-04-10',
                'type' => 'day_off',
            ]);

        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_create_requires_employee_and_dates(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", []);

        $response->assertSessionHasErrors(['employee_id', 'start_date', 'end_date', 'type']);
    }

    public function test_unauthenticated_user_cannot_access_days_off(): void
    {
        $response = $this->get("{$this->baseUrl}/folgas");

        $response->assertRedirect();
    }

    public function test_invalid_type_is_rejected(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", [
                'employee_id' => $this->employee->id,
                'start_date' => '2026-04-10',
                'end_date' => '2026-04-10',
                'type' => 'invalid_type',
            ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_nonexistent_employee_is_rejected(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", [
                'employee_id' => 99999,
                'start_date' => '2026-04-10',
                'end_date' => '2026-04-10',
                'type' => 'day_off',
            ]);

        $response->assertSessionHasErrors(['employee_id']);
    }

    public function test_can_create_same_day_off(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", [
                'employee_id' => $this->employee->id,
                'start_date' => '2026-05-20',
                'end_date' => '2026-05-20',
                'type' => 'day_off',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_days_off', [
            'employee_id' => $this->employee->id,
            'start_date' => '2026-05-20 00:00:00',
            'end_date' => '2026-05-20 00:00:00',
        ]);
    }

    public function test_can_create_each_type(): void
    {
        $types = ['day_off', 'vacation', 'medical_leave', 'personal', 'other'];

        foreach ($types as $index => $type) {
            $date = '2026-06-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $this->actingAs($this->user)
                ->post("{$this->baseUrl}/folgas", [
                    'employee_id' => $this->employee->id,
                    'start_date' => $date,
                    'end_date' => $date,
                    'type' => $type,
                ]);

            $this->assertDatabaseHas('employee_days_off', [
                'employee_id' => $this->employee->id,
                'start_date' => $date.' 00:00:00',
                'type' => $type,
            ]);
        }
    }

    public function test_reason_max_500_chars_is_enforced(): void
    {
        $response = $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", [
                'employee_id' => $this->employee->id,
                'start_date' => '2026-04-10',
                'end_date' => '2026-04-10',
                'type' => 'day_off',
                'reason' => str_repeat('A', 501),
            ]);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_can_create_day_off_with_notes(): void
    {
        $this->actingAs($this->user)
            ->post("{$this->baseUrl}/folgas", [
                'employee_id' => $this->employee->id,
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-03',
                'type' => 'medical_leave',
                'reason' => 'Cirurgia',
                'notes' => 'Atestado médico anexado',
            ]);

        $this->assertDatabaseHas('employee_days_off', [
            'employee_id' => $this->employee->id,
            'start_date' => '2026-08-01 00:00:00',
            'notes' => 'Atestado médico anexado',
        ]);
    }
}
