<?php

namespace App\Actions\Account;

use App\Models\Accounts;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowAccountFormAction
{
    use AsAction;

    public function asController(ActionRequest $request, ?Accounts $account = null): Response
    {
        return Inertia::render('accounts/form', [
            'account' => $account,
            'isEdit' => $account !== null,
            'dres' => \App\Models\Dre::orderBy('code')->get(['id', 'code', 'description']),
            'customers' => \App\Models\Customer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
