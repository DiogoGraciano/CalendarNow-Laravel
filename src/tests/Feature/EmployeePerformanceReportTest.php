<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePerformanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_employee_performance_report(): void
    {
        $response = $this->get(route('reports.employee-performance'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_employee_performance_pdf(): void
    {
        $response = $this->get(route('reports.employee-performance.pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_employee_performance_excel(): void
    {
        $response = $this->get(route('reports.employee-performance.excel'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_employee_performance_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.employee-performance'));

        $response->assertOk();
    }

    public function test_authenticated_user_can_download_employee_performance_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.employee-performance.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_print_employee_performance_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.employee-performance.pdf', ['mode' => 'print']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_download_employee_performance_excel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.employee-performance.excel'));

        $response->assertOk();
    }

    public function test_employee_performance_pdf_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.employee-performance.pdf', [
            'employee' => 1,
            'service' => 1,
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
        ]));

        $response->assertOk();
    }

    public function test_employee_performance_excel_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.employee-performance.excel', [
            'employee' => 1,
            'service' => 1,
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
        ]));

        $response->assertOk();
    }
}
