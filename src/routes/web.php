<?php

use App\Actions\Marketplace\LoadMarketplaceCitiesAction;
use App\Actions\Marketplace\SearchMarketplaceAction;
use App\Actions\Marketplace\ShowMarketplaceAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

$centralRoutes = config('tenancy.central_domains', []);

foreach ($centralRoutes as $key => $domain) {
    Route::domain($domain)->group(function ($key) {
        Route::get('/', function (Request $request) {
            return Inertia::render('auth/enter-email');
        })->name('home');

        Route::get('/marketplace', ShowMarketplaceAction::class)->name('marketplace');
        Route::get('/marketplace/search', SearchMarketplaceAction::class)->name('marketplace.search');
        Route::get('/marketplace/cities', LoadMarketplaceCitiesAction::class)->name('marketplace.cities');

        Route::post('/login/redirect', function (Request $request) {
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:255'],
            ]);

            $user = User::withoutGlobalScopes()->where('email', $request->input('email'))->first();

            if (! $user || ! $user->tenant_id) {
                throw ValidationException::withMessages([
                    'email' => __('Não encontramos uma conta com este e-mail.'),
                ]);
            }

            $tenant = Tenant::find($user->tenant_id);
            $tenantDomain = $tenant?->domains()->first()?->domain;

            if (! $tenantDomain) {
                throw ValidationException::withMessages([
                    'email' => __('Não encontramos uma conta com este e-mail.'),
                ]);
            }

            $scheme = $request->getScheme();
            $url = $scheme.'://'.$tenantDomain.'/login?email='.rawurlencode($request->input('email'));

            if ($request->header('X-Inertia')) {
                return Inertia::location($url);
            }

            return redirect()->away($url);
        })->middleware('throttle:login')->name('login.redirect');

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/dashboard', function (Request $request) {
                $tenantDomain = null;

                $user = Auth::user();

                if ($user && $user->tenant_id) {
                    $tenant = Tenant::find($user->tenant_id);
                    if ($tenant) {
                        $domain = $tenant->domains()->first();
                        if ($domain) {
                            $tenantDomain = $domain->domain;
                        }
                    }
                }

                if ($tenantDomain) {
                    $scheme = $request->getScheme();
                    $url = $scheme.'://'.$tenantDomain.'/dashboard';

                    if ($request->header('X-Inertia')) {
                        return Inertia::location($url);
                    }

                    return redirect($url);
                }

                return redirect()->route('home');
            });
        });
    });
}

require __DIR__.'/settings.php';
require __DIR__.'/tenant.php';
