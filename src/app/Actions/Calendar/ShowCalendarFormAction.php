<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowCalendarFormAction
{
    use AsAction;

    public function asController(?int $id, ActionRequest $request): Response
    {
        $calendar = $id ? Calendar::findOrFail($id) : null;

        return Inertia::render('calendars/form', [
            'calendar' => $calendar,
            'isEdit' => $id !== null,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
