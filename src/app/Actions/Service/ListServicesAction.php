<?php

namespace App\Actions\Service;

use App\Models\Employee;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListServicesAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $services = Service::with('employees')
            ->latest()
            ->withCount('schedulingItems')
            ->paginate(15);

        // Adicionar URL da imagem para cada serviço
        $services->getCollection()->transform(function ($service) {
            $service->image_url = $service->getFirstMediaUrl('images');

            return $service;
        });

        $employees = Employee::with('user')->orderBy('email')->get()->map(fn (Employee $e) => [
            'id' => $e->id,
            'name' => $e->user?->name ?? $e->email ?? 'Funcionário #'.$e->id,
        ]);

        return Inertia::render('services/index', [
            'services' => $services,
            'employees' => $employees,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
