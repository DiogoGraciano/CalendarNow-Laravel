<?php

namespace Tests\Feature;

use App\Models\Accounts;
use App\Models\Calendar;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\Scheduling;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTenantPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_tenant_home_returns_200_and_shows_tenant_name(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'Meu Salão Teste',
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'test.localhost']);

        $response = $this->get('http://test.localhost/');

        $response->assertOk();
        $response->assertSee('Meu Salão Teste', false);
        $response->assertSee('Agendar', false);
    }

    public function test_public_booking_page_returns_200(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'Meu Salão Teste',
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'booking.localhost']);

        $response = $this->get('http://booking.localhost/agendar');

        $response->assertOk();
        $response->assertSee('Agendar', false);
    }

    public function test_public_booking_slots_returns_fragment_without_services(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'slots.localhost']);

        tenancy()->initialize($tenant);
        $calendar = Calendar::factory()->create();

        $response = $this->get('http://slots.localhost/agendar/slots?calendar_id='.$calendar->id);

        $response->assertOk();
        $response->assertSee('slots-days-list', false);
        $response->assertSee('Nenhum horário disponível', false);
        tenancy()->end();
    }

    public function test_public_booking_slots_returns_days_and_slots_when_services_selected(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'slots2.localhost']);

        tenancy()->initialize($tenant);
        $calendar = Calendar::factory()->create();
        $service = Service::factory()->create(['duration' => '01:00:00']);
        $employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
            'work_start_time' => null,
            'work_end_time' => null,
        ]);
        $employee->services()->attach($service->id);
        $employee->calendars()->attach($calendar->id, ['is_public' => true]);

        $response = $this->get('http://slots2.localhost/agendar/slots?calendar_id='.$calendar->id.'&service_ids[]='.$service->id);

        $response->assertOk();
        $response->assertSee('slots-days-list', false);
        $response->assertSee('Carregar mais dias', false);
        tenancy()->end();
    }

    public function test_public_booking_slots_with_cursor_returns_next_days(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'slots3.localhost']);

        tenancy()->initialize($tenant);
        $calendar = Calendar::factory()->create();
        $service = Service::factory()->create(['duration' => '01:00:00']);
        $employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
            'work_start_time' => null,
            'work_end_time' => null,
        ]);
        $employee->services()->attach($service->id);
        $employee->calendars()->attach($calendar->id, ['is_public' => true]);

        $cursor = now()->addDays(7)->format('Y-m-d');
        $response = $this->get('http://slots3.localhost/agendar/slots?calendar_id='.$calendar->id.'&service_ids[]='.$service->id.'&cursor='.$cursor);

        $response->assertOk();
        $response->assertSee('slots-load-more', false);
        tenancy()->end();
    }

    public function test_public_booking_store_creates_scheduling_with_slot_times(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'store.localhost']);

        tenancy()->initialize($tenant);
        $calendar = Calendar::factory()->create();
        $service = Service::factory()->create(['duration' => '01:00:00']);
        $employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
            'work_start_time' => null,
            'work_end_time' => null,
        ]);
        $employee->services()->attach($service->id);
        $employee->calendars()->attach($calendar->id, ['is_public' => true]);

        $start = now()->next('Monday')->setTime(9, 0);
        $end = $start->copy()->addHour();

        $response = $this->post('http://store.localhost/agendar', [
            '_token' => csrf_token(),
            'name' => 'Cliente Teste',
            'email' => 'cliente@example.com',
            'phone' => '11999999999',
            'calendar_id' => $calendar->id,
            'employee_id' => $employee->id,
            'start_time' => $start->format('Y-m-d\TH:i'),
            'end_time' => $end->format('Y-m-d\TH:i'),
            'service_ids' => [$service->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('schedulings', [
            'calendar_id' => $calendar->id,
            'employee_id' => $employee->id,
        ]);
        tenancy()->end();
    }

    public function test_public_booking_fails_when_employee_has_same_slot_on_another_calendar(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'overlap.localhost']);

        tenancy()->initialize($tenant);
        $calendarPublic = Calendar::factory()->create();
        $calendarOther = Calendar::factory()->create();
        $service = Service::factory()->create(['duration' => 60]);
        $employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
            'work_start_time' => null,
            'work_end_time' => null,
        ]);
        $employee->services()->attach($service->id);
        $employee->calendars()->attach($calendarPublic->id, ['is_public' => true]);
        $employee->calendars()->attach($calendarOther->id, ['is_public' => false]);
        $customer = Customer::factory()->create();
        $account = Accounts::factory()->create(['customer_id' => $customer->id]);

        $start = now()->next('Monday')->setTime(9, 0);
        $end = $start->copy()->addHour();

        Scheduling::create([
            'code' => 'SCH-OVERLAP-001',
            'calendar_id' => $calendarOther->id,
            'employee_id' => $employee->id,
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'pending',
        ]);

        $response = $this->post('http://overlap.localhost/agendar', [
            '_token' => csrf_token(),
            'name' => 'Outro Cliente',
            'email' => 'outro@example.com',
            'phone' => '11988887777',
            'calendar_id' => $calendarPublic->id,
            'employee_id' => $employee->id,
            'start_time' => $start->format('Y-m-d\TH:i'),
            'end_time' => $end->format('Y-m-d\TH:i'),
            'service_ids' => [$service->id],
        ]);

        $response->assertSessionHasErrors();
        tenancy()->end();
    }

    public function test_public_home_shows_employees_with_public_calendar(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'Team Test Salon',
            'show_employees_section' => true,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'team.localhost']);

        tenancy()->initialize($tenant);
        $calendar = Calendar::factory()->create();
        $employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
        ]);
        $employee->calendars()->attach($calendar->id, ['is_public' => true]);

        $response = $this->get('http://team.localhost/');

        $response->assertOk();
        $response->assertSee('Nossa equipe', false);
        tenancy()->end();
    }

    public function test_public_home_hides_employees_section_when_disabled(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'No Team Salon',
            'show_employees_section' => false,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'noteam.localhost']);

        $response = $this->get('http://noteam.localhost/');

        $response->assertOk();
        $response->assertDontSee('Nossa equipe', false);
    }

    public function test_public_home_shows_hero_with_custom_title(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'hero_title' => 'Bem-vindo ao Salão XYZ',
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'hero.localhost']);

        $response = $this->get('http://hero.localhost/');

        $response->assertOk();
        $response->assertSee('Bem-vindo ao Salão XYZ', false);
    }

    public function test_public_booking_shows_employee_cards_for_selection(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'cards.localhost']);

        tenancy()->initialize($tenant);
        $calendar = Calendar::factory()->create();
        $service = Service::factory()->create();
        $employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
        ]);
        $employee->services()->attach($service->id);
        $employee->calendars()->attach($calendar->id, ['is_public' => true]);

        $response = $this->get('http://cards.localhost/agendar');

        $response->assertOk();
        $response->assertSee('Escolha o profissional', false);
        $response->assertSee('bookingWizard', false);
        tenancy()->end();
    }

    public function test_public_home_uses_custom_seo_title_when_set(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'Salão SEO',
            'seo_home_title' => 'Custom SEO Title',
            'seo_home_description' => 'Custom SEO Description',
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'seo-home.localhost']);

        $response = $this->get('http://seo-home.localhost/');

        $response->assertOk();
        $response->assertSee('<title>Custom SEO Title</title>', false);
        $response->assertSee('content="Custom SEO Description"', false);
    }

    public function test_public_home_uses_default_seo_when_custom_not_set(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'Salão Default',
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'seo-default.localhost']);

        $response = $this->get('http://seo-default.localhost/');

        $response->assertOk();
        $response->assertSee('<title>Salão Default</title>', false);
    }

    public function test_public_booking_uses_custom_seo_title_when_set(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'name' => 'Salão Booking SEO',
            'seo_booking_title' => 'Custom Booking Title',
            'seo_booking_description' => 'Custom Booking Description',
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'seo-booking.localhost']);

        $response = $this->get('http://seo-booking.localhost/agendar');

        $response->assertOk();
        $response->assertSee('<title>Custom Booking Title</title>', false);
        $response->assertSee('content="Custom Booking Description"', false);
    }

    public function test_public_slots_exclude_times_where_employee_has_booking_on_another_calendar(): void
    {
        $plan = Plan::factory()->create(['is_default' => true]);
        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);
        $tenant->domains()->firstOrCreate(['domain' => 'slots-overlap.localhost']);

        tenancy()->initialize($tenant);
        $calendarPublic = Calendar::factory()->create();
        $calendarOther = Calendar::factory()->create();
        $service = Service::factory()->create(['duration' => 60]);
        $employee = Employee::factory()->create([
            'work_days' => [1, 2, 3, 4, 5],
            'work_start_time' => null,
            'work_end_time' => null,
        ]);
        $employee->services()->attach($service->id);
        $employee->calendars()->attach($calendarPublic->id, ['is_public' => true]);
        $employee->calendars()->attach($calendarOther->id, ['is_public' => false]);
        $customer = Customer::factory()->create();
        $account = Accounts::factory()->create(['customer_id' => $customer->id]);

        $date = now()->next('Monday');
        $start = $date->copy()->setTime(10, 0);
        $end = $start->copy()->addHour();

        Scheduling::create([
            'code' => 'SCH-SLOT-001',
            'calendar_id' => $calendarOther->id,
            'employee_id' => $employee->id,
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'pending',
        ]);

        $response = $this->get('http://slots-overlap.localhost/agendar/slots?calendar_id='.$calendarPublic->id.'&employee_id='.$employee->id.'&service_ids[]='.$service->id);

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringNotContainsString('10:00 - 11:00', $html);
        tenancy()->end();
    }
}
