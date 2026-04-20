<?php

namespace App\Actions\Service;

use App\Models\Employee;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowServiceFormAction
{
    use AsAction;

    public function asController(ActionRequest $request, ?Service $service = null): Response
    {
        if ($service) {
            $service->load('employees');
            $service->image_url = $service->getFirstMediaUrl('images');
        }

        $employees = Employee::with('user')->orderBy('email')->get()->map(fn (Employee $e) => [
            'id' => $e->id,
            'name' => $e->user?->name ?? $e->email ?? 'Funcionário #'.$e->id,
        ]);

        return Inertia::render('services/form', [
            'service' => $service,
            'employees' => $employees,
            'isEdit' => $service !== null,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
