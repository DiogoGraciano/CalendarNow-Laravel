<?php

namespace App\Actions\Dre;

use App\Models\Dre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateDreAction
{
    use AsAction, WithAttributes;

    public function asController(Dre $dre, ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $this->set('_dre_id', $dre->id);
        $validated = $this->validateAttributes();
        unset($validated['_dre_id']);
        $this->handle($validated, $dre);

        return redirect()
            ->route('dres.index')
            ->with('success', 'Conta DRE atualizada com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?Dre $dre = null): void
    {
        if ($dre === null) {
            throw new \InvalidArgumentException('Dre is required for update.');
        }
        $dre->update($validated);
    }

    public function rules(): array
    {
        $tenantId = tenant('id');
        $ignoreId = $this->get('_dre_id');

        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dres', 'code')->where('tenant_id', $tenantId)->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'type' => 'required|in:receivable,payable',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
