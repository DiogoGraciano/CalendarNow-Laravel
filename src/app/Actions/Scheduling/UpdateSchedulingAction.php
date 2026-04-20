<?php

namespace App\Actions\Scheduling;

use App\Models\Accounts;
use App\Models\Customer;
use App\Models\Scheduling;
use App\Models\SchedulingItem;
use App\Models\TenantSetting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateSchedulingAction
{
    use AsAction, WithAttributes;

    public function asController(Scheduling $scheduling, ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $scheduling = $this->handle($validated, $scheduling);

        return redirect()
            ->route('scheduling.index', [
                'calendar' => $scheduling->calendar_id,
                'employee' => $scheduling->employee_id,
            ])
            ->with('success', 'Agendamento atualizado com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?Scheduling $scheduling = null): Scheduling
    {
        if ($scheduling === null) {
            throw new \InvalidArgumentException('Scheduling is required for update.');
        }

        $start = Carbon::parse($validated['start_time']);
        $requestEnd = Carbon::parse($validated['end_time']);
        $rangeMinutes = $start->diffInMinutes($requestEnd);

        $totalDurationMinutes = 0;
        foreach ($validated['items'] ?? [] as $item) {
            $dur = (float) ($item['duration'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);
            $totalDurationMinutes += $dur * $qty;
        }
        if ($totalDurationMinutes > $rangeMinutes) {
            throw ValidationException::withMessages([
                'items' => __('validation.scheduling.duration_exceeds_period', [
                    'duration' => (int) round($totalDurationMinutes),
                    'period' => $rangeMinutes,
                ]),
            ]);
        }

        $end = $totalDurationMinutes > 0
            ? $start->copy()->addMinutes((int) round($totalDurationMinutes))
            : $requestEnd;

        if ($requestEnd->gt($end)) {
            throw ValidationException::withMessages([
                'end_time' => __('validation.scheduling.end_time_exceeds_services_duration', [
                    'duration' => (int) round($totalDurationMinutes),
                ]),
            ]);
        }

        $startNorm = $start->copy()->startOfMinute();
        $endNorm = $end->copy()->startOfMinute();
        if (Scheduling::employeeHasOverlap((int) $validated['employee_id'], $startNorm, $endNorm, $scheduling->id)) {
            throw ValidationException::withMessages([
                'start_time' => __('validation.scheduling.employee_slot_overlap'),
            ]);
        }

        $dreIdRaw = TenantSetting::getValue(TenantSetting::KEY_SCHEDULING_DEFAULT_DRE_ID);
        if ($dreIdRaw === null || $dreIdRaw === '') {
            throw ValidationException::withMessages([
                'error' => __('validation.scheduling.default_dre_required'),
            ]);
        }
        $dreId = (int) $dreIdRaw;
        if (! \App\Models\Dre::where('id', $dreId)->where('tenant_id', tenant('id'))->exists()) {
            throw ValidationException::withMessages([
                'error' => __('validation.scheduling.default_dre_invalid'),
            ]);
        }

        $account = $this->getOrCreateAccountForScheduling($dreId, (int) $validated['customer_id']);
        $accountId = $account->id;

        DB::beginTransaction();
        try {
            $scheduling->update([
                'calendar_id' => $validated['calendar_id'],
                'employee_id' => $validated['employee_id'],
                'account_id' => $accountId,
                'customer_id' => $validated['customer_id'],
                'start_time' => $start,
                'end_time' => $end,
                'status' => $validated['status'] ?? $scheduling->status,
                'color' => $validated['color'] ?? $scheduling->color,
                'notes' => $validated['notes'] ?? $scheduling->notes,
            ]);

            $scheduling->items()->delete();

            if (isset($validated['items']) && is_array($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    if (isset($item['service_id']) && isset($item['quantity'])) {
                        SchedulingItem::create([
                            'scheduling_id' => $scheduling->id,
                            'service_id' => $item['service_id'],
                            'quantity' => $item['quantity'],
                            'unit_amount' => $item['unit_amount'] ?? 0,
                            'total_amount' => ($item['unit_amount'] ?? 0) * $item['quantity'],
                            'duration' => $this->minutesToTimeString((float) ($item['duration'] ?? 0)),
                        ]);
                    }
                }
            }

            DB::commit();

            return $scheduling;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' => 'Erro ao atualizar agendamento: '.$e->getMessage(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'calendar_id' => 'required|exists:calendars,id',
            'employee_id' => 'required|exists:employees,id',
            'customer_id' => 'required|exists:customers,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'color' => 'nullable|string|max:7',
            'notes' => 'nullable|string|max:400',
            'items' => 'nullable|array',
            'items.*.service_id' => 'required_with:items|exists:services,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_amount' => 'nullable|numeric|min:0',
            'items.*.duration' => 'nullable|numeric|min:0',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }

    /**
     * Converte duração em minutos para formato time do PostgreSQL (HH:MM:SS).
     */
    private function minutesToTimeString(float $minutes): string
    {
        $totalMins = max(0, (int) round($minutes));
        $h = (int) floor($totalMins / 60);
        $m = $totalMins % 60;

        return sprintf('%02d:%02d:00', $h, $m);
    }

    private function getOrCreateAccountForScheduling(int $dreId, int $customerId): Accounts
    {
        $tenantId = tenant('id');
        $account = Accounts::where('tenant_id', $tenantId)
            ->where('dre_id', $dreId)
            ->where('customer_id', $customerId)
            ->where('type', 'receivable')
            ->first();

        if ($account !== null) {
            return $account;
        }

        $customer = Customer::find($customerId);
        $customerName = $customer?->name ?? 'Cliente #'.$customerId;

        return Accounts::create([
            'dre_id' => $dreId,
            'customer_id' => $customerId,
            'code' => Accounts::generateUniqueCode($tenantId),
            'name' => 'Agendamentos - '.$customerName,
            'type' => 'receivable',
            'type_interest' => 'fixed',
            'interest_rate' => 0,
            'total' => 0,
            'paid' => 0,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
        ]);
    }
}
