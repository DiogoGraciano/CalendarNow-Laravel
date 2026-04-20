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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->index('tenant_id');
            $table->string('street');
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('country')->default('Brasil');
            $table->enum('type', ['residential', 'commercial', 'delivery', 'billing', 'shipping', 'other'])->default('residential');
            $table->boolean('is_primary')->default(false);
            if (DB::connection()->getDriverName() === 'pgsql') {
                $table->geometry('location', 'POINT', 4326)->nullable();
            }
            $table->softDeletes();
            $table->timestamps();

            $table->index('zip');
            $table->index('city');
            $table->index('state');
            if (DB::connection()->getDriverName() === 'pgsql') {
                $table->fullText('street');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
