<?php

namespace App\Actions\Marketplace;

use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchMarketplaceAction
{
    public function __invoke(Request $request): View
    {
        $query = Tenant::query()
            ->with('domains')
            ->withCount('services')
            ->whereHas('domains');

        if ($search = $request->input('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%']);
        }

        if ($segment = $request->input('segment')) {
            $query->where('segment', $segment);
        }

        if ($country = $request->input('country')) {
            $query->where('country', $country);
        }

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        $tenants = $query->latest()->paginate(12);

        return view('marketplace.partials.tenant-grid', [
            'tenants' => $tenants,
        ]);
    }
}
