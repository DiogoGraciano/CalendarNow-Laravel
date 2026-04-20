<?php

namespace App\Actions\Scheduling;

use App\Models\Scheduling;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteSchedulingAction
{
    use AsAction;

    public function asController(Scheduling $scheduling, ActionRequest $request): RedirectResponse
    {
        $calendarId = $scheduling->calendar_id;
        $employeeId = $scheduling->employee_id;
        $this->handle($scheduling);

        return redirect()
            ->to('/scheduling/'.$calendarId.'/employee/'.$employeeId)
            ->with('success', 'Agendamento excluído com sucesso');
    }

    public function handle(Scheduling $scheduling): void
    {
        $scheduling->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
