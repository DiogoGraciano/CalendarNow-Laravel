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
            $table->string('seo_home_title')->nullable()->after('show_employees_section');
            $table->text('seo_home_description')->nullable()->after('seo_home_title');
            $table->string('seo_booking_title')->nullable()->after('seo_home_description');
            $table->text('seo_booking_description')->nullable()->after('seo_booking_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'seo_home_title',
                'seo_home_description',
                'seo_booking_title',
                'seo_booking_description',
            ]);
        });
    }
};
