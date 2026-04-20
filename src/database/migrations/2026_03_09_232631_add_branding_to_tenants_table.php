<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('primary_color')->nullable()->after('favicon');
            $table->string('secondary_color')->nullable()->after('primary_color');
            $table->string('hero_title')->nullable()->after('secondary_color');
            $table->text('hero_subtitle')->nullable()->after('hero_title');
            $table->boolean('show_employees_section')->default(true)->after('hero_subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color',
                'secondary_color',
                'hero_title',
                'hero_subtitle',
                'show_employees_section',
            ]);
        });
    }
};
