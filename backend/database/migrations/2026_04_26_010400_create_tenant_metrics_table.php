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
        Schema::create('tenant_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->date('metric_date');
            $table->integer('employees_count')->nullable();
            $table->bigInteger('revenue')->nullable();
            $table->integer('products_produced')->nullable();
            $table->enum('market_reach', ['local', 'regional', 'national', 'international'])->default('local');
            $table->integer('sustainability_score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_metrics');
    }
};
