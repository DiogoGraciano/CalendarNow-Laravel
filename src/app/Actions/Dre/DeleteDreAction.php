<?php

namespace App\Actions\Dre;

use App\Models\Dre;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDreAction
{
    use AsAction;

    public function asController(Dre $dre, ActionRequest $request): RedirectResponse
    {
        $this->handle($dre);

        return redirect()
            ->route('dres.index')
            ->with('success', 'Conta DRE excluída com sucesso');
    }

    public function handle(Dre $dre): void
    {
        $dre->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
