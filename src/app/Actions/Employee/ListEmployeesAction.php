<?php

namespace App\Actions\Employee;

use App\Models\Calendar;
use App\Models\Employee;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListEmployeesAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $employees = Employee::with(['user', 'media'])
            ->withCount('schedulings')
            ->latest()
            ->paginate(15);

        $employees->getCollection()->transform(function (Employee $employee) {
            $employee->photo_url = $employee->getFirstMediaUrl('photos', 'thumb') ?: null;

            return $employee;
        });

        $employeeToEdit = null;
        if ($request->has('edit')) {
            $employeeId = (int) $request->query('edit');
            $employeeToEdit = Employee::with(['user', 'services', 'calendars', 'media'])->find($employeeId);
            if ($employeeToEdit) {
                $employeeToEdit->photo_url = $employeeToEdit->getFirstMediaUrl('photos', 'preview') ?: null;
            }
        }

        $services = Service::query()->orderBy('order')->orderBy('name')->get(['id', 'name']);
        $calendars = Calendar::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('employees/index', [
            'employees' => $employees,
            'services' => $services,
            'calendars' => $calendars,
            'shouldOpenCreateModal' => $request->has('create'),
            'employeeToEdit' => $employeeToEdit,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
