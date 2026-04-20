<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Scheduling extends Model
{
    /** @use HasFactory<\Database\Factories\SchedulingFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'employee_id',
        'calendar_id',
        'account_id',
        'customer_id',
        'start_time',
        'end_time',
        'status',
        'color',
        'duration',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SchedulingItem::class);
    }

    /**
     * Verifica se o funcionário já tem algum agendamento (não cancelado) que sobrepõe o intervalo,
     * em qualquer agenda. Usado para impedir double-booking do mesmo funcionário.
     *
     * @param  Carbon  $start  início do intervalo (startOfMinute)
     * @param  Carbon  $end  fim do intervalo (startOfMinute)
     * @param  int|null  $excludeSchedulingId  ID do agendamento a ignorar (ex.: no update)
     */
    public static function employeeHasOverlap(int $employeeId, Carbon $start, Carbon $end, ?int $excludeSchedulingId = null): bool
    {
        $query = self::query()
            ->where('employee_id', $employeeId)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($excludeSchedulingId !== null) {
            $query->where('id', '!=', $excludeSchedulingId);
        }

        return $query->exists();
    }

    /**
     * Gera o próximo código único para novo agendamento (apenas create).
     * Formato: SCH-AAAA-NNNN (ex.: SCH-2026-0001).
     * Nota: PostgreSQL não permite FOR UPDATE com COUNT(); em concorrência alta
     * pode haver duplicata — o Store trata retentando com novo código.
     */
    public static function generateNextCode(): string
    {
        $tenantId = tenant('id');
        $nextNumber = self::where('tenant_id', $tenantId)->count() + 1;

        return 'SCH-'.now()->format('Y').'-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
