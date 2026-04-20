<?php

namespace App\Actions\Holiday;

use App\Models\Holiday;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListHolidaysAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $holidays = Holiday::query()
            ->orderBy('date')
            ->paginate(15);

        return Inertia::render('holidays/index', [
            'holidays' => $holidays,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
