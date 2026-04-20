<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\DreReportData;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

use function Spatie\LaravelPdf\Support\pdf;

class ExportDrePdfAction
{
    use AsAction;
    use DreReportData;

    public function asController(ActionRequest $request): mixed
    {
        $data = $this->getDreReportData($request);
        $data['tenantName'] = tenant()->name ?? '';

        $builder = pdf('reports.dre-pdf', $data)
            ->name('dre-report.pdf');

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
