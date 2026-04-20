<?php

namespace App\Actions\Schedule;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAvailableCalendarsAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $user = $request->user();

        // Buscar agendas do usuário
        $calendars = $user->calendars()->withCount('schedulings')->get();

        return Inertia::render('schedule/index', [
            'calendars' => $calendars,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
