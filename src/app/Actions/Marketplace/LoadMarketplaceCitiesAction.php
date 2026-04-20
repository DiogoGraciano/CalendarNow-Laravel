<?php

namespace App\Actions\Marketplace;

use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LoadMarketplaceCitiesAction
{
    public function __invoke(Request $request): View
    {
        $cities = Tenant::query()
            ->whereHas('domains')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->when($request->input('country'), fn ($q, $country) => $q->where('country', $country))
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

        return view('marketplace.partials.city-options', ['cities' => $cities]);
    }
}
