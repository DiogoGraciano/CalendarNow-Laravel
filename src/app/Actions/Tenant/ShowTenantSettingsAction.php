<?php

namespace App\Actions\Tenant;

use App\Models\Dre;
use App\Models\TenantSetting;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowTenantSettingsAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $dres = Dre::query()
            ->orderBy('code')
            ->get(['id', 'code', 'description', 'type']);

        $schedulingDefaultDreId = TenantSetting::getValue(TenantSetting::KEY_SCHEDULING_DEFAULT_DRE_ID);

        return Inertia::render('settings/tenant', [
            'dres' => $dres,
            'schedulingDefaultDreId' => $schedulingDefaultDreId ? (int) $schedulingDefaultDreId : null,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
