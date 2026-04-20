<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\ServiceReportData;
use App\Exports\ServiceReportExport;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportServiceExcelAction
{
    use AsAction;
    use ServiceReportData;

    public function asController(ActionRequest $request): BinaryFileResponse
    {
        $data = $this->getServiceReportData($request);

        return Excel::download(
            new ServiceReportExport($data['services'], $data['summary']),
            'service-report.xlsx'
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
