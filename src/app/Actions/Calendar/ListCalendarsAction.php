<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListCalendarsAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $calendars = Calendar::withCount('schedulings')
            ->latest()
            ->paginate(15);

        return Inertia::render('calendars/index', [
            'calendars' => $calendars,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
