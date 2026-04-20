<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\CustomerReportData;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowCustomerReportAction
{
    use AsAction;
    use CustomerReportData;

    public function asController(ActionRequest $request): Response
    {
        $data = $this->getCustomerReportData($request);

        return Inertia::render('reports/customer-analysis', [
            'customers' => $data['customers'],
            'newVsReturning' => $data['newVsReturning'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
