<?php

namespace App\Actions\Employee;

use App\Actions\Auth\CreateUserAction;
use App\Models\Employee;
use App\Traits\PasswordValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreEmployeeAction
{
    use AsAction, PasswordValidationRules, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated, $request);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Funcionário criado com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?ActionRequest $request = null): void
    {
        DB::beginTransaction();
        try {
            if ($validated['create_user'] ?? false) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'password_confirmation' => $validated['password_confirmation'] ?? '',
                ];
                $user = CreateUserAction::run($userData);
                $validated['user_id'] = $user->id;
            }
            $serviceIds = $validated['service_ids'] ?? [];
            $calendarIds = array_values(array_unique(array_filter($validated['calendar_ids'] ?? [])));
            $publicCalendarId = isset($validated['public_calendar_id']) ? (int) $validated['public_calendar_id'] : null;
            unset(
                $validated['create_user'],
                $validated['name'],
                $validated['password'],
                $validated['password_confirmation'],
                $validated['service_ids'],
                $validated['calendar_ids'],
                $validated['public_calendar_id'],
                $validated['photo']
            );
            $employee = Employee::create($validated);
            $employee->services()->sync($serviceIds);
            $this->syncEmployeeCalendars($employee, $calendarIds, $publicCalendarId);

            if ($request && $request->hasFile('photo')) {
                $employee->addMediaFromRequest('photo')->toMediaCollection('photos');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rules(): array
    {
        $createUser = (bool) $this->get('create_user', false);
        $rules = [
            'create_user' => 'nullable|boolean',
            'cpf_cnpj' => 'nullable|string|max:20',
            'rg' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:working,vacation,sick_leave,fired,resigned',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'admission_date' => 'nullable|date',
            'work_start_date' => 'nullable|date',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'launch_start_time' => 'nullable|date_format:H:i',
            'launch_end_time' => 'nullable|date_format:H:i',
            'work_days' => 'nullable|array',
            'work_days.*' => 'nullable|integer|in:0,1,2,3,4,5,6',
            'work_end_date' => 'nullable|date',
            'fired_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'pay_day' => 'nullable|integer|min:1|max:31',
            'notes' => 'nullable|string',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer|exists:services,id',
            'calendar_ids' => 'nullable|array',
            'calendar_ids.*' => 'integer|exists:calendars,id',
            'public_calendar_id' => [
                'nullable',
                'integer',
                'exists:calendars,id',
                Rule::in($this->get('calendar_ids', []) ?: []),
            ],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
        if ($createUser) {
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = $this->passwordRules();
            $rules['password_confirmation'] = 'required|string';
        } else {
            $rules['email'] = 'nullable|email|max:255';
        }

        return $rules;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }

    /**
     * @param  array<int>  $calendarIds
     */
    private function syncEmployeeCalendars(Employee $employee, array $calendarIds, ?int $publicCalendarId): void
    {
        $sync = [];
        foreach ($calendarIds as $id) {
            $sync[$id] = ['is_public' => $id === $publicCalendarId];
        }
        $employee->calendars()->sync($sync);
    }
}
