<?php

namespace App\Actions\Marketplace;

use App\Enums\SegmentEnum;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowMarketplaceAction
{
    public function __invoke(Request $request): View
    {
        $segments = SegmentEnum::cases();

        $tenants = Tenant::query()
            ->with('domains')
            ->withCount('services')
            ->whereHas('domains')
            ->latest()
            ->paginate(12);

        $countries = Tenant::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->whereHas('domains')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values();

        $cities = Tenant::query()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->whereHas('domains')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

        $SEOData = new SEOData(
            title: __('marketplace.title'),
            description: __('marketplace.description'),
            image: 'logo.webp',
            url: $request->url(),
        );

        return view('marketplace.index', [
            'segments' => $segments,
            'tenants' => $tenants,
            'countries' => $countries,
            'cities' => $cities,
            'SEOData' => $SEOData,
        ]);
    }
}
