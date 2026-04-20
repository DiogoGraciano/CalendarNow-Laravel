<?php

namespace App\Actions\Holiday;

use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteHolidayAction
{
    use AsAction;

    public function asController(Holiday $holiday, ActionRequest $request): RedirectResponse
    {
        $this->handle($holiday);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Feriado excluído com sucesso');
    }

    public function handle(Holiday $holiday): void
    {
        $holiday->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
