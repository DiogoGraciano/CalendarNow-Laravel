<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Holiday extends Model
{
    /** @use HasFactory<\Database\Factories\HolidayFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'date',
        'recurring',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'recurring' => 'boolean',
    ];
}
