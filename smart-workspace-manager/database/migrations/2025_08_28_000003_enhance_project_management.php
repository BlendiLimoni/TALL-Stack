<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create project templates table
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('default_columns')->nullable(); // Custom Kanban columns
            $table->json('task_templates')->nullable(); // Default tasks to create
            $table->json('settings')->nullable(); // Template-specific settings
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Create project tags/categories
        Schema::create('project_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#6366f1'); // hex color
            $table->timestamps();
        });

        // Pivot table for project-tag relationships
        Schema::create('project_tag_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_tag_id')->constrained('project_tags')->cascadeOnDelete();
            $table->timestamps();
        });

        // Add more fields to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('description');
            $table->date('end_date')->nullable()->after('start_date');
            $table->enum('status', ['planning', 'active', 'on_hold', 'completed', 'archived'])->default('planning')->after('end_date');
            $table->json('custom_fields')->nullable()->after('status');
            $table->decimal('budget', 10, 2)->nullable()->after('custom_fields');
            $table->text('goals')->nullable()->after('budget');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'status', 'custom_fields', 'budget', 'goals']);
        });
        
        Schema::dropIfExists('project_tag_project');
        Schema::dropIfExists('project_tags');
        Schema::dropIfExists('project_templates');
    }
};
