<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\ServiceReportData;
use App\Models\Calendar;
use App\Models\Employee;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowServiceReportAction
{
    use AsAction;
    use ServiceReportData;

    public function asController(ActionRequest $request): Response
    {
        $data = $this->getServiceReportData($request);

        $calendars = Calendar::all();

        $employees = Employee::with('user')->get()->map(fn ($e) => [
            'id' => $e->id,
            'name' => $e->user?->name ?? "Funcionário #{$e->id}",
        ]);

        return Inertia::render('reports/service-analysis', [
            'services' => $data['services'],
            'calendars' => $calendars,
            'employees' => $employees,
            'summary' => $data['summary'],
            'filters' => $data['filters'],
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
