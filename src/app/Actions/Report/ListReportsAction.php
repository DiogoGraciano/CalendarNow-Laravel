<?php

namespace App\Actions\Report;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListReportsAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        return Inertia::render('reports/index');
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
