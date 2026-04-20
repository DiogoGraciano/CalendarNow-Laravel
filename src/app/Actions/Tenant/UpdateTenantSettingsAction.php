<?php

namespace App\Actions\Tenant;

use App\Models\TenantSetting;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateTenantSettingsAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated);

        return redirect()
            ->route('configuracoes.index')
            ->with('success', __('Configurações salvas com sucesso.'));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): void
    {
        $raw = $validated['scheduling_default_dre_id'] ?? null;
        $value = ($raw !== null && $raw !== '') ? (string) $raw : null;
        TenantSetting::setValue(TenantSetting::KEY_SCHEDULING_DEFAULT_DRE_ID, $value);
    }

    public function rules(): array
    {
        return [
            'scheduling_default_dre_id' => 'nullable|exists:dres,id',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
