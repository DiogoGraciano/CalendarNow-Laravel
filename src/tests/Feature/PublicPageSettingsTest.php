<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_public_page_settings(): void
    {
        $response = $this->get(route('configuracoes.public-page'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_public_page_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('configuracoes.public-page'));

        $response->assertOk();
    }

    public function test_can_update_branding_colors(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'primary_color' => '#ff5500',
            'secondary_color' => '#1a1a1a',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tenant = tenant();
        $this->assertEquals('#ff5500', $tenant->fresh()->primary_color);
        $this->assertEquals('#1a1a1a', $tenant->fresh()->secondary_color);
    }

    public function test_can_update_hero_text(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'hero_title' => 'Bem-vindo ao nosso salão',
            'hero_subtitle' => 'Agende seu horário com os melhores profissionais',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tenant = tenant();
        $this->assertEquals('Bem-vindo ao nosso salão', $tenant->fresh()->hero_title);
        $this->assertEquals('Agende seu horário com os melhores profissionais', $tenant->fresh()->hero_subtitle);
    }

    public function test_can_toggle_show_employees_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'show_employees_section' => false,
        ]);

        $response->assertRedirect();

        $tenant = tenant();
        $this->assertFalse($tenant->fresh()->show_employees_section);
    }

    public function test_can_upload_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tenant = tenant();
        $this->assertNotNull($tenant->fresh()->logo);
    }

    public function test_can_upload_favicon(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'favicon' => UploadedFile::fake()->image('favicon.png', 32, 32),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tenant = tenant();
        $this->assertNotNull($tenant->fresh()->favicon);
    }

    public function test_rejects_invalid_color_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'primary_color' => str_repeat('a', 25),
        ]);

        $response->assertSessionHasErrors('primary_color');
    }

    public function test_can_update_seo_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'seo_home_title' => 'Meu Salão - Agendamento Online',
            'seo_home_description' => 'O melhor salão da cidade',
            'seo_booking_title' => 'Agendar Horário - Meu Salão',
            'seo_booking_description' => 'Agende seu horário conosco',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tenant = tenant()->fresh();
        $this->assertEquals('Meu Salão - Agendamento Online', $tenant->seo_home_title);
        $this->assertEquals('O melhor salão da cidade', $tenant->seo_home_description);
        $this->assertEquals('Agendar Horário - Meu Salão', $tenant->seo_booking_title);
        $this->assertEquals('Agende seu horário conosco', $tenant->seo_booking_description);
    }

    public function test_seo_title_max_length_is_70(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'seo_home_title' => str_repeat('a', 71),
        ]);

        $response->assertSessionHasErrors('seo_home_title');
    }

    public function test_seo_description_max_length_is_160(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'seo_home_description' => str_repeat('a', 161),
        ]);

        $response->assertSessionHasErrors('seo_home_description');
    }

    public function test_rejects_oversized_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('configuracoes.public-page.update'), [
            'logo' => UploadedFile::fake()->create('logo.png', 3000),
        ]);

        $response->assertSessionHasErrors('logo');
    }
}
