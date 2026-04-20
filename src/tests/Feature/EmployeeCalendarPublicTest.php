<?php

namespace Tests\Feature;

use App\Actions\Employee\UpdateEmployeeAction;
use App\Models\Calendar;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeCalendarPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_employee_syncs_calendars_and_only_one_is_public(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'empcal.localhost']);

        tenancy()->initialize($tenant);

        $employee = Employee::factory()->create();
        $cal1 = Calendar::factory()->create(['name' => 'Agenda 1']);
        $cal2 = Calendar::factory()->create(['name' => 'Agenda 2']);

        $action = new UpdateEmployeeAction;
        $action->handle([
            'calendar_ids' => [$cal1->id, $cal2->id],
            'public_calendar_id' => $cal2->id,
        ], $employee);

        $this->assertSame(2, (int) DB::table('employee_calendars')->where('employee_id', $employee->id)->count());
        $this->assertSame(1, (int) DB::table('employee_calendars')->where('employee_id', $employee->id)->where('is_public', true)->count());
        $pivot = DB::table('employee_calendars')->where('employee_id', $employee->id)->where('calendar_id', $cal2->id)->first();
        $this->assertTrue((bool) $pivot->is_public);
        $pivot1 = DB::table('employee_calendars')->where('employee_id', $employee->id)->where('calendar_id', $cal1->id)->first();
        $this->assertFalse((bool) $pivot1->is_public);

        $action->handle([
            'calendar_ids' => [$cal1->id, $cal2->id],
            'public_calendar_id' => $cal1->id,
        ], $employee);

        $this->assertSame(1, (int) DB::table('employee_calendars')->where('employee_id', $employee->id)->where('is_public', true)->count());
        $pivot1After = DB::table('employee_calendars')->where('employee_id', $employee->id)->where('calendar_id', $cal1->id)->first();
        $this->assertTrue((bool) $pivot1After->is_public);
        $pivot2After = DB::table('employee_calendars')->where('employee_id', $employee->id)->where('calendar_id', $cal2->id)->first();
        $this->assertFalse((bool) $pivot2After->is_public);

        tenancy()->end();
    }

    public function test_employee_public_calendar_returns_calendar_with_is_public_pivot(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'empcal2.localhost']);

        tenancy()->initialize($tenant);

        $employee = Employee::factory()->create();
        $calPublic = Calendar::factory()->create(['name' => 'Agenda Pública']);
        $calOther = Calendar::factory()->create(['name' => 'Outra']);
        $employee->calendars()->attach($calOther->id, ['is_public' => false]);
        $employee->calendars()->attach($calPublic->id, ['is_public' => true]);

        $this->assertNotNull($employee->publicCalendar());
        $this->assertSame($calPublic->id, $employee->publicCalendar()->id);

        tenancy()->end();
    }
}
