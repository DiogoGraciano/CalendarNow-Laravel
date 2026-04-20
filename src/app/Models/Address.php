<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip',
        'country',
        'type',
        'is_primary',
        'location',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'location' => Point::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_addresses');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_addresses');
    }
}
