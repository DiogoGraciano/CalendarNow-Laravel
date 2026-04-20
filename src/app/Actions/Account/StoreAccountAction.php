<?php

namespace App\Actions\Account;

use App\Models\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreAccountAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Conta criada com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): void
    {
        if (empty($validated['code'])) {
            $validated['code'] = 'ACC-'.Str::upper(Str::random(8));
        }
        if (($validated['status'] ?? '') === 'paid' && isset($validated['payment_date'], $validated['due_date'])) {
            if ($validated['payment_date'] > $validated['due_date']) {
                $validated['status'] = 'overdue';
            }
        }
        Accounts::create($validated);
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
