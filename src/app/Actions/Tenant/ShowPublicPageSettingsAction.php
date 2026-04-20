<?php

namespace App\Actions\Tenant;

use App\Support\ThemeResolver;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowPublicPageSettingsAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $tenant = tenant();

        return Inertia::render('settings/public-page', [
            'tenant' => [
                'name' => $tenant->name,
                'logo' => $tenant->logo,
                'favicon' => $tenant->favicon,
                'primary_color' => $tenant->primary_color,
                'secondary_color' => $tenant->secondary_color,
                'hero_title' => $tenant->hero_title,
                'hero_subtitle' => $tenant->hero_subtitle,
                'show_employees_section' => $tenant->show_employees_section ?? true,
                'seo_home_title' => $tenant->seo_home_title,
                'seo_home_description' => $tenant->seo_home_description,
                'seo_booking_title' => $tenant->seo_booking_title,
                'seo_booking_description' => $tenant->seo_booking_description,
                'theme' => $tenant->theme ?? 'default',
            ],
            'availableThemes' => ThemeResolver::available(),
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
