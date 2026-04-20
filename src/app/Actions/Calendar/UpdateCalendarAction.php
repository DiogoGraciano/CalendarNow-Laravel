<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateCalendarAction
{
    use AsAction, WithAttributes;

    public function asController(Calendar $calendar, ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated, $calendar);

        return redirect()
            ->route('calendars.index')
            ->with('success', 'Agenda atualizada com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?Calendar $calendar = null): void
    {
        if ($calendar === null) {
            throw new \InvalidArgumentException('Calendar is required for update.');
        }
        unset($validated['code']);
        $calendar->update($validated);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
