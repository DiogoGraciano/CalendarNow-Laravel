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
        Schema::create('scheduling_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->index('tenant_id');
            $table->foreignId('scheduling_id')->constrained('schedulings');
            $table->foreignId('service_id')->constrained('services');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('unit_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->time('duration')->default('00:00:00');
            $table->softDeletes();
            $table->timestamps();

            $table->index('service_id');
            $table->index('scheduling_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduling_items');
    }
};
