<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Dre;
use App\Models\Employee;
use App\Models\EmployeeDayOff;
use App\Models\Holiday;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotBlockingTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Employee $employee;

    private Calendar $calendar;

    private Service $service;

    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::factory()->create(['is_default' => true]);
        $this->tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $this->tenant->domains()->firstOrCreate(['domain' => 'slot-block.localhost']);

        tenancy()->initialize($this->tenant);

        $this->calendar = Calendar::factory()->create();
        $this->employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
            'work_start_time' => '08:00',
            'work_end_time' => '18:00',
        ]);
        $this->service = Service::factory()->create(['duration' => 30]);
        $this->employee->services()->attach($this->service->id);
        $this->employee->calendars()->attach($this->calendar->id, ['is_public' => true]);
        $this->baseUrl = 'http://slot-block.localhost';

        // Configure default DRE for booking tests
        $dre = Dre::factory()->create();
        TenantSetting::setValue(TenantSetting::KEY_SCHEDULING_DEFAULT_DRE_ID, (string) $dre->id);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function slotsUrl(array $params): string
    {
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        // http_build_query encodes arrays as service_ids[0]=X, we need service_ids[]=X
        $query = preg_replace('/%5B\d+%5D/', '%5B%5D', $query);

        return "{$this->baseUrl}/agendar/slots?{$query}";
    }

    private function bookingUrl(): string
    {
        return "{$this->baseUrl}/agendar";
    }

    public function test_holiday_blocks_all_slots_for_date(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        Holiday::factory()->create([
            'date' => $nextMonday->toDateString(),
            'recurring' => false,
        ]);

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $this->employee->id,
            'cursor' => $nextMonday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertDontSee($nextMonday->format('d/m/Y'));
    }

    public function test_recurring_holiday_blocks_slots_matching_month_and_day(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        Holiday::factory()->create([
            'date' => $nextMonday->copy()->subYear()->toDateString(),
            'recurring' => true,
        ]);

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $this->employee->id,
            'cursor' => $nextMonday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertDontSee($nextMonday->format('d/m/Y'));
    }

    public function test_employee_day_off_blocks_only_that_employee(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        $otherEmployee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
            'work_start_time' => '08:00',
            'work_end_time' => '18:00',
        ]);
        $otherEmployee->services()->attach($this->service->id);
        $otherEmployee->calendars()->attach($this->calendar->id, ['is_public' => true]);

        EmployeeDayOff::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->toDateString(),
            'type' => 'day_off',
        ]);

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $otherEmployee->id,
            'cursor' => $nextMonday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonday->format('d/m/Y'));
    }

    public function test_employee_day_off_range_blocks_multiple_days(): void
    {
        $nextMonday = Carbon::now()->next('Monday');
        $nextFriday = $nextMonday->copy()->addDays(4);

        EmployeeDayOff::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextFriday->toDateString(),
            'type' => 'vacation',
        ]);

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $this->employee->id,
            'cursor' => $nextMonday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertDontSee($nextMonday->format('d/m/Y'));
    }

    public function test_booking_on_holiday_is_rejected(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        Holiday::factory()->create([
            'date' => $nextMonday->toDateString(),
            'recurring' => false,
        ]);

        $response = $this->post($this->bookingUrl(), [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '11999999999',
            'calendar_id' => $this->calendar->id,
            'employee_id' => $this->employee->id,
            'start_time' => $nextMonday->copy()->setTime(9, 0)->format('Y-m-d\TH:i'),
            'end_time' => $nextMonday->copy()->setTime(9, 30)->format('Y-m-d\TH:i'),
            'service_ids' => [$this->service->id],
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }

    public function test_booking_on_employee_day_off_is_rejected(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        EmployeeDayOff::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->toDateString(),
            'type' => 'day_off',
        ]);

        $response = $this->post($this->bookingUrl(), [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '11999999999',
            'calendar_id' => $this->calendar->id,
            'employee_id' => $this->employee->id,
            'start_time' => $nextMonday->copy()->setTime(9, 0)->format('Y-m-d\TH:i'),
            'end_time' => $nextMonday->copy()->setTime(9, 30)->format('Y-m-d\TH:i'),
            'service_ids' => [$this->service->id],
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }

    public function test_normal_workday_has_slots(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $this->employee->id,
            'cursor' => $nextMonday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonday->format('d/m/Y'));
    }

    public function test_non_work_day_has_no_slots(): void
    {
        $nextSaturday = Carbon::now()->next('Saturday');

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $this->employee->id,
            'cursor' => $nextSaturday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertDontSee($nextSaturday->format('d/m/Y'));
    }

    public function test_booking_on_recurring_holiday_is_rejected(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        Holiday::factory()->create([
            'date' => $nextMonday->copy()->subYear()->toDateString(),
            'recurring' => true,
        ]);

        $response = $this->post($this->bookingUrl(), [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '11999999999',
            'calendar_id' => $this->calendar->id,
            'employee_id' => $this->employee->id,
            'start_time' => $nextMonday->copy()->setTime(9, 0)->format('Y-m-d\TH:i'),
            'end_time' => $nextMonday->copy()->setTime(9, 30)->format('Y-m-d\TH:i'),
            'service_ids' => [$this->service->id],
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }

    public function test_deleted_holiday_does_not_block_slots(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        $holiday = Holiday::factory()->create([
            'date' => $nextMonday->toDateString(),
            'recurring' => false,
        ]);
        $holiday->delete();

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $this->employee->id,
            'cursor' => $nextMonday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonday->format('d/m/Y'));
    }

    public function test_deleted_day_off_does_not_block_slots(): void
    {
        $nextMonday = Carbon::now()->next('Monday');

        $dayOff = EmployeeDayOff::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->toDateString(),
            'type' => 'day_off',
        ]);
        $dayOff->delete();

        $response = $this->get($this->slotsUrl([
            'calendar_id' => $this->calendar->id,
            'service_ids' => [$this->service->id],
            'employee_id' => $this->employee->id,
            'cursor' => $nextMonday->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonday->format('d/m/Y'));
    }
}
