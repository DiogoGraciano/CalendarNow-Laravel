<?php

namespace App\Actions\Scheduling;

use App\Models\Scheduling;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSchedulingDataAction
{
    use AsAction;

    public function asController(ActionRequest $request, Scheduling $scheduling): JsonResponse
    {
        $scheduling->load(['items.service', 'customer']);

        Log::info($scheduling);

        return response()->json([
            'id' => $scheduling->id,
            'calendar_id' => $scheduling->calendar_id,
            'employee_id' => $scheduling->employee_id,
            'customer_id' => $scheduling->customer_id,
            'start_time' => $scheduling->start_time->format('Y-m-d\TH:i'),
            'end_time' => $scheduling->end_time->format('Y-m-d\TH:i'),
            'status' => $scheduling->status,
            'color' => $scheduling->color ?? '#4267b2',
            'notes' => $scheduling->notes ?? '',
            'items' => $scheduling->items->map(fn ($item) => [
                'service_id' => (int) $item->service_id,
                'quantity' => (int) $item->quantity,
                'unit_amount' => (float) $item->unit_amount,
                'duration' => $item->duration,
            ])->toArray(),
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
