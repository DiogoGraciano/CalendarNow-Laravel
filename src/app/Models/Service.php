<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Service extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use BelongsToTenant, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'order' => 'integer',
    ];

    public function schedulingItems(): HasMany
    {
        return $this->hasMany(SchedulingItem::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_service');
    }

    /**
     * Duração do serviço em minutos (o campo duration é armazenado como time HH:MM:SS).
     */
    public function getDurationMinutesAttribute(): int
    {
        $d = $this->duration;
        if (is_numeric($d)) {
            return (int) $d;
        }
        $str = (string) $d;
        if (preg_match('/^(\d+):(\d+)/', $str, $m)) {
            return (int) $m[1] * 60 + (int) $m[2];
        }

        return 0;
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
