<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Accounts extends Model
{
    /**
     * Gera um código único para a conta no tenant (ex: REC-2026-00001).
     */
    public static function generateUniqueCode(?string $tenantId = null): string
    {
        $tenantId = $tenantId ?? tenant('id');
        $prefix = 'REC-'.date('Y').'-';
        $lastNum = static::query()
            ->where('tenant_id', $tenantId)
            ->where('code', 'like', $prefix.'%')
            ->get()
            ->map(fn (self $a): int => (int) preg_replace('/^REC-\d+-/', '', $a->code))
            ->filter(fn (int $n): bool => $n > 0)
            ->max();

        $next = ($lastNum ?? 0) + 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
    /** @use HasFactory<\Database\Factories\AccountsFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = [
        'dre_id',
        'customer_id',
        'code',
        'name',
        'type',
        'type_interest',
        'interest_rate',
        'total',
        'paid',
        'due_date',
        'payment_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:6',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    public function dre(): BelongsTo
    {
        return $this->belongsTo(Dre::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function schedulings(): HasMany
    {
        return $this->hasMany(Scheduling::class, 'account_id');
    }
}
