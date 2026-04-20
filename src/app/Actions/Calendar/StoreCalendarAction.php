<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreCalendarAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $calendar = $this->handle($validated, $request->user());

        return redirect()
            ->route('calendars.index')
            ->with('success', 'Agenda criada com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?Authenticatable $user = null): Calendar
    {
        $validated['code'] = $this->generateUniqueCode();
        $calendar = Calendar::create($validated);
        if ($user && method_exists($user, 'calendars')) {
            $user->calendars()->attach($calendar->id);
        }

        return $calendar;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    private function generateUniqueCode(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $exists = Calendar::where('code', $code)->exists();
            $attempt++;
            if ($attempt >= $maxAttempts) {
                throw new \RuntimeException('Não foi possível gerar um código único após várias tentativas.');
            }
        } while ($exists);

        return $code;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
