<?php

namespace App\Actions\Scheduling;

use App\Models\Calendar;
use App\Models\Employee;
use App\Models\Scheduling;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListSchedulingsAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $user = $request->user();

        $calendarId = $request->query('calendar');
        $employeeId = $request->query('employee');
        $dtIni = $request->query('dt_ini');
        $dtFim = $request->query('dt_fim');

        $query = Scheduling::with(['calendar', 'employee', 'customer', 'items.service']);

        // Filtros
        if ($calendarId) {
            $query->where('calendar_id', $calendarId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($dtIni) {
            $query->where('start_time', '>=', Carbon::parse($dtIni));
        }

        if ($dtFim) {
            $query->where('end_time', '<=', Carbon::parse($dtFim));
        }

        // Cliente só vê seus próprios agendamentos
        if ($user->customer) {
            $query->where('customer_id', $user->customer->id);
        }

        $schedulings = $query->latest('start_time')->paginate(15);

        // Buscar agendas e funcionários para filtros
        $calendars = Calendar::all();
        $employees = Employee::all();

        return Inertia::render('scheduling/list', [
            'schedulings' => $schedulings,
            'calendars' => $calendars,
            'employees' => $employees,
            'filters' => [
                'calendar' => $calendarId,
                'employee' => $employeeId,
                'dt_ini' => $dtIni,
                'dt_fim' => $dtFim,
            ],
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
