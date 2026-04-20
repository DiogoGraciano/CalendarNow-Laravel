<?php

namespace App\Models;

use App\Enums\SegmentEnum;
use Clickbar\Magellan\Data\Geometries\Point;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\HasScopedValidationRules;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    /** @use HasFactory<TenantFactory> */
    use Billable, HasDomains, HasFactory, HasScopedValidationRules;

    protected $fillable = [
        'plan_id',
        'segment',
        'name',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'neighborhood',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'theme',
        'hero_title',
        'hero_subtitle',
        'show_employees_section',
        'seo_home_title',
        'seo_home_description',
        'seo_booking_title',
        'seo_booking_description',
        'location',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected $casts = [
        'location' => Point::class,
        'show_employees_section' => 'boolean',
        'segment' => SegmentEnum::class,
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'plan_id',
            'segment',
            'name',
            'email',
            'phone',
            'website',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'neighborhood',
            'logo',
            'favicon',
            'primary_color',
            'secondary_color',
            'theme',
            'hero_title',
            'hero_subtitle',
            'show_employees_section',
            'seo_home_title',
            'seo_home_description',
            'seo_booking_title',
            'seo_booking_description',
            'location',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getPublicColors(): array
    {
        return [
            'primary' => $this->primary_color ?? '#E85D2B',
            'secondary' => $this->secondary_color ?? '#1a1a1a',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'tenant_id');
    }

    public function tenantEmails(): HasMany
    {
        return $this->hasMany(TenantEmail::class);
    }

    /**
     * Adiciona um email ao tenant.
     */
    public function addEmail(string $email): TenantEmail
    {
        return $this->tenantEmails()->firstOrCreate([
            'email' => $email,
        ]);
    }

    public function removeEmail(string $email)
    {
        $this->tenantEmails()->where('email', $email)->delete();
    }
}
