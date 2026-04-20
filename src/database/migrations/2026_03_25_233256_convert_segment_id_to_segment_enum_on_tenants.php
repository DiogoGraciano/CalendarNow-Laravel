<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mapping of segment names (from seeder) to enum values.
     *
     * @var array<string, string>
     */
    private const NAME_TO_ENUM = [
        'Beleza e Estética' => 'beauty_aesthetics',
        'Saúde e Bem-estar' => 'health_wellness',
        'Educação' => 'education',
        'Consultoria' => 'consulting',
        'Tecnologia' => 'technology',
        'Serviços Gerais' => 'general_services',
        'Alimentação' => 'food',
        'Fitness' => 'fitness',
        'Moda' => 'fashion',
        'Automotivo' => 'automotive',
    ];

    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('segment', 50)->nullable()->after('segment_id');
        });

        // Migrate existing data
        if (Schema::hasTable('segments')) {
            $segments = DB::table('segments')->get();
            foreach ($segments as $segment) {
                $enumValue = self::NAME_TO_ENUM[$segment->name] ?? null;
                if ($enumValue) {
                    DB::table('tenants')
                        ->where('segment_id', $segment->id)
                        ->update(['segment' => $enumValue]);
                }
            }
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('segment_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('segment_id')->nullable()->constrained();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('segment');
        });
    }
};
