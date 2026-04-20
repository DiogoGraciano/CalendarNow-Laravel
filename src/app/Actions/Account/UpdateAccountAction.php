<?php

namespace App\Actions\Account;

use App\Models\Accounts;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateAccountAction
{
    use AsAction, WithAttributes;

    public function asController(Accounts $account, ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated, $account);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Conta atualizada com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?Accounts $account = null): void
    {
        if ($account === null) {
            throw new \InvalidArgumentException('Account is required for update.');
        }
        if (($validated['status'] ?? '') === 'paid' && isset($validated['payment_date'], $validated['due_date'])) {
            if ($validated['payment_date'] > $validated['due_date']) {
                $validated['status'] = 'overdue';
            }
        }
        $account->update($validated);
    }

    public function rules(): array
    {
        return [
            'dre_id' => 'required|exists:dres,id',
            'customer_id' => 'required|exists:customers,id',
            'code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'type' => 'required|in:receivable,payable',
            'type_interest' => 'required|in:fixed,variable',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'total' => 'required|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,paid,overdue,cancelled',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
