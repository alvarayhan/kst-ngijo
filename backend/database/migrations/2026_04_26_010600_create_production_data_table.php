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
        Schema::create('production_data', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('visitor_count');
            $table->enum('visitor_category', ['individuals', 'groups', 'researchers', 'students']);
            $table->enum('time_slot', ['morning', 'afternoon', 'evening']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_data');
    }
};
