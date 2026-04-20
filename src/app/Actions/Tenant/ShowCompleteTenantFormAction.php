<?php

namespace App\Actions\Tenant;

use App\Enums\SegmentEnum;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowCompleteTenantFormAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $tenant = tenant();
        $segments = SegmentEnum::toSelectArray();

        return Inertia::render('tenant/complete-profile', [
            'tenant' => $tenant,
            'segments' => $segments,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
