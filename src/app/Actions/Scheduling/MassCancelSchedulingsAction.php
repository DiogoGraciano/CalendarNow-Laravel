<?php

namespace App\Actions\Scheduling;

use App\Models\Scheduling;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class MassCancelSchedulingsAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated);

        return redirect()
            ->back()
            ->with('success', 'Agendamento(s) cancelado(s) com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): void
    {
        $ids = $validated['ids'] ?? [];
        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'Selecione ao menos um agendamento',
            ]);
        }
        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $scheduling = Scheduling::find($id);
                if ($scheduling) {
                    $scheduling->update(['status' => 'cancelled']);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'ids' => 'Erro ao cancelar agendamentos: '.$e->getMessage(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:schedulings,id',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
