<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        if ($request->routeIs('tenant.complete-profile') || $request->routeIs('tenant.update')) {
            return $next($request);
        }

        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        $isComplete = $this->isProfileComplete($tenant);

        if (! $isComplete) {
            return redirect()->route('tenant.complete-profile');
        }

        return $next($request);
    }

    /**
     * Verifica se o perfil do tenant está completo.
     */
    private function isProfileComplete($tenant): bool
    {
        $hasPhone = ! empty($tenant->phone);
        $hasAddress = ! empty($tenant->address);
        $hasCity = ! empty($tenant->city);
        $hasSegment = ! empty($tenant->segment);

        $additionalFieldsCount = (int) $hasPhone + (int) $hasAddress + (int) $hasCity + (int) $hasSegment;

        return $additionalFieldsCount >= 2;
    }
}
