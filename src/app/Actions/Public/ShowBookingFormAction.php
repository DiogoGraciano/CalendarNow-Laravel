<?php

namespace App\Actions\Public;

use App\Models\Employee;
use App\Models\Service;
use App\Support\ThemeResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowBookingFormAction
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
            ->get();

        $employees = Employee::query()
            ->with(['user', 'services', 'calendars', 'media'])
            ->orderBy('email')
            ->get()
            ->map(function (Employee $e) {
                $publicCalendar = $e->publicCalendar();

                return [
                    'id' => $e->id,
                    'name' => $e->user?->name ?? $e->email ?? 'Funcionário #'.$e->id,
                    'photo_url' => $e->getFirstMediaUrl('photos', 'preview') ?: null,
                    'service_ids' => $e->services->pluck('id')->values()->all(),
                    'public_calendar_id' => $publicCalendar?->id,
                ];
            })
            ->filter(fn (array $e) => $e['public_calendar_id'] !== null && count($e['service_ids']) > 0)
            ->values();

        $SEOData = new SEOData(
            title: $tenant->seo_booking_title ?: __('Agendar').' - '.$tenant->name,
            description: $tenant->seo_booking_description ?: __('Agende seu horário online').' - '.$tenant->name,
            url: $request->url(),
        );

        return view(ThemeResolver::viewPath('booking'), [
            'tenant' => $tenant,
            'services' => $services,
            'employees' => $employees,
            'SEOData' => $SEOData,
        ]);
    }
}
