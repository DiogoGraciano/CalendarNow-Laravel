<?php

namespace App\Actions\Dre;

use App\Models\Dre;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListDresAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $dres = Dre::latest()
            ->paginate(15);

        return Inertia::render('dres/index', [
            'dres' => $dres,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
