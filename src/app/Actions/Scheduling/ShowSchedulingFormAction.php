<?php

namespace App\Actions\Scheduling;

use App\Models\Calendar;
use App\Models\Employee;
use App\Models\Scheduling;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowSchedulingFormAction
{
    use AsAction;

    public function asController(
        ActionRequest $request,
        ?Scheduling $scheduling = null,
        ?Calendar $calendar = null,
        ?Employee $employee = null
    ): Response {
        $services = collect();

        if ($scheduling) {
            $scheduling->load(['items.service', 'customer', 'calendar', 'employee']);
            $calendar = $scheduling->calendar;
            $employee = $scheduling->employee;
        }

        if ($employee) {
            $services = $employee->services()->orderBy('order')->orderBy('name')->get();
        }

        // Buscar clientes/usuários
        $customers = \App\Models\Customer::with('user')->get();

        // Status possíveis
        $statuses = [
            ['id' => 'pending', 'name' => 'Pendente'],
            ['id' => 'confirmed', 'name' => 'Confirmado'],
            ['id' => 'completed', 'name' => 'Concluído'],
            ['id' => 'cancelled', 'name' => 'Cancelado'],
        ];

        return Inertia::render('scheduling/form', [
            'customersStoreUrl' => route('customers.store'),
            'scheduling' => $scheduling ? [
                'id' => $scheduling->id,
                'calendar_id' => $scheduling->calendar_id,
                'employee_id' => $scheduling->employee_id,
                'customer_id' => $scheduling->customer_id,
                'start_time' => $scheduling->start_time->format('Y-m-d\TH:i'),
                'end_time' => $scheduling->end_time->format('Y-m-d\TH:i'),
                'status' => $scheduling->status,
                'color' => $scheduling->color,
                'notes' => $scheduling->notes,
                'items' => $scheduling->items->map(function ($item) {
                    return [
                        'service_id' => $item->service_id,
                        'quantity' => $item->quantity,
                        'unit_amount' => $item->unit_amount,
                        'duration' => $item->duration,
                    ];
                })->toArray(),
            ] : null,
            'calendar' => $calendar,
            'employee' => $employee,
            'services' => $services,
            'customers' => $customers,
            'statuses' => $statuses,
            'isEdit' => $scheduling !== null,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
