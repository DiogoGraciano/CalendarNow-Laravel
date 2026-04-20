<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\ServiceReportData;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

use function Spatie\LaravelPdf\Support\pdf;

class ExportServicePdfAction
{
    use AsAction;
    use ServiceReportData;

    public function asController(ActionRequest $request): mixed
    {
        $data = $this->getServiceReportData($request);
        $data['tenantName'] = tenant()->name ?? '';

        $builder = pdf('reports.service-pdf', $data)
            ->name('service-report.pdf');

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
