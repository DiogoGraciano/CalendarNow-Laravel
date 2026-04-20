<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TenantSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'key',
        'value',
    ];

    /** DRE padrão usada ao criar a conta a receber vinculada ao agendamento. */
    public const KEY_SCHEDULING_DEFAULT_DRE_ID = 'scheduling_default_dre_id';

    /**
     * Obtém o valor de uma chave de configuração do tenant atual.
     */
    public static function getValue(string $key): ?string
    {
        $tenantId = tenant('id');
        if (! $tenantId) {
            return null;
        }

        $setting = self::where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        return $setting?->value;
    }

    /**
     * Define o valor de uma chave de configuração do tenant atual.
     */
    public static function setValue(string $key, ?string $value): void
    {
        $tenantId = tenant('id');
        if (! $tenantId) {
            return;
        }

        self::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'key' => $key,
            ],
            ['value' => $value]
        );
    }
}
