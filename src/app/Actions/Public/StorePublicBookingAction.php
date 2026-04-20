<?php

namespace App\Actions\Public;

use App\Actions\Scheduling\StoreSchedulingAction;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeDayOff;
use App\Models\Holiday;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class StorePublicBookingAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'calendar_id' => 'required|exists:calendars,id',
            'employee_id' => 'required|exists:employees,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
        ]);

        $serviceIds = array_values(array_unique(array_filter($validated['service_ids'])));
        $employee = Employee::find($validated['employee_id']);

        $publicCalendar = $employee?->publicCalendar();
        if (! $publicCalendar || $publicCalendar->id !== (int) $validated['calendar_id']) {
            throw ValidationException::withMessages([
                'calendar_id' => __('A agenda selecionada não é a agenda pública deste profissional.'),
            ]);
        }

        $employeeServiceIds = $employee ? $employee->services()->pluck((new Service)->getTable().'.id')->all() : [];
        $invalid = array_diff($serviceIds, $employeeServiceIds);
        if (! empty($invalid)) {
            throw ValidationException::withMessages([
                'service_ids' => __('Os serviços selecionados devem ser oferecidos pelo profissional escolhido.'),
            ]);
        }

        $this->validateSlot($validated);

        $customer = $this->resolveCustomer($validated['name'], $validated['email'] ?? null, $validated['phone'] ?? null);
        $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');
        $items = [];
        foreach ($serviceIds as $serviceId) {
            $service = $services->get($serviceId);
            if (! $service) {
                throw ValidationException::withMessages(['service_ids' => __('Serviço inválido.')]);
            }
            $items[] = [
                'service_id' => $service->id,
                'quantity' => 1,
                'unit_amount' => (float) $service->price,
                'duration' => (float) $service->duration_minutes,
            ];
        }

        $payload = [
            'calendar_id' => (int) $validated['calendar_id'],
            'employee_id' => (int) $validated['employee_id'],
            'customer_id' => $customer->id,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending',
            'items' => $items,
        ];

        StoreSchedulingAction::run($payload);

        return redirect()
            ->route('public.booking-confirmed')
            ->with('success', __('Agendamento realizado com sucesso. Em breve entraremos em contato.'));
    }

    private function validateSlot(array $validated): void
    {
        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);
        $date = $start->copy()->startOfDay();
        $durationMinutes = (int) $start->diffInMinutes($end);

        // Check if the date is a holiday
        $isHoliday = Holiday::query()
            ->where(function ($q) use ($date) {
                $q->whereDate('date', $date->toDateString());
            })
            ->orWhere(function ($q) use ($date) {
                $q->where('recurring', true)
                    ->whereMonth('date', $date->month)
                    ->whereDay('date', $date->day);
            })
            ->exists();

        if ($isHoliday) {
            throw ValidationException::withMessages([
                'start_time' => __('Não é possível agendar em um feriado.'),
            ]);
        }

        // Check if the employee has a day off on this date
        $hasDayOff = EmployeeDayOff::query()
            ->where('employee_id', (int) $validated['employee_id'])
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->exists();

        if ($hasDayOff) {
            throw ValidationException::withMessages([
                'start_time' => __('O profissional está de folga nesta data.'),
            ]);
        }

        $services = Service::whereIn('id', $validated['service_ids'])->get();
        $expectedMinutes = $this->totalDurationMinutes($services);
        if ($durationMinutes < $expectedMinutes) {
            throw ValidationException::withMessages([
                'start_time' => __('O horário selecionado não é válido.'),
            ]);
        }
    }

    /**
     * @param  Collection<int, Service>  $services
     */
    private function totalDurationMinutes(Collection $services): int
    {
        $total = 0;
        foreach ($services as $service) {
            $d = $service->duration;
            if (is_numeric($d)) {
                $total += (int) $d;
            } elseif (is_string($d) && preg_match('/^(\d+):(\d+)/', (string) $d, $m)) {
                $total += (int) $m[1] * 60 + (int) $m[2];
            }
        }

        return $total;
    }

    private function resolveCustomer(string $name, ?string $email, ?string $phone): Customer
    {
        if ($email) {
            $customer = Customer::where('email', $email)->first();
            if ($customer) {
                $customer->update(array_filter([
                    'name' => $name,
                    'phone' => $phone,
                ]));

                return $customer;
            }
        }

        return Customer::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);
    }
}
