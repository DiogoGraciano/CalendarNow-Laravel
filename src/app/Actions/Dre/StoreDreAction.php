<?php

namespace App\Actions\Dre;

use App\Models\Dre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreDreAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): JsonResponse|RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $dre = $this->handle($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'dre' => [
                    'id' => $dre->id,
                    'code' => $dre->code,
                    'description' => $dre->description,
                    'type' => $dre->type,
                ],
            ]);
        }

        return redirect()
            ->route('dres.index')
            ->with('success', 'Conta DRE criada com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated): Dre
    {
        return Dre::create($validated);
    }

    public function rules(): array
    {
        $tenantId = tenant('id');

        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dres', 'code')->where('tenant_id', $tenantId),
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
