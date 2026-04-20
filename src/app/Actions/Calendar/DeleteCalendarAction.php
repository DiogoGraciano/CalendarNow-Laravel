<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCalendarAction
{
    use AsAction;

    public function asController(Calendar $calendar, ActionRequest $request): RedirectResponse
    {
        $this->handle($calendar);

        return redirect()
            ->route('calendars.index')
            ->with('success', 'Agenda excluída com sucesso');
    }

    public function handle(Calendar $calendar): void
    {
        $calendar->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
