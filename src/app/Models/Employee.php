<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Employee extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use BelongsToTenant, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'user_id',
        'cpf_cnpj',
        'rg',
        'email',
        'phone',
        'photo',
        'status',
        'gender',
        'birth_date',
        'admission_date',
        'work_start_date',
        'work_start_time',
        'work_end_time',
        'launch_start_time',
        'launch_end_time',
        'work_days',
        'work_end_date',
        'fired_date',
        'salary',
        'pay_day',
        'notes',
    ];

    protected $casts = [
        'work_days' => 'array',
        'birth_date' => 'date',
        'admission_date' => 'date',
        'work_start_date' => 'date',
        'work_start_time' => 'datetime',
        'work_end_time' => 'datetime',
        'launch_start_time' => 'date',
        'launch_end_time' => 'date',
        'work_end_date' => 'date',
        'fired_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedulings(): HasMany
    {
        return $this->hasMany(Scheduling::class);
    }

    public function daysOff(): HasMany
    {
        return $this->hasMany(EmployeeDayOff::class);
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class, 'employee_addresses');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'employee_service');
    }

    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(Calendar::class, 'employee_calendars')
            ->withPivot('is_public')
            ->withTimestamps();
    }

    /**
     * Calendar marked as public for this employee (at most one per employee).
     */
    public function publicCalendar(): ?Calendar
    {
        return $this->calendars()->wherePivot('is_public', true)->first();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();

        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Contain, 150, 150)
            ->nonQueued();
    }
}
