<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEmployeeAction
{
    use AsAction;

    public function asController(Employee $employee, ActionRequest $request): RedirectResponse
    {
        $this->handle($employee);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Funcionário excluído com sucesso');
    }

    public function handle(Employee $employee): void
    {
        $employee->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
