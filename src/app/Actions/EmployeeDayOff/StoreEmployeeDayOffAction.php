<?php

namespace App\Actions\EmployeeDayOff;

use App\Models\EmployeeDayOff;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreEmployeeDayOffAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated);

        return redirect()
            ->route('employee-days-off.index')
            ->with('success', 'Folga criada com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): EmployeeDayOff
    {
        return EmployeeDayOff::create($validated);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:day_off,vacation,medical_leave,personal,other',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
