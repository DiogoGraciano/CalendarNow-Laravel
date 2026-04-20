<?php

namespace App\Actions\Dre;

use App\Models\Dre;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDreFormAction
{
    use AsAction;

    public function asController(ActionRequest $request, ?Dre $dre = null): Response
    {
        return Inertia::render('dres/form', [
            'dre' => $dre,
            'isEdit' => $dre !== null,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
