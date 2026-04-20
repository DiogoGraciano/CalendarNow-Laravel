<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantEmail extends Model
{
    /** @use HasFactory<\Database\Factories\TenantEmailFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'email',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
