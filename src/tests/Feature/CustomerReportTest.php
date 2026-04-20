<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_customer_report(): void
    {
        $response = $this->get(route('reports.customer-analysis'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_customer_pdf(): void
    {
        $response = $this->get(route('reports.customer-analysis.pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_customer_excel(): void
    {
        $response = $this->get(route('reports.customer-analysis.excel'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_customer_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.customer-analysis'));

        $response->assertOk();
    }

    public function test_authenticated_user_can_download_customer_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.customer-analysis.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_print_customer_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.customer-analysis.pdf', ['mode' => 'print']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_download_customer_excel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.customer-analysis.excel'));

        $response->assertOk();
    }

    public function test_customer_pdf_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.customer-analysis.pdf', [
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
            'min_visits' => 2,
        ]));

        $response->assertOk();
    }

    public function test_customer_excel_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.customer-analysis.excel', [
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
            'min_visits' => 2,
        ]));

        $response->assertOk();
    }
}
