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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->index('tenant_id');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('cpf_cnpj')->nullable();
            $table->string('rg')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['working', 'vacation', 'sick_leave', 'fired', 'resigned'])->default('working');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->date('admission_date')->useCurrent();
            $table->date('work_start_date')->nullable();
            $table->time('work_start_time')->nullable();
            $table->time('work_end_time')->nullable();
            $table->date('launch_start_time')->nullable();
            $table->date('launch_end_time')->nullable();
            $table->json('work_days')->nullable();
            $table->date('work_end_date')->nullable();
            $table->date('fired_date')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->integer('pay_day')->default(5);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['tenant_id', 'cpf_cnpj']);
            $table->unique(['tenant_id', 'rg']);
            $table->unique(['tenant_id', 'email']);
            $table->unique(['tenant_id', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
