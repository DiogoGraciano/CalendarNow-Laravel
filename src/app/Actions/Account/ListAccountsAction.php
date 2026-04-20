<?php

namespace App\Actions\Account;

use App\Models\Accounts;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAccountsAction
{
    use AsAction;

    public function asController(ActionRequest $request): Response
    {
        $name = $request->input('name');
        $dueDate = $request->input('due_date');
        $paymentDate = $request->input('payment_date');
        $status = $request->input('status');

        $query = Accounts::query()
            ->with(['dre', 'customer'])
            ->latest('due_date');

        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($dueDate) {
            $query->whereDate('due_date', $dueDate);
        }

        if ($paymentDate) {
            $query->whereDate('payment_date', $paymentDate);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $accounts = $query->paginate(15);

        // Calcular totais
        $totals = $this->calculateTotals(tenant()->id);

        return Inertia::render('accounts/index', [
            'accounts' => $accounts,
            'filters' => [
                'name' => $name,
                'due_date' => $dueDate,
                'payment_date' => $paymentDate,
                'status' => $status ?? 'all',
            ],
            'totals' => $totals,
            'dres' => \App\Models\Dre::orderBy('code')->get(['id', 'code', 'description', 'type']),
            'customers' => \App\Models\Customer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function calculateTotals(string $tenantId): array
    {
        $totals = DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->selectRaw("
                SUM(CASE WHEN type = 'payable' AND status = 'pending' THEN total ELSE 0 END) as total_a_pagar,
                SUM(CASE WHEN type = 'receivable' AND status = 'pending' THEN total ELSE 0 END) as total_a_receber,
                SUM(CASE WHEN type = 'payable' AND status = 'overdue' THEN total ELSE 0 END) as total_pago_atrasado,
                SUM(CASE WHEN type = 'receivable' AND status = 'overdue' THEN total ELSE 0 END) as total_recebido_atrasado,
                SUM(CASE WHEN type = 'payable' AND status = 'paid' THEN total ELSE 0 END) as total_pago,
                SUM(CASE WHEN type = 'receivable' AND status = 'paid' THEN total ELSE 0 END) as total_recebido
            ")
            ->first();

        return [
            'total_a_pagar' => (float) ($totals->total_a_pagar ?? 0),
            'total_a_receber' => (float) ($totals->total_a_receber ?? 0),
            'total_pago_atrasado' => (float) ($totals->total_pago_atrasado ?? 0),
            'total_recebido_atrasado' => (float) ($totals->total_recebido_atrasado ?? 0),
            'total_pago' => (float) ($totals->total_pago ?? 0),
            'total_recebido' => (float) ($totals->total_recebido ?? 0),
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
