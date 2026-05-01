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
        Schema::create('research_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['planning', 'active', 'completed', 'paused'])->default('active');
            $table->enum('category', ['technology', 'agriculture', 'energy', 'sustainability', 'other'])->default('other');
            $table->foreignId('principal_investigator_id')->nullable()->constrained('users');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->bigInteger('budget')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_projects');
    }
};
