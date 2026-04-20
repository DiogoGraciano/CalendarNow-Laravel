<?php

namespace App\Actions\Account;

use App\Models\Accounts;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAccountAction
{
    use AsAction;

    public function asController(Accounts $account, ActionRequest $request): RedirectResponse
    {
        $this->handle($account);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Conta excluída com sucesso');
    }

    public function handle(Accounts $account): void
    {
        $account->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
