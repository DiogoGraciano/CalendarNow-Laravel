<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\EmployeePerformanceReportData;
use App\Exports\EmployeePerformanceReportExport;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportEmployeePerformanceExcelAction
{
    use AsAction;
    use EmployeePerformanceReportData;

    public function asController(ActionRequest $request): BinaryFileResponse
    {
        $data = $this->getEmployeePerformanceData($request);

        return Excel::download(
            new EmployeePerformanceReportExport($data['employees'], $data['summary']),
            'employee-performance-report.xlsx'
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
