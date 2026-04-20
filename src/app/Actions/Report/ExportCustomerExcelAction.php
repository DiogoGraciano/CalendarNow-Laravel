<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\CustomerReportData;
use App\Exports\CustomerReportExport;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportCustomerExcelAction
{
    use AsAction;
    use CustomerReportData;

    public function asController(ActionRequest $request): BinaryFileResponse
    {
        $data = $this->getCustomerReportData($request);

        return Excel::download(
            new CustomerReportExport($data['customers'], $data['summary']),
            'customer-report.xlsx'
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
