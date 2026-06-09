<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('trl_level')
                  ->default(1)
                  ->after('status')
                  ->comment('Technology Readiness Level 1-9');
        });
    }

    public function down(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->dropColumn('trl_level');
        });
    }
};