<?php

namespace App\Actions\EmployeeDayOff;

use App\Models\EmployeeDayOff;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEmployeeDayOffAction
{
    use AsAction;

    public function asController(EmployeeDayOff $employeeDayOff, ActionRequest $request): RedirectResponse
    {
        $this->handle($employeeDayOff);

        return redirect()
            ->route('employee-days-off.index')
            ->with('success', 'Folga excluída com sucesso');
    }

    public function handle(EmployeeDayOff $employeeDayOff): void
    {
        $employeeDayOff->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
