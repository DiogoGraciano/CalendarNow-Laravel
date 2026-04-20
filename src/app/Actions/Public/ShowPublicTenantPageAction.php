<?php

namespace App\Actions\Public;

use App\Models\Employee;
use App\Models\Service;
use App\Support\ThemeResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowPublicTenantPageAction
{
    public function __invoke(Request $request): View
    {
        $tenant = tenant();

        if (! $tenant) {
            abort(404);
        }

        $services = Service::query()
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(6);

        $employees = collect();
        if ($tenant->show_employees_section ?? true) {
            $employees = Employee::query()
                ->with(['user', 'media'])
                ->whereHas('calendars', fn ($q) => $q->where('employee_calendars.is_public', true))
                ->get()
                ->map(fn (Employee $e) => [
                    'id' => $e->id,
                    'name' => $e->user?->name ?? $e->email ?? 'Funcionário #'.$e->id,
                    'photo_url' => $e->getFirstMediaUrl('photos', 'preview') ?: null,
                ]);
        }

        $defaultDescription = $tenant->name;
        if ($tenant->address || $tenant->city) {
            $defaultDescription .= ' - '.trim(implode(', ', array_filter([$tenant->address, $tenant->city, $tenant->state])));
        }
        $defaultDescription .= '. Agende online.';

        $SEOData = new SEOData(
            title: $tenant->seo_home_title ?: $tenant->name,
            description: $tenant->seo_home_description ?: $defaultDescription,
            image: $tenant->logo ? ltrim($tenant->logo, '/') : null,
            url: $request->url(),
        );

        return view(ThemeResolver::viewPath('home'), [
            'tenant' => $tenant,
            'services' => $services,
            'employees' => $employees,
            'SEOData' => $SEOData,
        ]);
    }
}
