<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreCustomerAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): JsonResponse|RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $customer = $this->handle($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Cliente cadastrado com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): Customer
    {
        return Customer::create($validated);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
