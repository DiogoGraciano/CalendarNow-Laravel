<?php

namespace App\Actions\Scheduling;

use App\Models\Calendar;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Scheduling;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowSchedulingCalendarAction
{
    use AsAction;

    public function asController(Calendar $calendar, ActionRequest $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Obter o parâmetro employee da rota (pode ser null)
        $employee = $request->route('employee') ? (int) $request->route('employee') : null;

        // Verificar se há funcionários cadastrados
        $totalEmployees = Employee::count();

        if ($totalEmployees === 0) {
            return redirect()
                ->route('employees.create')
                ->with('warning', 'É necessário cadastrar pelo menos um funcionário para acessar o agendamento.');
        }

        // Buscar funcionários da agenda
        // Por enquanto, vamos buscar todos os funcionários que têm agendamentos nesta agenda
        $employeeIds = DB::table('schedulings')
            ->where('calendar_id', $calendar->id)
            ->distinct()
            ->pluck('employee_id')
            ->toArray();

        $employees = Employee::whereIn('id', $employeeIds)
            ->orWhereHas('schedulings', function ($query) use ($calendar) {
                $query->where('calendar_id', $calendar->id);
            })
            ->get();

        // Se não houver funcionários, buscar todos os funcionários do tenant
        if ($employees->isEmpty()) {
            $employees = Employee::all();
        }

        // Selecionar funcionário
        $selectedEmployee = null;
        if ($employee && $employees->contains('id', $employee)) {
            $selectedEmployee = Employee::find($employee);
        } elseif ($employees->isNotEmpty()) {
            $selectedEmployee = $employees->first();
        }

        if (! $selectedEmployee) {
            return redirect()
                ->route('employees.create')
                ->with('warning', 'É necessário cadastrar pelo menos um funcionário para acessar o agendamento.');
        }

        // Configurações do funcionário para o calendário
        $workDays = $selectedEmployee->work_days ?? [1, 2, 3, 4, 5]; // seg, ter, qua, qui, sex

        // Horários de trabalho - assumindo que são strings no formato H:i ou null
        $workStartTime = '08:00';
        $workEndTime = '18:00';
        $launchStartTime = '12:00';
        $launchEndTime = '13:30';

        // TODO: Implementar campos de horário no modelo Employee quando disponíveis

        $customers = Customer::all()->map(fn (Customer $c) => ['id' => $c->id, 'name' => $c->name]);
        $services = Service::all();
        $statuses = [
            ['id' => 'pending', 'name' => 'Pendente'],
            ['id' => 'confirmed', 'name' => 'Confirmado'],
            ['id' => 'completed', 'name' => 'Concluído'],
            ['id' => 'cancelled', 'name' => 'Cancelado'],
        ];

        return Inertia::render('scheduling/index', [
            'calendar' => $calendar,
            'employees' => $employees,
            'selectedEmployee' => $selectedEmployee,
            'workDays' => $workDays,
            'workStartTime' => $workStartTime,
            'workEndTime' => $workEndTime,
            'launchStartTime' => $launchStartTime,
            'launchEndTime' => $launchEndTime,
            'eventsUrl' => url('/scheduling/events/'.$calendar->id.'/'.$selectedEmployee->id),
            'customers' => $customers,
            'services' => $services,
            'statuses' => $statuses,
            'customersStoreUrl' => route('customers.store'),
            'createSchedulingCode' => Scheduling::generateNextCode(),
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
