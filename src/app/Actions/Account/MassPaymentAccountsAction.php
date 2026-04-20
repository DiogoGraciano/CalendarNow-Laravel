<?php

namespace App\Actions\Account;

use App\Models\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class MassPaymentAccountsAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Contas pagas com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): void
    {
        $ids = $validated['ids'] ?? [];
        $paymentDate = $validated['payment_date'] ?? null;
        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'Selecione ao menos uma conta',
            ]);
        }
        if (! $paymentDate) {
            throw ValidationException::withMessages([
                'payment_date' => 'Data de pagamento é obrigatória',
            ]);
        }
        DB::transaction(function () use ($ids, $paymentDate) {
            Accounts::whereIn('id', $ids)->get()->each(function (Accounts $account) use ($paymentDate) {
                $status = $paymentDate > $account->due_date ? 'overdue' : 'paid';
                $account->update([
                    'payment_date' => $paymentDate,
                    'status' => $status,
                ]);
            });
        });
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:accounts,id',
            'payment_date' => 'required|date',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
