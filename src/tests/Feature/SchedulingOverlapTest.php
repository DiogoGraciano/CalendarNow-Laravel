<?php

namespace Tests\Feature;

use App\Actions\Scheduling\UpdateSchedulingAction;
use App\Models\Accounts;
use App\Models\Calendar;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\Scheduling;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SchedulingOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_scheduling_fails_when_employee_has_same_slot_on_another_calendar(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'overlap-update.localhost']);

        tenancy()->initialize($tenant);

        $calA = Calendar::factory()->create();
        $calB = Calendar::factory()->create();
        $employee = Employee::factory()->create();
        $customer = Customer::factory()->create();
        $account = Accounts::factory()->create(['customer_id' => $customer->id]);

        $startA = now()->next('Monday')->setTime(9, 0);
        $endA = $startA->copy()->addHour();
        $startB = now()->next('Monday')->setTime(14, 0);
        $endB = $startB->copy()->addHour();

        $schedulingA = Scheduling::create([
            'code' => 'SCH-A-001',
            'calendar_id' => $calA->id,
            'employee_id' => $employee->id,
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'start_time' => $startA,
            'end_time' => $endA,
            'status' => 'pending',
        ]);

        $schedulingB = Scheduling::create([
            'code' => 'SCH-B-001',
            'calendar_id' => $calB->id,
            'employee_id' => $employee->id,
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'start_time' => $startB,
            'end_time' => $endB,
            'status' => 'pending',
        ]);

        $this->expectException(ValidationException::class);

        (new UpdateSchedulingAction)->handle([
            'calendar_id' => $schedulingB->calendar_id,
            'employee_id' => $employee->id,
            'customer_id' => $customer->id,
            'start_time' => $startA->format('Y-m-d\TH:i'),
            'end_time' => $endA->format('Y-m-d\TH:i'),
            'items' => [],
        ], $schedulingB);

        tenancy()->end();
    }
}
