<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add time tracking
            $table->unsignedInteger('estimated_hours')->nullable()->after('priority');
            $table->unsignedInteger('actual_hours')->nullable()->after('estimated_hours');
            
            // Add task dependencies
            $table->json('depends_on')->nullable()->after('actual_hours');
            
            // Add labels/tags
            $table->json('labels')->nullable()->after('depends_on');
            
            // Add completion percentage
            $table->unsignedTinyInteger('completion_percentage')->default(0)->after('labels');
            
            // Add archiving capability
            $table->timestamp('archived_at')->nullable()->after('completion_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'estimated_hours', 
                'actual_hours', 
                'depends_on', 
                'labels', 
                'completion_percentage',
                'archived_at'
            ]);
        });
    }
};
