<?php

namespace App\Actions\Scheduling;

use App\Models\Calendar;
use App\Models\Employee;
use App\Models\EmployeeDayOff;
use App\Models\Holiday;
use App\Models\Scheduling;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class LoadSchedulingEventsAction
{
    use AsAction;

    public function asController(Calendar $calendar, Employee $employee, ActionRequest $request): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');

        if (! $start || ! $end) {
            return response()->json([]);
        }

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        $events = [];

        // Buscar agendamentos
        $schedulings = Scheduling::where('calendar_id', $calendar->id)
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_time', [$startDate, $endDate])
                    ->orWhereBetween('end_time', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_time', '<=', $startDate)
                            ->where('end_time', '>=', $endDate);
                    });
            })
            ->with(['customer', 'items.service'])
            ->get();

        $user = $request->user();

        foreach ($schedulings as $scheduling) {
            // Verificar permissões: cliente só vê seus próprios agendamentos
            if ($user->customer && $scheduling->customer_id !== $user->customer->id) {
                // Mostrar como "Outro agendamento" para clientes
                $events[] = [
                    'title' => 'Outro agendamento',
                    'color' => '#9099ad',
                    'start' => $scheduling->start_time->toIso8601String(),
                    'end' => $scheduling->end_time->toIso8601String(),
                ];
            } else {
                $events[] = [
                    'id' => $scheduling->id,
                    'title' => $scheduling->customer ? $scheduling->customer->name : 'Agendamento',
                    'color' => $scheduling->color ?? '#4267b2',
                    'start' => $scheduling->start_time->toIso8601String(),
                    'end' => $scheduling->end_time->toIso8601String(),
                ];
            }
        }

        // Adicionar horários de almoço
        $workDays = $employee->work_days ?? [1, 2, 3, 4, 5];
        $launchStart = Carbon::createFromTimeString('12:00');
        $launchEnd = Carbon::createFromTimeString('13:30');
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->dayOfWeek;
            $dayNumber = $dayOfWeek === 0 ? 7 : $dayOfWeek;
            if (in_array($dayNumber, $workDays)) {
                $eventStart = $currentDate->copy()
                    ->setTime($launchStart->hour, $launchStart->minute, 0);
                $eventEnd = $currentDate->copy()
                    ->setTime($launchEnd->hour, $launchEnd->minute, 0);
                $events[] = [
                    'title' => 'Almoço',
                    'color' => '#000',
                    'start' => $eventStart->toIso8601String(),
                    'end' => $eventEnd->toIso8601String(),
                    'display' => 'block',
                ];
            }
            $currentDate->addDay();
        }

        // Employee days off as background events
        $daysOff = EmployeeDayOff::query()
            ->where('employee_id', $employee->id)
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->get();

        foreach ($daysOff as $dayOff) {
            $typeLabels = [
                'day_off' => 'Folga',
                'vacation' => 'Férias',
                'medical_leave' => 'Licença Médica',
                'personal' => 'Pessoal',
                'other' => 'Ausente',
            ];
            $events[] = [
                'title' => $typeLabels[$dayOff->type] ?? 'Folga',
                'color' => '#ef4444',
                'start' => $dayOff->start_date->toDateString(),
                'end' => $dayOff->end_date->copy()->addDay()->toDateString(),
                'display' => 'background',
                'allDay' => true,
            ];
        }

        // Holidays as background events
        $holidays = Holiday::query()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->orWhere('recurring', true)
            ->get();

        foreach ($holidays as $holiday) {
            if ($holiday->recurring) {
                $years = range($startDate->year, $endDate->year);
                foreach ($years as $year) {
                    $candidateDate = Carbon::createFromDate($year, $holiday->date->month, $holiday->date->day);
                    if ($candidateDate->between($startDate, $endDate)) {
                        $events[] = [
                            'title' => $holiday->name,
                            'color' => '#f59e0b',
                            'start' => $candidateDate->toDateString(),
                            'end' => $candidateDate->copy()->addDay()->toDateString(),
                            'display' => 'background',
                            'allDay' => true,
                        ];
                    }
                }
            } else {
                $events[] = [
                    'title' => $holiday->name,
                    'color' => '#f59e0b',
                    'start' => $holiday->date->toDateString(),
                    'end' => $holiday->date->copy()->addDay()->toDateString(),
                    'display' => 'background',
                    'allDay' => true,
                ];
            }
        }

        return response()->json($events);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
