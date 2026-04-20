<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DreReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_dre_pdf_export(): void
    {
        $response = $this->get(route('reports.dre.pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_dre_excel_export(): void
    {
        $response = $this->get(route('reports.dre.excel'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_dre_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.dre.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_print_dre_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.dre.pdf', ['mode' => 'print']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_download_dre_excel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.dre.excel'));

        $response->assertOk();
    }

    public function test_dre_pdf_export_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.dre.pdf', [
            'calendar' => 1,
            'employee' => 1,
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
        ]));

        $response->assertOk();
    }

    public function test_dre_excel_export_accepts_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.dre.excel', [
            'calendar' => 1,
            'employee' => 1,
            'dt_ini' => '2026-01-01T00:00',
            'dt_fim' => '2026-12-31T23:59',
        ]));

        $response->assertOk();
    }
}
