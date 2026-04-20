<?php

namespace App\Actions\Account;

use App\Models\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class MassCancelAccountsAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Contas canceladas com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): void
    {
        $ids = $validated['ids'] ?? [];
        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'Selecione ao menos uma conta',
            ]);
        }
        DB::transaction(function () use ($ids) {
            Accounts::whereIn('id', $ids)->update(['status' => 'cancelled']);
        });
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:accounts,id',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
