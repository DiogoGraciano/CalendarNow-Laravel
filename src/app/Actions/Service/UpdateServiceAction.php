<?php

namespace App\Actions\Service;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateServiceAction
{
    use AsAction, WithAttributes;

    public function asController(Service $service, ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated, $service, $request);

        return redirect()
            ->route('services.index')
            ->with('success', 'Serviço atualizado com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?Service $service = null, ?Request $request = null): void
    {
        if ($service === null) {
            throw new \InvalidArgumentException('Service is required for update.');
        }
        $employeeIds = $validated['employee_ids'] ?? [];
        unset($validated['image'], $validated['employee_ids']);
        $service->update($validated);
        $service->employees()->sync($employeeIds);
        if ($request && $request->hasFile('image')) {
            $service->clearMediaCollection('images');
            $service->addMediaFromRequest('image')->toMediaCollection('images');
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
