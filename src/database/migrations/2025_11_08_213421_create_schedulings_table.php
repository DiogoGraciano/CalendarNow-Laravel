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
        Schema::create('schedulings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->index('tenant_id');
            $table->string('code');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('calendar_id')->constrained('calendars');
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('customer_id')->constrained('customers');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->string('color')->default('#000000');
            $table->decimal('duration', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('code');
            $table->index('employee_id');
            $table->index('calendar_id');
            $table->index('account_id');
            $table->index('customer_id');
            $table->index(['start_time', 'end_time']);
            $table->index('status');
            $table->unique(['tenant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedulings');
    }
};
