<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\DreReportData;
use App\Exports\DreReportExport;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportDreExcelAction
{
    use AsAction;
    use DreReportData;

    public function asController(ActionRequest $request): BinaryFileResponse
    {
        $data = $this->getDreReportData($request);

        return Excel::download(
            new DreReportExport($data['schedulingsByDre'], $data['totals']),
            'dre-report.xlsx'
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
