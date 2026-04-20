<?php

namespace App\Actions\EmployeeDayOff;

use App\Models\Employee;
use App\Models\EmployeeDayOff;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListEmployeeDaysOffAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $query = EmployeeDayOff::with('employee.user')
            ->orderByDesc('start_date');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->query('employee_id'));
        }

        $daysOff = $query->paginate(15)->withQueryString();

        $employees = Employee::with('user')->orderBy('email')->get()->map(fn (Employee $e) => [
            'id' => $e->id,
            'name' => $e->user?->name ?? $e->email ?? 'Funcionário #'.$e->id,
        ]);

        return Inertia::render('employee-days-off/index', [
            'daysOff' => $daysOff,
            'employees' => $employees,
            'selectedEmployeeId' => $request->query('employee_id'),
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
