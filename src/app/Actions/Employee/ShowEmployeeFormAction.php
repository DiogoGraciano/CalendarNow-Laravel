<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowEmployeeFormAction
{
    use AsAction;

    public function asController(ActionRequest $request): RedirectResponse
    {
        // Obter o parâmetro employee da rota (pode ser null)
        $id = $request->route('employee') ? (int) $request->route('employee') : null;

        // Preservar mensagens flash existentes
        $redirect = redirect()->route('employees.index');

        // Preservar todas as flash messages da sessão
        $session = $request->session();
        if ($session->has('success')) {
            $redirect->with('success', $session->get('success'));
        }
        if ($session->has('error')) {
            $redirect->with('error', $session->get('error'));
        }
        if ($session->has('warning')) {
            $redirect->with('warning', $session->get('warning'));
        }
        if ($session->has('info')) {
            $redirect->with('info', $session->get('info'));
        }

        if ($id) {
            // Se for edição, redirecionar para index com o ID do funcionário como query parameter
            return $redirect->with('edit', $id);
        }

        // Se for criação, redirecionar para index com parâmetro create como query parameter
        return $redirect->with('create', 'true');
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
