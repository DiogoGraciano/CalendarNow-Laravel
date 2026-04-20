<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\DreReportData;
use App\Models\Calendar;
use App\Models\Employee;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDreReportAction
{
    use AsAction;
    use DreReportData;

    public function asController(ActionRequest $request): Response
    {
        $data = $this->getDreReportData($request);

        $calendars = Calendar::all();
        $employees = Employee::all();

        return Inertia::render('reports/dre', [
            'schedulingsByDre' => $data['schedulingsByDre'],
            'calendars' => $calendars,
            'employees' => $employees,
            'totals' => $data['totals'],
            'filters' => $data['filters'],
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
