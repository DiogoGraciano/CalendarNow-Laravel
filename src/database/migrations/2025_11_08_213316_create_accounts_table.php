<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->index('tenant_id');
            $table->foreignId('dre_id')->constrained('dres');
            $table->foreignId(column: 'customer_id')->constrained('customers');
            $table->string('code');
            $table->string('name');
            $table->enum('type', ['receivable', 'payable'])->default('receivable');
            $table->enum('type_interest', ['fixed', 'variable'])->default('fixed');
            $table->decimal('interest_rate', 10, 6)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid', 10, 2)->default(0);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->softDeletes();
            $table->timestamps();

            $table->index('code');
            if (DB::connection()->getDriverName() === 'pgsql') {
                $table->fullText('name');
            }
            $table->unique(['tenant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_receivables');
    }
};
