<?php

namespace App\Actions\Service;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteServiceAction
{
    use AsAction;

    public function asController(Service $service, ActionRequest $request): RedirectResponse
    {
        $this->handle($service);

        return redirect()
            ->route('services.index')
            ->with('success', 'Serviço excluído com sucesso');
    }

    public function handle(Service $service): void
    {
        $service->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
