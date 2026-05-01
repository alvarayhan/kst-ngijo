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
        Schema::create('sustainability_data', function (Blueprint $table) {
            $table->id();
            $table->date('record_date');
            $table->enum('category', ['energy', 'water', 'waste', 'emissions', 'social'])->default('energy');
            $table->string('metric_name');
            $table->decimal('value', 15, 2);
            $table->string('unit');
            $table->decimal('target_value', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sustainability_data');
    }
};
