<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\EmployeePerformanceReportData;
use App\Models\Employee;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowEmployeePerformanceReportAction
{
    use AsAction;
    use EmployeePerformanceReportData;

    public function asController(ActionRequest $request): Response
    {
        $data = $this->getEmployeePerformanceData($request);

        $allEmployees = Employee::with('user')->get()->map(fn ($e) => [
            'id' => $e->id,
            'name' => $e->user?->name ?? "Funcionário #{$e->id}",
        ]);

        $services = Service::all()->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
        ]);

        return Inertia::render('reports/employee-performance', [
            'employees' => $data['employees'],
            'allEmployees' => $allEmployees,
            'services' => $services,
            'summary' => $data['summary'],
            'filters' => $data['filters'],
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
