<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_service_report(): void
    {
        $response = $this->get(route('reports.service-analysis'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_service_pdf(): void
    {
        $response = $this->get(route('reports.service-analysis.pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_service_excel(): void
    {
        $response = $this->get(route('reports.service-analysis.excel'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_service_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.service-analysis'));

        $response->assertOk();
    }

    public function test_authenticated_user_can_download_service_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.service-analysis.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_print_service_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.service-analysis.pdf', ['mode' => 'print']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_download_service_excel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.service-analysis.excel'));

        $response->assertOk();
    }

    public function test_service_pdf_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.service-analysis.pdf', [
            'calendar' => 1,
            'employee' => 1,
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
        ]));

        $response->assertOk();
    }

    public function test_service_excel_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.service-analysis.excel', [
            'calendar' => 1,
            'employee' => 1,
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
        ]));

        $response->assertOk();
    }
}
