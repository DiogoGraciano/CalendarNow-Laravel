<?php

namespace App\Actions\Holiday;

use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateHolidayAction
{
    use AsAction, WithAttributes;

    public function asController(Holiday $holiday, ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated, $holiday);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Feriado atualizado com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, Holiday $holiday): void
    {
        $holiday->update($validated);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'recurring' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
