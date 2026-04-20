<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Dre extends Model
{
    /** @use HasFactory<\Database\Factories\DreFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $table = 'dres';

    protected $fillable = [
        'code',
        'description',
        'type',
    ];

    public function accountsReceivable(): HasMany
    {
        return $this->hasMany(Accounts::class);
    }
}
