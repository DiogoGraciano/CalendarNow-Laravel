<?php

namespace App\Actions\Public;

use App\Models\Employee;
use App\Models\EmployeeDayOff;
use App\Models\Holiday;
use App\Models\Scheduling;
use App\Models\Service;
use App\Support\ThemeResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mauricius\LaravelHtmx\Http\HtmxResponse;

class LoadPublicSlotsAction
{
    private const DAYS_PER_PAGE = 7;

    /**
     * Retorna fragmento(s) HTML com dias e horários disponíveis (slots) conforme serviços e opcionalmente profissional.
     * Paginação por cursor (from_date). Slots têm duração total dos serviços selecionados.
     */
    public function __invoke(Request $request): HtmxResponse|\Illuminate\Http\Response
    {
        $validated = Validator::make($request->all(), [
            'calendar_id' => 'required|exists:calendars,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'employee_id' => 'nullable|exists:employees,id',
            'cursor' => 'nullable|date_format:Y-m-d',
        ])->validate();

        $calendarId = (int) $validated['calendar_id'];
        $serviceIds = array_values(array_unique(array_filter($validated['service_ids'] ?? [])));
        if (count($serviceIds) === 0) {
            return (new HtmxResponse)->renderFragment(ThemeResolver::viewPath('booking-slots'), 'slots_initial', [
                'days' => [],
                'next_cursor' => null,
                'calendar_id' => $calendarId,
                'service_ids' => [],
                'employee_id' => $validated['employee_id'] ?? null,
            ]);
        }
        $employeeId = isset($validated['employee_id']) ? (int) $validated['employee_id'] : null;
        $cursorDate = isset($validated['cursor']) ? Carbon::parse($validated['cursor']) : null;

        $services = Service::whereIn('id', $serviceIds)->get();
        if ($services->count() !== count($serviceIds)) {
            return (new HtmxResponse)->renderFragment(ThemeResolver::viewPath('booking-slots'), 'slots_initial', [
                'days' => [],
                'next_cursor' => null,
                'calendar_id' => $calendarId,
                'service_ids' => $serviceIds,
                'employee_id' => $employeeId,
            ]);
        }

        $totalDurationMinutes = $this->totalDurationMinutes($services);
        if ($totalDurationMinutes <= 0) {
            return (new HtmxResponse)->renderFragment(ThemeResolver::viewPath('booking-slots'), 'slots_initial', [
                'days' => [],
                'next_cursor' => null,
                'calendar_id' => $calendarId,
                'service_ids' => $serviceIds,
                'employee_id' => $employeeId,
            ]);
        }

        $employees = $this->resolveEmployees($serviceIds, $employeeId);
        if ($employees->isEmpty()) {
            return (new HtmxResponse)->renderFragment(ThemeResolver::viewPath('booking-slots'), 'slots_initial', [
                'days' => [],
                'next_cursor' => null,
                'calendar_id' => $calendarId,
                'service_ids' => $serviceIds,
                'employee_id' => $employeeId,
            ]);
        }

        $startFrom = $cursorDate ?? Carbon::today();
        if ($startFrom->isPast()) {
            $startFrom = Carbon::today();
        }

        $daysWithSlots = [];
        $current = $startFrom->copy();
        $endWindow = $startFrom->copy()->addDays(self::DAYS_PER_PAGE);

        // Pre-fetch holidays for the date window
        $holidays = Holiday::query()
            ->where(function ($q) use ($startFrom, $endWindow) {
                $q->whereBetween('date', [$startFrom->toDateString(), $endWindow->toDateString()]);
            })
            ->orWhere('recurring', true)
            ->get();

        // Pre-fetch employee days off for the date window
        $employeeDaysOff = EmployeeDayOff::query()
            ->whereIn('employee_id', $employees->pluck('id'))
            ->where('start_date', '<=', $endWindow->toDateString())
            ->where('end_date', '>=', $startFrom->toDateString())
            ->get();

        while ($current->lt($endWindow)) {
            $date = $current->copy();
            $daySlots = $this->slotsForDay($date, $employees, $calendarId, $totalDurationMinutes, $holidays, $employeeDaysOff);
            if (! empty($daySlots)) {
                $daysWithSlots[] = [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->translatedFormat('d/m/Y'),
                    'slots' => $daySlots,
                ];
            }
            $current->addDay();
        }

        $nextCursor = $endWindow->copy()->format('Y-m-d');

        $isAppend = $cursorDate !== null;

        if ($isAppend) {
            return response()->view(ThemeResolver::viewPath('booking-slots-append'), [
                'days' => $daysWithSlots,
                'next_cursor' => $nextCursor,
                'calendar_id' => $calendarId,
                'service_ids' => $serviceIds,
                'employee_id' => $employeeId,
            ])->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return (new HtmxResponse)->renderFragment(ThemeResolver::viewPath('booking-slots'), 'slots_initial', [
            'days' => $daysWithSlots,
            'next_cursor' => $nextCursor,
            'calendar_id' => $calendarId,
            'service_ids' => $serviceIds,
            'employee_id' => $employeeId,
        ]);
    }

    /**
     * @param  array<int>  $serviceIds
     * @return \Illuminate\Support\Collection<int, Employee>
     */
    private function resolveEmployees(array $serviceIds, ?int $employeeId): \Illuminate\Support\Collection
    {
        if ($employeeId !== null) {
            $employee = Employee::with('services')->find($employeeId);
            if (! $employee) {
                return collect();
            }
            $hasAll = count(array_intersect($employee->services->pluck('id')->all(), $serviceIds)) === count($serviceIds);

            return $hasAll ? collect([$employee]) : collect();
        }

        return Employee::query()
            ->with('services')
            ->get()
            ->filter(fn (Employee $e) => count(array_intersect($e->services->pluck('id')->all(), $serviceIds)) === count($serviceIds));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Employee>  $employees
     * @return array<int, array{start: string, end: string, label: string, employee_id: int}>
     */
    private function slotsForDay(Carbon $date, \Illuminate\Support\Collection $employees, int $calendarId, int $totalDurationMinutes, \Illuminate\Support\Collection $holidays, \Illuminate\Support\Collection $employeeDaysOff): array
    {
        // Check if this date is a tenant-wide holiday
        $isHoliday = $holidays->contains(function (Holiday $h) use ($date) {
            if ($h->recurring) {
                return $h->date->month === $date->month && $h->date->day === $date->day;
            }

            return $h->date->toDateString() === $date->toDateString();
        });

        if ($isHoliday) {
            return [];
        }

        $slots = [];
        foreach ($employees as $employee) {
            $workDays = $employee->work_days ?? [1, 2, 3, 4, 5];
            $dayOfWeek = $date->dayOfWeek;
            $dayNumber = $dayOfWeek === 0 ? 7 : $dayOfWeek;
            if (! in_array($dayNumber, $workDays, true)) {
                continue;
            }

            // Check if this employee has a day off on this date
            $hasDayOff = $employeeDaysOff->contains(function (EmployeeDayOff $d) use ($employee, $date) {
                return $d->employee_id === $employee->id
                    && $d->start_date->lte($date)
                    && $d->end_date->gte($date);
            });

            if ($hasDayOff) {
                continue;
            }

            $startTime = $employee->work_start_time
                ? Carbon::createFromFormat('H:i', $employee->work_start_time->format('H:i'))
                : Carbon::createFromTimeString('08:00');
            $endTime = $employee->work_end_time
                ? Carbon::createFromFormat('H:i', $employee->work_end_time->format('H:i'))
                : Carbon::createFromTimeString('18:00');

            $dayStart = $date->copy()->setTime((int) $startTime->format('H'), (int) $startTime->format('i'), 0);
            $dayEnd = $date->copy()->setTime((int) $endTime->format('H'), (int) $endTime->format('i'), 0);

            $existing = Scheduling::query()
                ->where('employee_id', $employee->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('start_time', $date)
                ->get();

            $current = $dayStart->copy();
            while ($current->copy()->addMinutes($totalDurationMinutes)->lte($dayEnd)) {
                $slotStart = $current->copy();
                $slotEnd = $current->copy()->addMinutes($totalDurationMinutes);
                $overlaps = $existing->contains(function (Scheduling $s) use ($slotStart, $slotEnd) {
                    $sStart = $s->start_time->copy()->startOfMinute();
                    $sEnd = $s->end_time->copy()->startOfMinute();

                    return $slotStart->lt($sEnd) && $slotEnd->gt($sStart);
                });
                if (! $overlaps) {
                    $slots[] = [
                        'start' => $slotStart->format('Y-m-d\TH:i'),
                        'end' => $slotEnd->format('Y-m-d\TH:i'),
                        'label' => $slotStart->format('H:i').' - '.$slotEnd->format('H:i'),
                        'employee_id' => $employee->id,
                    ];
                }
                $current->addMinutes(30);
            }
        }

        return $slots;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Service>  $services
     */
    private function totalDurationMinutes(\Illuminate\Support\Collection $services): int
    {
        $total = 0;
        foreach ($services as $service) {
            $d = $service->duration;
            if (is_numeric($d)) {
                $total += (int) $d;
            } elseif (is_string($d) && preg_match('/^(\d+):(\d+)/', $d, $m)) {
                $total += (int) $m[1] * 60 + (int) $m[2];
            }
        }

        return $total;
    }
}
