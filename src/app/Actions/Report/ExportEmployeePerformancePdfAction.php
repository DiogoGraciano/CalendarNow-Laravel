<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\EmployeePerformanceReportData;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

use function Spatie\LaravelPdf\Support\pdf;

class ExportEmployeePerformancePdfAction
{
    use AsAction;
    use EmployeePerformanceReportData;

    public function asController(ActionRequest $request): mixed
    {
        $data = $this->getEmployeePerformanceData($request);
        $data['tenantName'] = tenant()->name ?? '';

        $builder = pdf('reports.employee-performance-pdf', $data)
            ->name('employee-performance-report.pdf');

        if ($request->input('mode') === 'print') {
            return $builder->inline();
        }

        return $builder->download();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
