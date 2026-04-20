<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\CustomerReportData;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

use function Spatie\LaravelPdf\Support\pdf;

class ExportCustomerPdfAction
{
    use AsAction;
    use CustomerReportData;

    public function asController(ActionRequest $request): mixed
    {
        $data = $this->getCustomerReportData($request);
        $data['tenantName'] = tenant()->name ?? '';

        $builder = pdf('reports.customer-pdf', $data)
            ->name('customer-report.pdf');

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
